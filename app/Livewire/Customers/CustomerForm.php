<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerForm extends Component
{
    use WithFileUploads;

    public ?int $customerId   = null;
    public string $name       = '';
    public string $phone1     = '';
    public string $phone2     = '';
    public string $whatsapp   = '';
    public string $cnic       = '';
    public string $city       = '';
    public string $address    = '';
    public string $notes      = '';
    public $photo             = null;
    public $cnicFront         = null;
    public $cnicBack          = null;
    public ?string $existingPhoto     = null;
    public ?string $existingCnicFront = null;
    public ?string $existingCnicBack  = null;
    public bool $isEdit       = false;

    public function mount(?int $customerId = null): void
    {
        if ($customerId) {
            $this->customerId = $customerId;
            $this->isEdit     = true;
            $this->loadCustomer();
        }
    }

    public function loadCustomer(): void
    {
        $customer = Customer::findOrFail($this->customerId);

        $this->name               = $customer->name;
        $this->phone1             = $customer->phone1;
        $this->phone2             = $customer->phone2 ?? '';
        $this->whatsapp           = $customer->whatsapp ?? '';
        $this->cnic               = $customer->cnic ?? '';
        $this->city               = $customer->city ?? '';
        $this->address            = $customer->address ?? '';
        $this->notes              = $customer->notes ?? '';
        $this->existingPhoto      = $customer->photo;
        $this->existingCnicFront  = $customer->cnic_front;
        $this->existingCnicBack   = $customer->cnic_back;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-customer-modal');
    }

    public function openEdit(int $id): void
    {
        $this->customerId = $id;
        $this->isEdit     = true;
        $this->loadCustomer();
        $this->dispatch('open-customer-modal');
    }

    public function save(): void
    {
        $this->validate([
            'name'      => 'required|string|max:150',
            'phone1'    => 'required|string|max:20',
            'phone2'    => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
            'cnic'      => 'nullable|string|max:20',
            'city'      => 'nullable|string|max:100',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string|max:1000',
            'photo'     => 'nullable|image|max:2048',
            'cnicFront' => 'nullable|image|max:2048',
            'cnicBack'  => 'nullable|image|max:2048',
        ]);

        $photoPath     = $this->existingPhoto;
        $cnicFrontPath = $this->existingCnicFront;
        $cnicBackPath  = $this->existingCnicBack;

        if ($this->photo)     $photoPath     = $this->photo->store('customers/photos', 'public');
        if ($this->cnicFront) $cnicFrontPath = $this->cnicFront->store('customers/cnic', 'public');
        if ($this->cnicBack)  $cnicBackPath  = $this->cnicBack->store('customers/cnic', 'public');

        $data = [
            'name'       => $this->name,
            'phone1'     => $this->phone1,
            'phone2'     => $this->phone2 ?: null,
            'whatsapp'   => $this->whatsapp ?: null,
            'cnic'       => $this->cnic ?: null,
            'city'       => $this->city ?: null,
            'address'    => $this->address ?: null,
            'notes'      => $this->notes ?: null,
            'photo'      => $photoPath,
            'cnic_front' => $cnicFrontPath,
            'cnic_back'  => $cnicBackPath,
            'is_walkin'  => false,
            'updated_by' => auth()->id(),
        ];

        if ($this->isEdit) {
            Customer::findOrFail($this->customerId)->update($data);
            session()->flash('success', 'Customer updated successfully.');
        } else {
            $data['created_by'] = auth()->id();
            Customer::create($data);
            session()->flash('success', 'Customer added successfully.');
        }

        $this->dispatch('customer-saved');
        $this->dispatch('close-customer-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->customerId        = null;
        $this->isEdit            = false;
        $this->name              = '';
        $this->phone1            = '';
        $this->phone2            = '';
        $this->whatsapp          = '';
        $this->cnic              = '';
        $this->city              = '';
        $this->address           = '';
        $this->notes             = '';
        $this->photo             = null;
        $this->cnicFront         = null;
        $this->cnicBack          = null;
        $this->existingPhoto     = null;
        $this->existingCnicFront = null;
        $this->existingCnicBack  = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.customers.customer-form');
    }
}