<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class VendorList extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $name     = '';
    public string $phone    = '';
    public string $address  = '';
    public string $notes    = '';
    public bool   $isActive = true;
    public bool   $showForm = false;
    public ?int   $editId   = null;
    public ?int   $deleteId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $vendor          = Vendor::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $vendor->name;
        $this->phone     = $vendor->phone ?? '';
        $this->address   = $vendor->address ?? '';
        $this->notes     = $vendor->notes ?? '';
        $this->isActive  = $vendor->is_active;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'    => 'required|string|max:150',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:1000',
        ]);

        $data = [
            'name'       => $this->name,
            'phone'      => $this->phone ?: null,
            'address'    => $this->address ?: null,
            'notes'      => $this->notes ?: null,
            'is_active'  => $this->isActive,
            'updated_by' => auth()->id(),
        ];

        if ($this->editId) {
            Vendor::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Vendor updated.');
        } else {
            $data['created_by'] = auth()->id();
            Vendor::create($data);
            session()->flash('success', 'Vendor added.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'is_active'  => !$vendor->is_active,
            'updated_by' => auth()->id(),
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $vendor = Vendor::findOrFail($this->deleteId);

        if ($vendor->products()->count() > 0) {
            session()->flash('error', 'Cannot delete — vendor has products assigned.');
            $this->deleteId = null;
            return;
        }

        $vendor->delete();
        $this->deleteId = null;
        session()->flash('success', 'Vendor deleted.');
    }

    public function resetForm(): void
    {
        $this->editId   = null;
        $this->name     = '';
        $this->phone    = '';
        $this->address  = '';
        $this->notes    = '';
        $this->isActive = true;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        $vendors = Vendor::withCount('products')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate(15);

        return view('livewire.vendors.vendor-list', compact('vendors'));
    }
}