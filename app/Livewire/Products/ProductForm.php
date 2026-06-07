<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $code = '';

    public $photo = null;

    public ?string $existingPhoto = null;

    public array $itemVariants = [];

    public bool $autoCode = true;

    public string $name = '';

    public string $categoryId = '';

    public string $vendorId = '';

    public bool $showVendorForm = false;

    public string $newVendorName = '';

    public string $groupId = '';

    public bool $showGroupForm = false;

    public string $newGroupName = '';

    public string $newGroupCode = '';

    public string $newVendorPhone = '';

    public string $newVendorAddress = '';

    public string $color = '';

    public string $size = '';

    public string $type = 'rental';

    public string $purchasePrice = '';

    public string $rentalPrice = '';

    public string $salePrice = '';

    public int $stockQty = 1;

    public string $notes = '';

    public bool $isActive = true;

    public bool $isEdit = false;

    public bool $isAbandoned = false;

    public string $abandonedPrice = '';

    public string $abandonedDate = '';

    public string $abandonedNote = '';

    public function updatedCategoryId(): void
    {
        if ($this->autoCode && ! $this->isEdit) {
            $this->generateCode();
        }
    }

    public function updatedAutoCode(): void
    {
        if ($this->autoCode && ! $this->isEdit) {
            $this->generateCode();
        } else {
            $this->code = '';
        }
    }

    public function generateCode(): void
    {
        if (! $this->categoryId) {
            return;
        }

        $category = Category::find($this->categoryId);
        if (! $category) {
            return;
        }

        $prefix = strtoupper($category->code);
        $lastNum = Product::where('code', 'like', "{$prefix}-%")
            ->whereNull('deleted_at')
            ->get(['code'])
            ->map(function ($p) use ($prefix) {
                $suffix = substr($p->code, strlen($prefix) + 1);

                return is_numeric($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        $this->code = $prefix.'-'.str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    }

    #[On('open-create-product')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-product-modal');
    }

    #[On('open-edit-product')]
    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->productId = $id;
        $this->isEdit = true;
        $this->autoCode = false;
        $this->code = $product->code;
        $this->groupId = (string) ($product->group_id ?? '');
        $this->color = $product->color ?? '';
        $this->name = $product->name;
        $this->existingPhoto = $product->photo;
        $this->categoryId = (string) $product->category_id;
        $this->vendorId = (string) ($product->vendor_id ?? '');
        $this->size = $product->size ?? '';
        $this->type = $product->type;
        $this->purchasePrice = (string) $product->purchase_price;
        $this->rentalPrice = (string) $product->rental_price;
        $this->salePrice = (string) $product->sale_price;
        $this->stockQty = $product->stock_qty;
        $this->notes = $product->notes ?? '';
        $this->isActive = $product->is_active;
        $this->isAbandoned = $product->is_abandoned;
        $this->abandonedPrice = (string) ($product->abandoned_price ?? '');
        $this->abandonedDate = $product->abandoned_date?->format('Y-m-d') ?? '';
        $this->abandonedNote = $product->abandoned_note ?? '';

        $this->dispatch('open-product-modal');
    }

    public function save(): void
    {
        // Auto-generate code for new products if not manually set
        if (! $this->isEdit && empty($this->code)) {
            $this->generateCode();
        }

        $rules = [
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'code')
                    ->ignore($this->productId)
                    ->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:200',
            'categoryId' => 'required|exists:categories,id',
            'vendorId' => 'nullable|exists:vendors,id',
            'size' => 'nullable|string|max:50',
            'type' => 'required|in:rental,sale,both',
            'purchasePrice' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:3072',
            'rentalPrice' => 'nullable|numeric|min:0',
            'salePrice' => 'nullable|numeric|min:0',
            'stockQty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($this->type === 'rental') {
            $rules['rentalPrice'] = 'required|numeric|min:0';
        }

        if ($this->type === 'sale') {
            $rules['salePrice'] = 'required|numeric|min:0';
        }

        if ($this->type === 'both') {
            $rules['rentalPrice'] = 'required|numeric|min:0';
            $rules['salePrice'] = 'required|numeric|min:0';
        }

        if ($this->isAbandoned) {
            $rules['abandonedPrice'] = 'required|numeric|min:0';
            $rules['abandonedDate'] = 'required|date';
        }

        $this->validate($rules);

        $photoPath = $this->existingPhoto;
        if ($this->photo) {
            $photoPath = $this->photo->store('products', 'public');
        }

        $baseData = [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'vendor_id' => $this->vendorId ?: null,
            'group_id' => $this->groupId ?: null,
            'size' => $this->size ?: null,
            'photo' => $photoPath,
            'type' => $this->type,
            'purchase_price' => $this->purchasePrice ?: 0,
            'rental_price' => in_array($this->type, ['rental', 'both']) ? ($this->rentalPrice ?: 0) : 0,
            'sale_price' => in_array($this->type, ['sale', 'both']) ? ($this->salePrice ?: 0) : 0,
            'notes' => $this->notes ?: null,
            'is_active' => $this->isActive,
            'is_abandoned' => $this->isAbandoned,
            'abandoned_price' => $this->isAbandoned ? $this->abandonedPrice : 0,
            'abandoned_date' => $this->isAbandoned ? $this->abandonedDate : null,
            'abandoned_note' => $this->isAbandoned ? $this->abandonedNote : null,
            'color' => $this->color ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($this->isEdit) {
            $baseData['code'] = strtoupper($this->code);
            $baseData['stock_qty'] = $this->stockQty;
            unset($baseData['created_by']);

            Product::findOrFail($this->productId)->update($baseData);
            session()->flash('success', 'Product updated.');

        } elseif ($this->type === 'sale') {
            // Sale: one record with actual qty
            $baseData['code'] = strtoupper($this->code);
            $baseData['stock_qty'] = $this->stockQty;
            Product::create($baseData);
            session()->flash('success', 'Product created with qty '.$this->stockQty.'.');

        } else {
            // Rental / Both: N separate records each qty 1
            $qty = (int) $this->stockQty;
            $category = Category::find($this->categoryId);
            $prefix = strtoupper($category->code);

            $lastNum = Product::where('code', 'like', "{$prefix}-%")
                ->whereNull('deleted_at')
                ->get(['code'])
                ->map(function ($p) use ($prefix) {
                    $suffix = substr($p->code, strlen($prefix) + 1);

                    return is_numeric($suffix) ? (int) $suffix : 0;
                })
                ->max() ?? 0;

            for ($i = 0; $i < $qty; $i++) {
                $lastNum++;
                $variant = $this->itemVariants[$i] ?? [];
                $baseData['code'] = $prefix.'-'.str_pad($lastNum, 3, '0', STR_PAD_LEFT);
                $baseData['stock_qty'] = 1;
                $baseData['color'] = ! empty($variant['color']) ? $variant['color'] : ($this->color ?: null);
                $baseData['size'] = ! empty($variant['size']) ? $variant['size'] : ($this->size ?: null);
                Product::create($baseData);
            }

            $from = str_pad($lastNum - $qty + 1, 3, '0', STR_PAD_LEFT);
            $to = str_pad($lastNum, 3, '0', STR_PAD_LEFT);
            $message = $qty > 1
                ? "{$qty} items created: {$prefix}-{$from} to {$prefix}-{$to}"
                : '1 product created.';

            session()->flash('success', $message);
        }

        $this->dispatch('product-saved');
        $this->dispatch('close-product-modal');
        $this->resetForm();
    }

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
            'newGroupCode' => 'nullable|string|max:50|unique:product_groups,code',
        ], [
            'newGroupName.required' => 'Group name is required.',
            'newGroupCode.unique' => 'This code already exists.',
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
        $this->newGroupName = '';
        $this->newGroupCode = '';
        $this->resetValidation();
    }

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
            'newVendorPhone' => 'nullable|string|max:20',
        ], [
            'newVendorName.required' => 'Vendor name is required.',
        ]);

        $vendor = Vendor::create([
            'name' => $this->newVendorName,
            'phone' => $this->newVendorPhone ?: null,
            'address' => $this->newVendorAddress ?: null,
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Auto-select the newly created vendor
        $this->vendorId = (string) $vendor->id;
        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->resetValidation();
    }

    public function cancelVendorForm(): void
    {
        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->resetValidation();
    }

    public function updatedStockQty(): void
    {
        if (! $this->isEdit && in_array($this->type, ['rental', 'both'])) {
            $needed = max(1, (int) $this->stockQty);
            $current = count($this->itemVariants);

            if ($needed > $current) {
                for ($i = $current; $i < $needed; $i++) {
                    $this->itemVariants[] = [
                        'color' => '',
                        'size' => '',
                    ];
                }
            } else {
                $this->itemVariants = array_slice($this->itemVariants, 0, $needed);
            }
        } else {
            $this->itemVariants = [];
        }
    }

    public function updatedType(): void
    {
        if ($this->type === 'rental') {
            $this->salePrice = '';
        } elseif ($this->type === 'sale') {
            $this->rentalPrice = '';
            $this->itemVariants = [];

            return;
        }

        // Rebuild variants for rental/both
        $this->itemVariants = [];
        $needed = max(1, (int) $this->stockQty);
        for ($i = 0; $i < $needed; $i++) {
            $this->itemVariants[] = ['color' => '', 'size' => ''];
        }
    }

    public function resetForm(): void
    {
        $this->productId = null;
        $this->isEdit = false;
        $this->autoCode = true;
        $this->photo = null;
        $this->existingPhoto = null;
        $this->groupId = '';
        $this->showGroupForm = false;
        $this->newGroupName = '';
        $this->newGroupCode = '';
        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
        $this->newVendorAddress = '';
        $this->itemVariants = [];
        $this->code = '';
        $this->color = '';
        $this->name = '';
        $this->categoryId = '';
        $this->vendorId = '';
        $this->size = '';
        $this->type = 'rental';
        $this->purchasePrice = '';
        $this->rentalPrice = '';
        $this->salePrice = '';
        $this->stockQty = 1;
        $this->notes = '';
        $this->isActive = true;
        $this->isAbandoned = false;
        $this->abandonedPrice = '';
        $this->abandonedDate = '';
        $this->abandonedNote = '';
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();
        $groups = ProductGroup::orderBy('name')->get();

        return view('livewire.products.product-form',
            compact('categories', 'vendors', 'groups'));
    }
}
