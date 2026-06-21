<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Vendor;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $fabricUnit = 'meter';

    public bool $isEdit = false;

    public string $name = '';

    public bool $showCategoryForm = false;

    public string $newCategoryName = '';

    public string $newCategoryCode = '';

    public string $categoryId = '';

    public string $groupId = '';

    public string $type = 'rental';

    public string $rentalPrice = '0';

    public string $salePrice = '';

    public int $stockQty = 1;

    public string $notes = '';

    public bool $isActive = true;

    public bool $isAbandoned = false;

    public string $abandonedPrice = '';

    public string $abandonedDate = '';

    public string $abandonedNote = '';

    public $photo = null;

    public ?string $existingPhoto = null;

    // Per-item variants (code, color, size per qty)
    public array $itemVariants = [];

    // Inline vendor
    public bool $showVendorForm = false;

    public string $newVendorName = '';

    public string $newVendorPhone = '';

    public string $newVendorAddress = '';

    // Inline group
    public bool $showGroupForm = false;

    public string $newGroupName = '';

    public string $newGroupCode = '';

    public function updatedStockQty(): void
    {
        $this->rebuildVariants();
    }

    public function updatedType(): void
    {
        if ($this->type === 'rental') {
            $this->salePrice = '';
        }
        if ($this->type === 'service') {
            $this->salePrice = '';
            $this->stockQty = 1;
        }
        $this->rebuildVariants();
    }

    // Replace rebuildVariants():
    protected function rebuildVariants(): void
    {
        if ($this->isEdit) {
            return;
        }

        if (in_array($this->type, ['sale', 'service', 'fabric'])) {
            // These types always have exactly 1 variant (code only)
            $this->itemVariants = [['code' => $this->itemVariants[0]['code'] ?? '', 'color' => '', 'size' => '']];

            return;
        }

        $needed = max(1, (int) $this->stockQty);
        $current = count($this->itemVariants);

        if ($needed > $current) {
            for ($i = $current; $i < $needed; $i++) {
                $this->itemVariants[] = ['code' => '', 'color' => '', 'size' => ''];
            }
        } else {
            $this->itemVariants = array_slice($this->itemVariants, 0, $needed);
        }
    }

    #[On('open-create-product')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->itemVariants = [['code' => '', 'color' => '', 'size' => '']];
        $this->dispatch('open-product-modal');
    }

    #[On('open-edit-product')]
    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->productId = $id;
        $this->isEdit = true;
        $this->name = $product->name;
        $this->categoryId = (string) $product->category_id;
        $this->groupId = (string) ($product->group_id ?? '');
        $this->type = $product->type;
        $this->rentalPrice = (string) $product->rental_price;
        $this->salePrice = (string) $product->sale_price;
        $this->fabricUnit = $product->fabric_unit ?? 'meter';
        $this->stockQty = $product->stock_qty;
        $this->notes = $product->notes ?? '';
        $this->isActive = $product->is_active;
        $this->isAbandoned = $product->is_abandoned;
        $this->abandonedPrice = (string) ($product->abandoned_price ?? '');
        $this->abandonedDate = $product->abandoned_date?->format('Y-m-d') ?? '';
        $this->abandonedNote = $product->abandoned_note ?? '';
        $this->existingPhoto = $product->photo;

        // Edit: single variant for editing code/color/size
        $this->itemVariants = [[
            'code' => $product->code ?? '',
            'color' => $product->color ?? '',
            'size' => $product->size ?? '',
        ]];

        $this->dispatch('open-product-modal');
    }

    public function updatedItemVariants(): void
    {
        // Just triggers re-render so duplicate warning shows live
        // Actual validation happens on save
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:200',
            'categoryId' => 'required|exists:categories,id',
            'type' => 'required|in:rental,sale,both,service,fabric',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:3072',
        ];

        if (in_array($this->type, ['rental', 'both'])) {
            $rules['stockQty'] = 'required|integer|min:1';
        }

        if (in_array($this->type, ['sale', 'both'])) {
            $rules['salePrice'] = 'required|numeric|min:0';
        }
        if ($this->type === 'service') {
            $rules['salePrice'] = 'required|numeric|min:0';
        }
        if ($this->type === 'fabric') {
            $rules['fabricUnit'] = 'required|in:meter,gaz';
            $rules['salePrice'] = 'required|numeric|min:0';
        }

        // Validate at least first variant has a code
        foreach ($this->itemVariants as $i => $v) {
            $rules["itemVariants.{$i}.code"] = 'required|string|max:50';
        }

        $this->validate($rules, [
            'itemVariants.*.code.required' => 'Each item must have a code.',
        ]);

        // Validate codes — check for duplicates within the batch and against existing products
        $usedCodes = [];
        foreach ($this->itemVariants as $i => $v) {
            $code = strtoupper(trim($v['code'] ?? ''));

            if (empty($code)) {
                $this->addError("itemVariants.{$i}.code", 'Code is required.');

                return;
            }

            // Check duplicate within same batch
            if (in_array($code, $usedCodes)) {
                $this->addError("itemVariants.{$i}.code", "Code {$code} is already used in this batch.");

                return;
            }

            // Check against existing products in DB (excluding current product if editing)
            $exists = Product::where('code', $code)
                ->when($this->productId, fn ($q) => $q->where('id', '!=', $this->productId))
                ->exists();

            if ($exists) {
                $this->addError("itemVariants.{$i}.code", "Code {$code} already exists in the system.");

                return;
            }

            $usedCodes[] = $code;
        }

        $photoPath = $this->existingPhoto;
        if ($this->photo) {
            $photoPath = $this->photo->store('products', 'public');
        }

        $baseData = [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'group_id' => $this->groupId ?: null,
            'type' => $this->type,
            'rental_price' => 0,
            'sale_price' => in_array($this->type, ['sale', 'both', 'service', 'fabric'])
                                    ? ($this->salePrice ?: 0)
                                    : 0,
            'purchase_price' => 0,
            'notes' => $this->notes ?: null,
            'is_active' => $this->isActive,
            'is_abandoned' => $this->isAbandoned,
            'abandoned_price' => $this->isAbandoned ? $this->abandonedPrice : 0,
            'abandoned_date' => $this->isAbandoned ? $this->abandonedDate : null,
            'abandoned_note' => $this->isAbandoned ? $this->abandonedNote : null,
            'photo' => $photoPath,
            'updated_by' => auth()->id(),
        ];

        if ($this->isEdit) {
            $variant = $this->itemVariants[0] ?? [];
            $baseData['code'] = strtoupper($variant['code'] ?? '');
            $baseData['color'] = $variant['color'] ?: null;
            $baseData['size'] = $variant['size'] ?: null;
            $baseData['stock_qty'] = $this->stockQty;

            // Fabric: preserve stock_decimal, update fabric_unit
            if ($this->type === 'fabric') {
                $baseData['fabric_unit'] = $this->fabricUnit;
            }

            Product::findOrFail($this->productId)->update($baseData);
            session()->flash('success', 'Product updated.');

        } elseif ($this->type === 'service') {
            // Service: single record, no stock tracking
            $variant = $this->itemVariants[0] ?? [];
            $baseData['code'] = strtoupper($variant['code'] ?? '');
            $baseData['color'] = null;
            $baseData['size'] = null;
            $baseData['stock_qty'] = 0;
            $baseData['stock_decimal'] = 0;
            $baseData['fabric_unit'] = null;
            $baseData['created_by'] = auth()->id();

            Product::create($baseData);
            session()->flash('success', 'Service product created. Add it to sales to bill customers.');

        } elseif ($this->type === 'fabric') {
            // Fabric: single record, decimal stock, unit of measure
            $variant = $this->itemVariants[0] ?? [];
            $baseData['code'] = strtoupper($variant['code'] ?? '');
            $baseData['color'] = null;
            $baseData['size'] = null;
            $baseData['stock_qty'] = 0;
            $baseData['stock_decimal'] = 0; // starts at 0, PO will update
            $baseData['fabric_unit'] = $this->fabricUnit;
            $baseData['created_by'] = auth()->id();

            Product::create($baseData);
            session()->flash('success', 'Fabric (Thaan) created. Add a Purchase Order to receive stock.');

        } elseif ($this->type === 'sale') {
            // Sale: single record, integer stock
            $variant = $this->itemVariants[0] ?? [];
            $baseData['code'] = strtoupper($variant['code'] ?? '');
            $baseData['color'] = $variant['color'] ?: null;
            $baseData['size'] = $variant['size'] ?: null;
            $baseData['stock_qty'] = 0;
            $baseData['stock_decimal'] = 0;
            $baseData['fabric_unit'] = null;
            $baseData['created_by'] = auth()->id();

            Product::create($baseData);
            session()->flash('success', 'Product created. Add a Purchase Order to update stock.');

        } else {
            // Rental/Both: N separate records
            $qty = (int) $this->stockQty;
            $baseData['created_by'] = auth()->id();
            $baseData['stock_decimal'] = 0;
            $baseData['fabric_unit'] = null;

            for ($i = 0; $i < $qty; $i++) {
                $variant = $this->itemVariants[$i] ?? [];
                $baseData['code'] = strtoupper($variant['code'] ?? '');
                $baseData['color'] = $variant['color'] ?: null;
                $baseData['size'] = $variant['size'] ?: null;
                $baseData['stock_qty'] = 0;
                Product::create($baseData);
            }

            session()->flash('success', "{$qty} product(s) created. Add Purchase Order to receive stock.");
        }

        $this->dispatch('product-saved');
        $this->dispatch('close-product-modal');
        $this->dispatch('refresh-page');
        $this->resetForm();
    }

    // Inline vendor
    public function openVendorForm(): void
    {
        $this->showVendorForm = true;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->resetValidation();
    }

    public function saveVendor(): void
    {
        $this->validate([
            'newVendorName' => 'required|string|max:150',
        ]);

        Vendor::create([
            'name' => $this->newVendorName,
            'phone' => $this->newVendorPhone ?: null,
            'address' => $this->newVendorAddress ?: null,
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->resetValidation();
    }

    public function cancelVendorForm(): void
    {
        $this->showVendorForm = false;
        $this->resetValidation();
    }

    // Inline group
    public function openGroupForm(): void
    {
        $this->showGroupForm = true;
        $this->newGroupName = '';
        $this->newGroupCode = '';
        $this->resetValidation();
    }

    public function saveGroup(): void
    {
        $this->validate([
            'newGroupName' => 'required|string|max:200',
        ]);

        $group = ProductGroup::create([
            'name' => $this->newGroupName,
            'code' => $this->newGroupCode ?: null,
            'category_id' => $this->categoryId ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->groupId = (string) $group->id;
        $this->showGroupForm = false;
        $this->newGroupName = '';
        $this->newGroupCode = '';
        $this->resetValidation();
    }

    public function cancelGroupForm(): void
    {
        $this->showGroupForm = false;
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->productId = null;
        $this->isEdit = false;
        $this->name = '';
        $this->categoryId = '';
        $this->groupId = '';
        $this->type = 'rental';
        $this->fabricUnit = 'meter';
        $this->rentalPrice = '0';
        $this->salePrice = '';
        $this->showCategoryForm = false;
        $this->newCategoryName = '';
        $this->newCategoryCode = '';
        $this->stockQty = 1;
        $this->notes = '';
        $this->isActive = true;
        $this->isAbandoned = false;
        $this->abandonedPrice = '';
        $this->abandonedDate = '';
        $this->abandonedNote = '';
        $this->photo = null;
        $this->existingPhoto = null;
        $this->itemVariants = [['code' => '', 'color' => '', 'size' => '']]; // always start with 1 row
        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->showGroupForm = false;
        $this->newGroupName = '';
        $this->newGroupCode = '';
        $this->resetValidation();
    }

    public function openCategoryForm(): void
    {
        $this->showCategoryForm = true;
        $this->newCategoryName = '';
        $this->newCategoryCode = '';
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:150',
            'newCategoryCode' => 'required|string|max:10|unique:categories,code',
        ], [
            'newCategoryCode.unique' => 'This code is already used.',
        ]);

        $cat = Category::create([
            'name' => $this->newCategoryName,
            'code' => strtoupper($this->newCategoryCode),
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->categoryId = (string) $cat->id;
        $this->showCategoryForm = false;
        $this->newCategoryName = '';
        $this->newCategoryCode = '';
        $this->resetValidation();
    }

    public function cancelCategoryForm(): void
    {
        $this->showCategoryForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::active()->orderBy('name')->get();
        $groups = ProductGroup::orderBy('name')->get();

        return view('livewire.products.product-form',
            compact('categories', 'groups'));
    }
}
