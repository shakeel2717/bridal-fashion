<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Rental;
use App\Models\Sale;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use Livewire\Component;

class CustomerVendorReport extends Component
{
    public string $activeTab = 'customers';
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public string $search    = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        // Customer report
        $customerData = collect();
        if ($this->activeTab === 'customers') {
            $customerData = Customer::where('is_walkin', false)
                ->when($this->search, fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone1', 'like', "%{$this->search}%")
                )
                ->withCount([
                    'rentals as rental_count' => fn($q) =>
                        $q->whereRaw('DATE(booking_date) >= ?', [$this->dateFrom])
                          ->whereRaw('DATE(booking_date) <= ?', [$this->dateTo])
                          ->whereNotIn('status', ['cancelled', 'abandoned']),
                ])
                ->get()
                ->map(function($customer) {
                    $rentals = \App\Models\Rental::where('customer_id', $customer->id)
                        ->whereRaw('DATE(booking_date) >= ?', [$this->dateFrom])
                        ->whereRaw('DATE(booking_date) <= ?', [$this->dateTo])
                        ->whereNotIn('status', ['cancelled', 'abandoned'])
                        ->get();

                    $sales = \App\Models\Sale::where('customer_id', $customer->id)
                        ->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom])
                        ->whereRaw('DATE(sale_date) <= ?', [$this->dateTo])
                        ->whereNotIn('status', ['cancelled'])
                        ->get();

                    return [
                        'id'             => $customer->id,
                        'name'           => $customer->name,
                        'phone'          => $customer->phone1,
                        'cnic'           => $customer->cnic,
                        'rental_count'   => $rentals->count(),
                        'sale_count'     => $sales->count(),
                        'total_rental'   => $rentals->sum('total_amount'),
                        'total_paid'     => $rentals->sum('advance_paid') + $sales->sum('advance_paid'),
                        'total_balance'  => $rentals->sum('remaining_balance'),
                        'last_visit'     => $rentals->max('booking_date') ?? $sales->max('sale_date'),
                    ];
                })
                ->filter(fn($c) => $c['rental_count'] > 0 || $c['sale_count'] > 0)
                ->sortByDesc('rental_count')
                ->values();
        }

        // Vendor report
        $vendorData = collect();
        if ($this->activeTab === 'vendors') {
            $vendorData = Vendor::when($this->search, fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                )
                ->get()
                ->map(function($vendor) {
                    $pos = PurchaseOrder::where('vendor_id', $vendor->id)
                        ->whereRaw('DATE(order_date) >= ?', [$this->dateFrom])
                        ->whereRaw('DATE(order_date) <= ?', [$this->dateTo])
                        ->whereNotIn('status', ['cancelled'])
                        ->get();

                    return [
                        'id'             => $vendor->id,
                        'name'           => $vendor->name,
                        'phone'          => $vendor->phone,
                        'po_count'       => $pos->count(),
                        'total_po'       => $pos->sum('total_amount'),
                        'total_paid'     => $pos->sum('amount_paid'),
                        'total_balance'  => $pos->sum('balance_due'),
                        'last_order'     => $pos->max('order_date'),
                    ];
                })
                ->filter(fn($v) => $v['po_count'] > 0)
                ->sortByDesc('total_po')
                ->values();
        }

        return view('livewire.reports.customer-vendor-report',
            compact('customerData', 'vendorData'));
    }
}