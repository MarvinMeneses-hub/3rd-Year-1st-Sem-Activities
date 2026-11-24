<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return in_array($purchaseOrder->status, ['draft', 'rejected'], true);
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'draft';
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'pending';
    }

    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'pending';
    }

    public function send(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'approved';
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'sent';
    }
}
