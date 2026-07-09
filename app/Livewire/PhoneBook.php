<?php

namespace App\Livewire;

use App\Models\PhonebookCategory;
use App\Models\PhonebookContact;
use Livewire\Component;

class PhoneBook extends Component
{
    // ─── Filter ───────────────────────────────────────────────────────────────
    public string $search         = '';
    public ?int   $filterCategory = null;

    // ─── Contact Form (side panel) ────────────────────────────────────────────
    public bool   $showForm          = false;
    public ?int   $editId            = null;
    public string $contactName       = '';
    public ?int   $contactCategoryId = null;
    public array  $contactPhones     = [''];
    public string $contactNotes      = '';

    // ─── Category Manager ─────────────────────────────────────────────────────
    public bool   $showCatManager = false;
    public bool   $showCatForm    = false;
    public ?int   $editCatId      = null;
    public string $catName        = '';

    // ─── Delete ───────────────────────────────────────────────────────────────
    public ?int $deleteId    = null;
    public ?int $deleteCatId = null;

    // ─── Contact Methods ──────────────────────────────────────────────────────

    public function openForm(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $c = PhonebookContact::findOrFail($id);
            $this->editId            = $id;
            $this->contactName       = $c->name;
            $this->contactCategoryId = $c->phonebook_category_id;
            $this->contactPhones     = count($c->phone_numbers) ? $c->phone_numbers : [''];
            $this->contactNotes      = $c->notes ?? '';
        }

        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->editId            = null;
        $this->contactName       = '';
        $this->contactCategoryId = null;
        $this->contactPhones     = [''];
        $this->contactNotes      = '';
        $this->showForm          = false;
        $this->resetValidation();
    }

    public function addPhone(): void
    {
        $this->contactPhones[] = '';
    }

    public function removePhone(int $index): void
    {
        if (count($this->contactPhones) > 1) {
            array_splice($this->contactPhones, $index, 1);
            $this->contactPhones = array_values($this->contactPhones);
        }
    }

    public function save(): void
    {
        $this->validate([
            'contactName'       => 'required|string|max:100',
            'contactPhones'     => 'required|array|min:1',
            'contactPhones.*'   => 'required|string|max:30',
            'contactCategoryId' => 'nullable|exists:phonebook_categories,id',
            'contactNotes'      => 'nullable|string|max:500',
        ], [
            'contactPhones.*.required' => 'Phone number cannot be empty.',
        ]);

        $phones = array_values(array_filter($this->contactPhones, fn($p) => filled($p)));

        $data = [
            'name'                  => $this->contactName,
            'phonebook_category_id' => $this->contactCategoryId,
            'phone_numbers'         => $phones,
            'notes'                 => $this->contactNotes ?: null,
            'created_by'            => auth()->id(),
        ];

        if ($this->editId) {
            PhonebookContact::findOrFail($this->editId)->update($data);
        } else {
            PhonebookContact::create($data);
        }

        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            PhonebookContact::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
        }
    }

    // ─── Category Methods ─────────────────────────────────────────────────────

    public function openCatManager(): void
    {
        $this->resetCatForm();
        $this->showCatManager = true;
    }

    public function closeCatManager(): void
    {
        $this->showCatManager = false;
        $this->resetCatForm();
    }

    public function openCatForm(?int $id = null): void
    {
        $this->resetCatForm();
        if ($id) {
            $cat = PhonebookCategory::findOrFail($id);
            $this->editCatId = $id;
            $this->catName   = $cat->name;
        }
        $this->showCatForm = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'catName' => 'required|string|max:60|unique:phonebook_categories,name,' . ($this->editCatId ?? 'NULL'),
        ]);

        $data = ['name' => $this->catName, 'created_by' => auth()->id()];

        if ($this->editCatId) {
            PhonebookCategory::findOrFail($this->editCatId)->update($data);
        } else {
            PhonebookCategory::create($data);
        }

        $this->resetCatForm();
    }

    public function confirmDeleteCat(int $id): void
    {
        $this->deleteCatId = $id;
    }

    public function deleteCategory(): void
    {
        if ($this->deleteCatId) {
            $cat = PhonebookCategory::withCount('contacts')->findOrFail($this->deleteCatId);
            if ($cat->contacts_count > 0) {
                session()->flash('cat_error', "Cannot delete \"{$cat->name}\" — {$cat->contacts_count} contact(s) assigned.");
                $this->deleteCatId = null;
                return;
            }
            $cat->delete();
            $this->deleteCatId = null;
        }
    }

    private function resetCatForm(): void
    {
        $this->editCatId   = null;
        $this->catName     = '';
        $this->showCatForm = false;
        $this->deleteCatId = null;
        $this->resetValidation();
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $contacts = PhonebookContact::with('category')
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', "%{$this->search}%")
                          ->orWhere('phone_numbers', 'like', "%{$this->search}%")
                          ->orWhere('notes', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory !== null, fn($q) => $q->where('phonebook_category_id', $this->filterCategory))
            ->orderBy('name')
            ->get();

        $categories = PhonebookCategory::withCount('contacts')->orderBy('name')->get();

        return view('livewire.phone-book', compact('contacts', 'categories'));
    }
}
