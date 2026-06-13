<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public bool $isActive = true;

    public bool $showForm = false;

    public ?int $editId = null;

    public ?int $deleteId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->dispatch('focus-first-input', selector: '#category_name_input');
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editId = $id;
        $this->name = $category->name;
        $this->code = $category->code;
        $this->description = $category->description ?? '';
        $this->isActive = $category->is_active;
        $this->showForm = true;
        $this->dispatch('focus-first-input', selector: '#category_name_input');
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|unique:categories,name|max:100',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('categories', 'code')
                    ->ignore($this->editId)
                    ->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:500',
        ]);

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
            'updated_by' => auth()->id(),
        ];

        if ($this->editId) {
            Category::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Category updated.');
        } else {
            $data['created_by'] = auth()->id();
            Category::create($data);
            session()->flash('success', 'Category added.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update([
            'is_active' => ! $category->is_active,
            'updated_by' => auth()->id(),
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $category = Category::findOrFail($this->deleteId);

        if ($category->products()->count() > 0) {
            session()->flash('error', 'Cannot delete — category has products assigned.');
            $this->deleteId = null;

            return;
        }

        $category->delete();
        $this->deleteId = null;
        session()->flash('success', 'Category deleted.');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->isActive = true;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::withCount('products')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate(15);

        return view('livewire.categories.category-list', compact('categories'));
    }
}
