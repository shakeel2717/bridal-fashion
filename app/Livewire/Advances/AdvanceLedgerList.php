<?php

namespace App\Livewire\Advances;

use App\Models\Advance;
use App\Models\AdvanceLedger;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceLedgerList extends Component
{
    use WithPagination;

    public string $filterUser  = '';
    public string $filterType  = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    // Form
    public bool   $showForm    = false;
    public string $userId      = '';
    public string $type        = 'advance';
    public string $amount      = '';
    public string $ledgerDate  = '';
    public string $note        = '';
    public ?int   $deleteId    = null;
    public string $deleteSource = ''; // 'advance' or 'ledger'

    public function mount(): void
    {
        $this->ledgerDate = now()->format('Y-m-d');
        $this->dateFrom   = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo     = now()->format('Y-m-d');
    }

    public function updatedFilterUser():  void { $this->resetPage(); }
    public function updatedFilterType():  void { $this->resetPage(); }
    public function updatedDateFrom():    void { $this->resetPage(); }
    public function updatedDateTo():      void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['userId', 'amount', 'note']);
        $this->type       = 'advance';
        $this->ledgerDate = now()->format('Y-m-d');
        $this->showForm   = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'userId'     => 'required|exists:users,id',
            'type'       => 'required|in:advance,salary_deduction,cash_repayment',
            'amount'     => 'required|numeric|min:1',
            'ledgerDate' => 'required|date',
            'note'       => 'nullable|string|max:500',
        ]);

        if ($this->type === 'advance') {
            Advance::create([
                'user_id'      => $this->userId,
                'amount'       => $this->amount,
                'advance_date' => $this->ledgerDate,
                'note'         => $this->note ?: null,
                'is_deducted'  => false,
                'created_by'   => auth()->id(),
                'updated_by'   => auth()->id(),
            ]);
        } else {
            AdvanceLedger::create([
                'user_id'     => $this->userId,
                'type'        => $this->type,
                'amount'      => $this->amount,
                'ledger_date' => $this->ledgerDate,
                'note'        => $this->note ?: null,
                'created_by'  => auth()->id(),
            ]);
        }

        $this->reset(['showForm', 'userId', 'amount', 'note']);
        $this->type       = 'advance';
        $this->ledgerDate = now()->format('Y-m-d');
        $this->resetValidation();
        $this->resetPage();
        session()->flash('success', 'Entry saved.');
    }

    public function confirmDelete(int $id, string $source): void
    {
        $this->deleteId     = $id;
        $this->deleteSource = $source;
    }

    public function delete(): void
    {
        if ($this->deleteSource === 'advance') {
            Advance::findOrFail($this->deleteId)->delete();
        } else {
            AdvanceLedger::findOrFail($this->deleteId)->delete();
        }
        $this->deleteId     = null;
        $this->deleteSource = '';
        session()->flash('success', 'Entry deleted.');
    }

    public function render()
    {
        $userIds = $this->filterUser ? [$this->filterUser] : null;

        // ── Pull from advances table (type = 'advance') ──
        $advanceRows = Advance::with('user')
            ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
            ->when($this->dateFrom, fn ($q) => $q->where('advance_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->where('advance_date', '<=', $this->dateTo))
            ->when($this->filterType && $this->filterType !== 'advance', fn ($q) => $q->whereRaw('1=0'))
            ->get()
            ->map(fn ($a) => (object)[
                'source'      => 'advance',
                'source_id'   => $a->id,
                'user'        => $a->user,
                'type'        => 'advance',
                'amount'      => $a->amount,
                'ledger_date' => $a->advance_date,
                'note'        => $a->note,
            ]);

        // ── Pull from advance_ledger table ──
        $ledgerRows = AdvanceLedger::with('user')
            ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
            ->when($this->filterType && $this->filterType !== 'advance', fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterType === 'advance', fn ($q) => $q->whereRaw('1=0'))
            ->when($this->dateFrom, fn ($q) => $q->where('ledger_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->where('ledger_date', '<=', $this->dateTo))
            ->get()
            ->map(fn ($l) => (object)[
                'source'      => 'ledger',
                'source_id'   => $l->id,
                'user'        => $l->user,
                'type'        => $l->type,
                'amount'      => $l->amount,
                'ledger_date' => $l->ledger_date,
                'note'        => $l->note,
            ]);

        // If no type filter, show both
        if (!$this->filterType) {
            $advanceRows = Advance::with('user')
                ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
                ->when($this->dateFrom, fn ($q) => $q->where('advance_date', '>=', $this->dateFrom))
                ->when($this->dateTo,   fn ($q) => $q->where('advance_date', '<=', $this->dateTo))
                ->get()
                ->map(fn ($a) => (object)[
                    'source'      => 'advance',
                    'source_id'   => $a->id,
                    'user'        => $a->user,
                    'type'        => 'advance',
                    'amount'      => $a->amount,
                    'ledger_date' => $a->advance_date,
                    'note'        => $a->note,
                ]);

            $ledgerRows = AdvanceLedger::with('user')
                ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
                ->when($this->dateFrom, fn ($q) => $q->where('ledger_date', '>=', $this->dateFrom))
                ->when($this->dateTo,   fn ($q) => $q->where('ledger_date', '<=', $this->dateTo))
                ->get()
                ->map(fn ($l) => (object)[
                    'source'      => 'ledger',
                    'source_id'   => $l->id,
                    'user'        => $l->user,
                    'type'        => $l->type,
                    'amount'      => $l->amount,
                    'ledger_date' => $l->ledger_date,
                    'note'        => $l->note,
                ]);
        }

        // Merge and sort by date desc
        $allEntries = $advanceRows->concat($ledgerRows)
            ->sortByDesc(fn ($e) => $e->ledger_date->format('Y-m-d') ?? $e->ledger_date)
            ->values();

        // Manual pagination
        $perPage     = 30;
        $currentPage = $this->getPage();
        $total       = $allEntries->count();
        $entries     = $allEntries->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginator   = new \Illuminate\Pagination\LengthAwarePaginator(
            $entries, $total, $perPage, $currentPage,
            ['path' => request()->url()]
        );

        // ── Totals across full filtered set ──
        $totalAdvances   = $advanceRows->sum('amount');
        $totalDeductions = $ledgerRows->where('type', 'salary_deduction')->sum('amount');
        $totalRepayments = $ledgerRows->where('type', 'cash_repayment')->sum('amount');
        $totalCredits    = $totalDeductions + $totalRepayments;
        $netBalance      = $totalAdvances - $totalCredits;

        $employees = User::where('is_active', true)->orderBy('name')->get();

        return view('livewire.advances.advance-ledger-list', [
            'entries'          => $paginator,
            'employees'        => $employees,
            'totalAdvances'    => $totalAdvances,
            'totalDeductions'  => $totalDeductions,
            'totalRepayments'  => $totalRepayments,
            'totalCredits'     => $totalCredits,
            'netBalance'       => $netBalance,
        ]);
    }
}
