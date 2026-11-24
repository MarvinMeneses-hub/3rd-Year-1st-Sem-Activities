<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Services\ReorderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckReorderPoints extends Command
{
    protected $signature = 'app:check-reorder-points';

    protected $description = 'Check inventory reorder points, send low-stock alerts, and generate purchase orders';

    public function handle(ReorderService $reorderService): int
    {
        $this->info('Checking inventory reorder points...');

        try {
            $this->sendLowStockAlerts();

            $products = $reorderService->getProductsNeedingReorder();

            if ($products->isEmpty()) {
                $this->info('No products currently need replenishment.');

                return self::SUCCESS;
            }

            $this->info($products->count() . ' product(s) need replenishment.');

            $orders = $reorderService->generatePurchaseOrders();

            if ($orders->isEmpty()) {
                $this->warn(
                    'No new purchase orders were generated. Products may already have open orders or may not have valid suppliers.'
                );

                return self::SUCCESS;
            }

            foreach ($orders as $order) {
                $this->line(
                    "Created PO: {$order->po_number} | Supplier ID: {$order->supplier_id} | Total: ₱"
                    . number_format((float) $order->total_amount, 2)
                );
            }

            $this->info(
                $orders->count() . ' purchase order(s) generated successfully.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Reorder check failed: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Send one low-stock alert per low-stock period.
     */
    private function sendLowStockAlerts(): void
    {
        $adminEmail = config('low-stock.alert_email');

        // Clear previous alert flags when products are replenished.
        Product::whereColumn('quantity', '>', 'reorder_level')
            ->pluck('id')
            ->each(function ($productId) {
                Cache::forget('low_stock_alert_sent_' . $productId);
            });

        $lowStockProducts = Product::whereColumn(
            'quantity',
            '<=',
            'reorder_level'
        )->orderBy('id')->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low-stock products detected.');

            return;
        }

        $this->info(
            $lowStockProducts->count() . ' low-stock product(s) detected.'
        );

        if (
            !$adminEmail ||
            !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $this->warn(
                'Low-stock alerts were skipped. Configure LOW_STOCK_ALERT_EMAIL in your .env file.'
            );

            return;
        }

        foreach ($lowStockProducts as $product) {
            $cacheKey = 'low_stock_alert_sent_' . $product->id;

            if (Cache::has($cacheKey)) {
                $this->line(
                    "Alert already sent for: {$product->name}"
                );

                continue;
            }

            try {
                Mail::to($adminEmail)->send(
                    new LowStockAlert($product)
                );

                // Save the flag only after the email succeeds.
                Cache::forever($cacheKey, true);

                $this->info(
                    "Low-stock alert sent for: {$product->name}"
                );
            } catch (Throwable $exception) {
                report($exception);

                $this->error(
                    "Failed to send low-stock alert for: {$product->name}"
                );
            }
        }
    }
}
