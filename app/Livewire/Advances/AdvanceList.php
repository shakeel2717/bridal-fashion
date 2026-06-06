<?php

namespace App\Livewire\Advances;

use App\Models\Advance;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceList extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterUser    = '';
    public string $filterStatus  = '';
    public ?int   $deleteId      = null;

    // Form
    public bool   $showForm      = false;
    public ?int   $editId        = null;
    public string $userId        = '';
    public string $amount        = '';
    public string $advanceDate   = '';
    public string $note          = '';

    public function mount(): void
    {
        $this->advanceDate = now()->format('Y-m-d');
    }

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedFilterUser(): void  { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm    = true;
        $this->advanceDate = now()->format('Y-m-d');
    }

    public function openEdit(int $id): void
    {
        $advance            = Advance::findOrFail($id);
        $this->editId       = $id;
        $this->userId       = (string) $advance->user_id;
        $this->amount       = (string) $advance->amount;
        $this->advanceDate  = \Carbon\Carbon::parse($advance->advance_date)->format('Y-m-d');
        $this->note         = $advance->note ?? '';
        $this->showForm     = true;
    }

    public function save(): void
    {
        $this->validate([
            'userId'      => 'required|exists:users,id',
            'amount'      => 'required|numeric|min:1',
            'advanceDate' => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        $data = [
            'user_id'      => $this->userId,
            'amount'       => $this->amount,
            'advance_date' => $this->advanceDate,
            'note'         => $this->note ?: null,
            'updated_by'   => auth()->id(),
        ];

        if ($this->editId) {
            Advance::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Advance updated.');
        } else {
            $data['created_by']  = auth()->id();
            $data['is_deducted'] = false;
            Advance::create($data);
            session()->flash('success', 'Advance recorded.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $advance = Advance::findOrFail($id);
        if ($advance->is_deducted) {
            session()->flash('error', 'Cannot delete — already deducted from salary.');
            return;
        }
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        Advance::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        session()->flash('success', 'Advance deleted.');
    }

    public function resetForm(): void
    {
        $this->showForm    = false;
        $this->editId      = null;
        $this->userId      = '';
        $this->amount      = '';
        $this->advanceDate = now()->format('Y-m-d');
        $this->note        = '';
        $this->resetValidation();
    }

    public function render()
    {
        $advances = Advance::with('user')
            ->when($this->search, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterUser, fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterStatus === 'deducted',     fn($q) => $q->where('is_deducted', true))
            ->when($this->filterStatus === 'not_deducted', fn($q) => $q->where('is_deducted', false))
            ->latest('advance_date')
            ->paginate(15);

        $employees = User::where('is_active', true)->orderBy('name')->get();

        $totalPending = Advance::where('is_deducted', false)->sum('amount');

        return view('livewire.advances.advance-list',
            compact('advances', 'employees', 'totalPending'));
    }
}