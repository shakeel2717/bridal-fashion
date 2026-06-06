<?php

namespace App\Livewire\Employees;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterRole   = 'employee';
    public string $filterStatus = 'active';
    public ?int   $deleteId     = null;

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterRole(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $user = User::findOrFail($this->deleteId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->deleteId = null;
            return;
        }

        $user->delete();
        $this->deleteId = null;
        session()->flash('success', 'Employee deleted.');
    }

    #[On('employee-saved')]
    public function employeeSaved(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $employees = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                      ->orWhere('cnic', 'like', "%{$this->search}%")
                      ->orWhere('designation', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterRole, fn($q) => $q->where('role', $this->filterRole))
            ->when($this->filterStatus === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15);

        $counts = [
            'admin'    => User::where('role', 'admin')->count(),
            'employee' => User::where('role', 'employee')->count(),
            'active'   => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('livewire.employees.employee-list', compact('employees', 'counts'));
    }
}