<?php

namespace App\Livewire\BillBooks;

use App\Models\BillBook;
use App\Models\Rental;
use App\Models\Sale;
use Livewire\Component;

class BillBookDetail extends Component
{
    public BillBook $billBook;
    public string   $filterStatus = ''; // '' | 'used' | 'deleted' | 'missing'
    public string   $search       = '';

    public function mount(BillBook $billBook): void
    {
        $this->billBook = $billBook;
    }

    public function render()
    {
        $book   = $this->billBook;
        $rows   = [];
        $used   = 0;
        $missing = 0;
        $deleted = 0;

        // Pre-fetch all matching rentals and sales in one query each
        $allRefs = collect(range($book->range_from, $book->range_to))
            ->map(fn($n) => $book->buildRef($n));

        $rentals = Rental::whereIn('bill_ref', $allRefs)
            ->get(['id', 'bill_ref', 'customer_name', 'booking_date', 'status', 'total_amount'])
            ->keyBy('bill_ref');

        $sales = Sale::whereIn('bill_ref', $allRefs)
            ->get(['id', 'bill_ref', 'customer_name', 'sale_date', 'status', 'total_amount'])
            ->keyBy('bill_ref');

        // Soft-deleted records — latest deletion wins when a ref was deleted more than once
        $trashedRentals = Rental::onlyTrashed()->whereIn('bill_ref', $allRefs)
            ->orderBy('deleted_at')
            ->get(['id', 'bill_ref', 'customer_name', 'booking_date', 'total_amount', 'deleted_at'])
            ->keyBy('bill_ref');

        $trashedSales = Sale::onlyTrashed()->whereIn('bill_ref', $allRefs)
            ->orderBy('deleted_at')
            ->get(['id', 'bill_ref', 'customer_name', 'sale_date', 'total_amount', 'deleted_at'])
            ->keyBy('bill_ref');

        foreach (range($book->range_from, $book->range_to) as $number) {
            $ref     = $book->buildRef($number);
            $rental  = $rentals->get($ref);
            $sale    = $sales->get($ref);
            $isUsed  = $rental || $sale;

            // Only treat as deleted when no active record holds the number
            $deletedRecord = null;
            if (!$isUsed) {
                $trashedRental = $trashedRentals->get($ref);
                $trashedSale   = $trashedSales->get($ref);
                if ($trashedRental && (!$trashedSale || $trashedRental->deleted_at >= $trashedSale->deleted_at)) {
                    $deletedRecord = [
                        'type'          => 'Rental',
                        'customer_name' => $trashedRental->customer_name,
                        'date'          => $trashedRental->booking_date,
                        'amount'        => $trashedRental->total_amount,
                        'deleted_at'    => $trashedRental->deleted_at,
                    ];
                } elseif ($trashedSale) {
                    $deletedRecord = [
                        'type'          => 'Sale',
                        'customer_name' => $trashedSale->customer_name,
                        'date'          => $trashedSale->sale_date,
                        'amount'        => $trashedSale->total_amount,
                        'deleted_at'    => $trashedSale->deleted_at,
                    ];
                }
            }
            $isDeleted = $deletedRecord !== null;

            if ($isUsed) $used++;
            elseif ($isDeleted) $deleted++;
            else $missing++;

            // Apply filter
            if ($this->filterStatus === 'used'    && !$isUsed) continue;
            if ($this->filterStatus === 'deleted' && !$isDeleted) continue;
            if ($this->filterStatus === 'missing' && ($isUsed || $isDeleted)) continue;

            // Apply search
            if ($this->search) {
                $haystack = strtolower($ref . ($rental?->customer_name ?? '') . ($sale?->customer_name ?? '') . ($deletedRecord['customer_name'] ?? ''));
                if (!str_contains($haystack, strtolower($this->search))) continue;
            }

            $rows[] = [
                'number'  => $number,
                'ref'     => $ref,
                'is_used' => $isUsed,
                'deleted' => $deletedRecord,
                'rental'  => $rental ? [
                    'id'            => $rental->id,
                    'customer_name' => $rental->customer_name,
                    'date'          => $rental->booking_date,
                    'status'        => $rental->status,
                    'amount'        => $rental->total_amount,
                ] : null,
                'sale'    => $sale ? [
                    'id'            => $sale->id,
                    'customer_name' => $sale->customer_name,
                    'date'          => $sale->sale_date,
                    'status'        => $sale->status,
                    'amount'        => $sale->total_amount,
                ] : null,
            ];
        }

        $total = $book->range_to - $book->range_from + 1;

        return view('livewire.bill-books.bill-book-detail', compact('rows', 'total', 'used', 'missing', 'deleted'));
    }
}