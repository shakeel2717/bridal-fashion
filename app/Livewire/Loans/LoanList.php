<?php

namespace App\Livewire\Loans;

use App\Models\Lender;
use Livewire\Component;

class LoanList extends Component
{
    // ── Add Lender Form ───────────────────────────────────
    public bool $showForm = false;

    public string $name     = '';
    public string $phone    = '';
    public string $relation = '';
    public string $notes    = '';

    public ?int $editId = null;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $lender = Lender::findOrFail($id);
        $this->editId   = $id;
        $this->name     = $lender->name;
        $this->phone    = $lender->phone ?? '';
        $this->relation = $lender->relation ?? '';
        $this->notes    = $lender->notes ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'     => 'required|string|max:120',
            'phone'    => 'nullable|string|max:25',
            'relation' => 'nullable|string|max:60',
            'notes'    => 'nullable|string|max:1000',
        ]);

        if ($this->editId) {
            Lender::findOrFail($this->editId)->update([
                'name'     => $this->name,
                'phone'    => $this->phone ?: null,
                'relation' => $this->relation ?: null,
                'notes'    => $this->notes ?: null,
            ]);
            session()->flash('success', 'Lender updated.');
        } else {
            Lender::create([
                'name'       => $this->name,
                'phone'      => $this->phone ?: null,
                'relation'   => $this->relation ?: null,
                'notes'      => $this->notes ?: null,
                'created_by' => auth()->id(),
            ]);
            session()->flash('success', 'Lender added.');
        }

        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $lender = Lender::findOrFail($id);
        $lender->update(['is_active' => ! $lender->is_active]);
    }

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editId   = null;
        $this->name     = '';
        $this->phone    = '';
        $this->relation = '';
        $this->notes    = '';
        $this->resetValidation();
    }

    public function render()
    {
        $lenders = Lender::orderBy('name')->get();

        // Aggregate totals across all lenders
        $grandReceived    = $lenders->sum(fn ($l) => $l->totalReceived());
        $grandPaid        = $lenders->sum(fn ($l) => $l->totalPaid());
        $grandOutstanding = max(0, $grandReceived - $grandPaid);

        return view('livewire.loans.loan-list', compact(
            'lenders', 'grandReceived', 'grandPaid', 'grandOutstanding'
        ));
    }
}
