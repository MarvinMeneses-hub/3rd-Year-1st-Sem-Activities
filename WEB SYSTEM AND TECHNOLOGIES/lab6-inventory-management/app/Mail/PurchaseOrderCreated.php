<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $purchaseOrder
    ) {
        $this->purchaseOrder->loadMissing([
            'supplier',
            'items.product',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Purchase Order: ' . $this->purchaseOrder->po_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-orders.created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}