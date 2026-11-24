<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'physical_address',
        'payment_terms',
        'lead_time_days',
        'minimum_order_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
            'minimum_order_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithLeadTime($query, int $days)
    {
        return $query->where('lead_time_days', '<=', $days);
    }

    public function getFullContactAttribute(): string
    {
        return collect([
            $this->contact_person,
            $this->email,
            $this->phone,
        ])->filter()->implode(' | ');
    }

    public function getPerformanceRatingAttribute(): string
    {
        $orders = $this->purchaseOrders()
            ->where('status', 'received')
            ->count();

        if ($orders === 0) {
            return 'No Rating';
        }

        $onTimeOrders = $this->purchaseOrders()
            ->where('status', 'received')
            ->whereColumn('received_at', '<=', 'expected_delivery_date')
            ->count();

        $percentage = ($onTimeOrders / $orders) * 100;

        if ($percentage >= 90) {
            return 'Excellent';
        }

        if ($percentage >= 75) {
            return 'Good';
        }

        if ($percentage >= 50) {
            return 'Average';
        }

        return 'Needs Improvement';
    }
}
