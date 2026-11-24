<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class SupplierReportController extends Controller
{
    /**
     * Get supplier performance data for the report and exports.
     */
    private function getSupplierPerformanceData()
    {
        $suppliers = Supplier::query()
            ->withCount([
                'purchaseOrders',
                'purchaseOrders as received_orders_count' => function ($query) {
                    $query->where('status', PurchaseOrder::STATUS_RECEIVED);
                },
                'purchaseOrders as pending_orders_count' => function ($query) {
                    $query->whereIn('status', [
                        PurchaseOrder::STATUS_PENDING,
                        PurchaseOrder::STATUS_APPROVED,
                        PurchaseOrder::STATUS_SENT,
                    ]);
                },
                'purchaseOrders as on_time_orders_count' => function ($query) {
                    $query->where('status', PurchaseOrder::STATUS_RECEIVED)
                        ->whereNotNull('received_at')
                        ->whereNotNull('expected_delivery_date')
                        ->whereRaw('DATE(received_at) <= expected_delivery_date');
                },
            ])
            ->orderBy('company_name')
            ->get();

        foreach ($suppliers as $supplier) {
            $received = (int) $supplier->received_orders_count;

            $supplier->on_time_percentage = $received > 0
                ? ((int) $supplier->on_time_orders_count / $received) * 100
                : null;

            $supplier->report_rating = match (true) {
                $received === 0 => 'No Rating',
                $supplier->on_time_percentage >= 90 => 'Excellent',
                $supplier->on_time_percentage >= 75 => 'Good',
                $supplier->on_time_percentage >= 50 => 'Average',
                default => 'Needs Improvement',
            };
        }

        return $suppliers;
    }

    /**
     * Display the supplier performance report.
     */
    public function index()
    {
        $suppliers = $this->getSupplierPerformanceData();

        $totalSuppliers = $suppliers->count();
        $totalOrders = $suppliers->sum('purchase_orders_count');
        $totalReceivedOrders = $suppliers->sum('received_orders_count');
        $totalPendingOrders = $suppliers->sum('pending_orders_count');

        return view('suppliers.performance', compact(
            'suppliers',
            'totalSuppliers',
            'totalOrders',
            'totalReceivedOrders',
            'totalPendingOrders'
        ));
    }

    /**
     * Download supplier performance report as CSV.
     */
    public function exportCsv()
    {
        $suppliers = $this->getSupplierPerformanceData();

        $filename = 'supplier-performance-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($suppliers) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for spreadsheet compatibility.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Supplier',
                'Email',
                'Total Orders',
                'Received Orders',
                'Open Orders',
                'On-Time Deliveries',
                'On-Time Percentage',
                'Performance Rating',
            ]);

            foreach ($suppliers as $supplier) {
                fputcsv($output, [
                    $supplier->company_name,
                    $supplier->email,
                    $supplier->purchase_orders_count,
                    $supplier->received_orders_count,
                    $supplier->pending_orders_count,
                    $supplier->on_time_orders_count,
                    $supplier->on_time_percentage !== null
                        ? number_format($supplier->on_time_percentage, 1) . '%'
                        : 'N/A',
                    $supplier->report_rating,
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Download supplier performance report as PDF.
     */
    public function exportPdf()
    {
        $suppliers = $this->getSupplierPerformanceData();

        $totalSuppliers = $suppliers->count();
        $totalOrders = $suppliers->sum('purchase_orders_count');
        $totalReceivedOrders = $suppliers->sum('received_orders_count');
        $totalPendingOrders = $suppliers->sum('pending_orders_count');

        $pdf = Pdf::loadView('suppliers.performance-pdf', compact(
            'suppliers',
            'totalSuppliers',
            'totalOrders',
            'totalReceivedOrders',
            'totalPendingOrders'
        ));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'supplier-performance-' . now()->format('Y-m-d') . '.pdf'
        );
    }
}
