<?php

namespace App\Livewire\Reports;

use App\Models\PurchaseOrder;
use App\Models\RentalPayment;
use App\Models\Sale;
use Livewire\Component;

class PurchaseSaleReport extends Component
{
    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        // Purchase Orders in date range
        $purchaseOrders = PurchaseOrder::with(['vendor', 'items'])
            ->whereRaw('DATE(order_date) >= ?', [$this->dateFrom])
            ->whereRaw('DATE(order_date) <= ?', [$this->dateTo])
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('order_date')
            ->get();

        $totalPurchases  = $purchaseOrders->sum('total_amount');
        $totalPOPaid     = $purchaseOrders->sum('amount_paid');
        $totalPOBalance  = $purchaseOrders->sum('balance_due');

        // Sales in date range
        $sales = Sale::with(['customer', 'items'])
            ->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom])
            ->whereRaw('DATE(sale_date) <= ?', [$this->dateTo])
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('sale_date')
            ->get();

        $totalSalesRevenue = $sales->sum('total_amount');
        $totalSalesPaid    = $sales->sum('advance_paid');

        // Rental payments in date range
        $rentalRevenue = RentalPayment::whereRaw('DATE(payment_date) >= ?', [$this->dateFrom])
            ->whereRaw('DATE(payment_date) <= ?', [$this->dateTo])
            ->sum('amount');

        $totalIncome = $totalSalesPaid + $rentalRevenue;
        $netPosition = $totalIncome - $totalPOPaid;

        return view('livewire.reports.purchase-sale-report', compact(
            'purchaseOrders', 'totalPurchases', 'totalPOPaid', 'totalPOBalance',
            'sales', 'totalSalesRevenue', 'totalSalesPaid',
            'rentalRevenue', 'totalIncome', 'netPosition'
        ));
    }
}