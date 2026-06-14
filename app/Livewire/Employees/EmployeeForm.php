<?php

namespace App\Livewire\Employees;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmployeeForm extends Component
{
    use WithFileUploads;

    public ?int $employeeId = null;

    public string $resign_date = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'employee';

    public string $phone = '';

    public string $cnic = '';

    public string $address = '';

    public string $designation = '';

    public string $joiningDate = '';

    public string $salaryType = 'monthly';

    public string $salaryAmount = '';

    public bool $isActive = true;

    public $photo = null;

    public ?string $existingPhoto = null;

    public bool $isEdit = false;

    #[On('open-create-employee')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-employee-modal');
    }

    #[On('open-edit-employee')]
    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->employeeId = $id;
        $this->isEdit = true;
        $this->resign_date = $user->resign_date?->format('Y-m-d') ?? '';
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->phone = $user->phone ?? '';
        $this->cnic = $user->cnic ?? '';
        $this->address = $user->address ?? '';
        $this->designation = $user->designation ?? '';
        $this->joiningDate = $user->joining_date?->format('Y-m-d') ?? '';
        $this->salaryType = $user->salary_type;
        $this->salaryAmount = (string) $user->salary_amount;
        $this->isActive = $user->is_active;
        $this->existingPhoto = $user->photo;

        $this->dispatch('open-employee-modal');
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:150',
            'email' => [
                'required', 'email',
                Rule::unique('users', 'email')->ignore($this->employeeId)->whereNull('deleted_at'),
            ],
            'role' => 'required|in:admin,employee',
            'phone' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'resign_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'designation' => 'nullable|string|max:100',
            'joiningDate' => 'nullable|date',
            'salaryType' => 'required|in:monthly,daily',
            'salaryAmount' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ];

        if (! $this->isEdit) {
            $rules['password'] = 'required|string|min:6';
        } else {
            $rules['password'] = 'nullable|string|min:6';
        }

        $this->validate($rules);

        $photoPath = $this->existingPhoto;
        if ($this->photo) {
            $photoPath = $this->photo->store('employees', 'public');
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone ?: null,
            'cnic' => $this->cnic ?: null,
            'resign_date' => $this->resign_date ?: null,
            'address' => $this->address ?: null,
            'designation' => $this->designation ?: null,
            'joining_date' => $this->joiningDate ?: null,
            'salary_type' => $this->salaryType,
            'salary_amount' => $this->salaryAmount,
            'is_active' => $this->isActive,
            'photo' => $photoPath,
            'updated_by' => auth()->id(),
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEdit) {
            User::findOrFail($this->employeeId)->update($data);
            session()->flash('success', 'Employee updated successfully.');
        } else {
            $data['created_by'] = auth()->id();
            User::create($data);
            session()->flash('success', 'Employee added successfully.');
        }

        $this->dispatch('employee-saved');
        $this->dispatch('close-employee-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->employeeId = null;
        $this->isEdit = false;
        $this->resign_date = '';
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'employee';
        $this->phone = '';
        $this->cnic = '';
        $this->address = '';
        $this->designation = '';
        $this->joiningDate = '';
        $this->salaryType = 'monthly';
        $this->salaryAmount = '';
        $this->isActive = true;
        $this->photo = null;
        $this->existingPhoto = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.employees.employee-form');
    }
}
