<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_number',
        'notes',
        'submission_token',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the product associated with this transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who recorded this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope transactions to stock-in records.
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'stock_in');
    }

    /**
     * Scope transactions to stock-out records.
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'stock_out');
    }

    /**
     * Scope transactions to recent records.
     */
    public function scopeRecent($query)
    {
        return $query->latest();
    }
}