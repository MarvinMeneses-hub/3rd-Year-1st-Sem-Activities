<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReorderService
{
    private const USAGE_PERIOD_DAYS = 30;
    private const DEFAULT_ORDERING_COST = 100;

    /**
     * Calculate average daily usage from stock-out transactions.
     */
    public function calculateAverageDailyUsage(
        Product $product,
        int $periodDays = self::USAGE_PERIOD_DAYS
    ): float {
        $periodDays = max(1, $periodDays);

        $totalUsed = InventoryTransaction::query()
            ->where('product_id', $product->id)
            ->where('type', 'stock_out')
            ->where('created_at', '>=', now()->subDays($periodDays))
            ->sum('quantity');

        return (float) $totalUsed / $periodDays;
    }

    /**
     * Calculate reorder point:
     * (Average Daily Usage x Supplier Lead Time) + Safety Stock.
     */
    public function calculateReorderPoint(Product $product): int
    {
        $product->loadMissing('supplierRecord');

        $averageDailyUsage = $this->calculateAverageDailyUsage($product);

        $leadTime = (int) ($product->supplierRecord?->lead_time_days ?? 7);

        $safetyStock = max(0, (int) $product->reorder_level);

        return (int) ceil(
            ($averageDailyUsage * $leadTime) + $safetyStock
        );
    }

    /**
     * Calculate Economic Order Quantity (EOQ).
     */
    public function calculateOptimalOrderQuantity(
        Product $product,
        float $orderingCost = self::DEFAULT_ORDERING_COST,
        ?float $annualHoldingCostPerUnit = null
    ): int {
        $averageDailyUsage = $this->calculateAverageDailyUsage($product);

        $annualDemand = $averageDailyUsage * 365;

        if ($annualDemand <= 0 || $orderingCost <= 0) {
            return max(1, (int) $product->reorder_level);
        }

        $unitCost = (float) ($product->unit_price ?: $product->price);

        $holdingCost = $annualHoldingCostPerUnit
            ?? ($unitCost * 0.20);

        if ($holdingCost <= 0) {
            return max(1, (int) $product->reorder_level);
        }

        $eoq = sqrt(
            (2 * $annualDemand * $orderingCost) / $holdingCost
        );

        return max(1, (int) ceil($eoq));
    }

    /**
     * Get products that have reached their reorder point
     * and have an active supplier.
     */
    public function getProductsNeedingReorder(): Collection
    {
        return Product::with('supplierRecord')
            ->whereHas('supplierRecord', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->filter(function (Product $product) {
                return $product->quantity <= $this->calculateReorderPoint($product);
            })
            ->values();
    }

    /**
     * Group products by supplier.
     */
    public function groupProductsBySupplier($products): Collection
    {
        return collect($products)
            ->filter(function (Product $product) {
                return $product->supplier_id !== null;
            })
            ->groupBy('supplier_id');
    }

    /**
     * Generate purchase orders for products needing replenishment.
     */
    public function generatePurchaseOrders(): Collection
    {
        $products = $this->getProductsNeedingReorder();

        // Avoid duplicate replenishment orders.
        $products = $products->reject(function (Product $product) {
            return PurchaseOrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('purchaseOrder', function ($query) {
                    $query->whereIn('status', [
                        PurchaseOrder::STATUS_PENDING,
                        PurchaseOrder::STATUS_APPROVED,
                        PurchaseOrder::STATUS_SENT,
                    ]);
                })
                ->exists();
        })->values();

        $groupedProducts = $this->groupProductsBySupplier($products);

        $generatedOrders = collect();

        foreach ($groupedProducts as $supplierId => $supplierProducts) {
            $purchaseOrder = DB::transaction(function () use (
                $supplierId,
                $supplierProducts
            ) {
                $supplier = Supplier::active()
                    ->whereKey($supplierId)
                    ->lockForUpdate()
                    ->first();

                if (!$supplier) {
                    return null;
                }

                $items = [];
                $subtotal = 0;

                foreach ($supplierProducts as $product) {
                    $unitCost = round(
                        (float) ($product->unit_price ?: $product->price),
                        2
                    );

                    $quantity = $this->calculateOptimalOrderQuantity($product);

                    $lineTotal = round($quantity * $unitCost, 2);

                    $items[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ];

                    $subtotal += $lineTotal;
                }

                if (empty($items)) {
                    return null;
                }

                // Respect the supplier's minimum order amount.
                $minimumOrder = (float) $supplier->minimum_order_amount;

                if ($subtotal < $minimumOrder) {
                    $shortfall = $minimumOrder - $subtotal;

                    $firstItemIndex = 0;
                    $firstUnitCost = $items[$firstItemIndex]['unit_cost'];

                    if ($firstUnitCost <= 0) {
                        return null;
                    }

                    $extraQuantity = (int) ceil(
                        $shortfall / $firstUnitCost
                    );

                    $items[$firstItemIndex]['quantity'] += $extraQuantity;

                    $items[$firstItemIndex]['line_total'] = round(
                        $items[$firstItemIndex]['quantity'] * $firstUnitCost,
                        2
                    );

                    $subtotal = array_sum(
                        array_column($items, 'line_total')
                    );
                }

                $poNumber = $this->generatePoNumber();

                $purchaseOrder = PurchaseOrder::create([
                    'supplier_id' => $supplier->id,
                    'created_by' => auth()->id(),
                    'po_number' => $poNumber,
                    'status' => PurchaseOrder::STATUS_PENDING,
                    'order_date' => today(),
                    'expected_delivery_date' => today()->addDays(
                        max(0, (int) $supplier->lead_time_days)
                    ),
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'total_amount' => $subtotal,
                    'notes' => 'Automatically generated by the inventory replenishment system.',
                ]);

                foreach ($items as $item) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'line_total' => $item['line_total'],
                    ]);
                }

                return $purchaseOrder;
            });

            if ($purchaseOrder) {
                $generatedOrders->push($purchaseOrder);
            }
        }

        return $generatedOrders;
    }

    /**
     * Generate a unique purchase order number.
     */
    private function generatePoNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-'
                . Str::upper(Str::random(6));
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }
}
