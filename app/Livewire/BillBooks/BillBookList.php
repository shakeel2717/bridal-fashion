<?php

namespace App\Livewire\BillBooks;

use App\Models\BillBook;
use App\Models\Rental;
use App\Models\Sale;
use Livewire\Component;

class BillBookList extends Component
{
    public string $search     = '';
    public string $filterType = '';
    public bool   $showForm   = false;

    // Form fields
    public string $name      = '';
    public string $prefix    = '';
    public string $rangeFrom = '';
    public string $rangeTo   = '';
    public string $type      = 'both';
    public string $notes     = '';
    public ?int   $editId    = null;

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $book = BillBook::findOrFail($id);
            $this->editId    = $id;
            $this->name      = $book->name;
            $this->prefix    = $book->prefix ?? '';
            $this->rangeFrom = (string) $book->range_from;
            $this->rangeTo   = (string) $book->range_to;
            $this->type      = $book->type;
            $this->notes     = $book->notes ?? '';
        }
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'      => 'required|string|max:100',
            'rangeFrom' => 'required|integer|min:1',
            'rangeTo'   => 'required|integer|gte:rangeFrom',
            'type'      => 'required|in:rental,sale,both',
        ]);

        $data = [
            'name'       => $this->name,
            'prefix'     => $this->prefix ?: null,
            'range_from' => (int) $this->rangeFrom,
            'range_to'   => (int) $this->rangeTo,
            'type'       => $this->type,
            'notes'      => $this->notes ?: null,
            'created_by' => auth()->id(),
        ];

        if ($this->editId) {
            BillBook::findOrFail($this->editId)->update($data);
        } else {
            BillBook::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $book = BillBook::findOrFail($id);
        $book->update(['is_active' => !$book->is_active]);
    }

    public function delete(int $id): void
    {
        BillBook::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->name      = '';
        $this->prefix    = '';
        $this->rangeFrom = '';
        $this->rangeTo   = '';
        $this->type      = 'both';
        $this->notes     = '';
        $this->editId    = null;
    }

    public function render()
    {
        $books = BillBook::with('createdBy')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->latest()
            ->get()
            ->map(function ($book) {
                // Count used refs across rentals and sales
                $usedRentals = 0;
                $usedSales   = 0;
                $total       = $book->range_to - $book->range_from + 1;

                if (in_array($book->type, ['rental', 'both'])) {
                    $refs = collect(range($book->range_from, $book->range_to))
                        ->map(fn($n) => $book->buildRef($n));
                    $usedRentals = Rental::whereIn('bill_ref', $refs)->count();
                }
                if (in_array($book->type, ['sale', 'both'])) {
                    $refs = collect(range($book->range_from, $book->range_to))
                        ->map(fn($n) => $book->buildRef($n));
                    $usedSales = Sale::whereIn('bill_ref', $refs)->count();
                }

                $used    = $usedRentals + $usedSales;
                $missing = $total - $used;

                return [
                    'id'        => $book->id,
                    'name'      => $book->name,
                    'prefix'    => $book->prefix,
                    'from'      => $book->range_from,
                    'to'        => $book->range_to,
                    'type'      => $book->type,
                    'notes'     => $book->notes,
                    'is_active' => $book->is_active,
                    'total'     => $total,
                    'used'      => $used,
                    'missing'   => $missing,
                    'pct'       => $total > 0 ? round($used / $total * 100) : 0,
                    'created_by'=> $book->createdBy?->name,
                ];
            });

        $stats = [
            'total'   => $books->count(),
            'active'  => $books->where('is_active', true)->count(),
            'rental'  => $books->whereIn('type', ['rental', 'both'])->count(),
            'sale'    => $books->whereIn('type', ['sale', 'both'])->count(),
        ];

        return view('livewire.bill-books.bill-book-list', compact('books', 'stats'));
    }
}