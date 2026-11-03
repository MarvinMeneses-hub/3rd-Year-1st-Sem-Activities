<?php

namespace App\Observers;

use App\Models\InventoryTransaction;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransactionObserver
{
    public function created(InventoryTransaction $transaction): void
    {
        // The transaction controller wraps both the transaction insert and this
        // observer update in one outer DB transaction.
        $product = $transaction->product()->lockForUpdate()->firstOrFail();

        $delta = match ($transaction->type) {
            'stock_in' => abs($transaction->quantity),
            'stock_out' => -abs($transaction->quantity),
            'adjustment' => $transaction->quantity,
            default => throw ValidationException::withMessages([
                'type' => 'Invalid inventory transaction type.',
            ]),
        };

        $newQuantity = $product->quantity + $delta;

        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock. This transaction would create negative inventory.',
            ]);
        }

        $product->quantity = $newQuantity;
        $product->save();

        if ($product->isOutOfStock() || $product->isLowStock()) {
            $status = $product->isOutOfStock() ? 'out of stock' : 'low on stock';
            $transaction->user?->notify(new LowStockNotification($product, $status));
        }
    }
}
