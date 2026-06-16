<?php

namespace App\Livewire\Rentals;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalPayment;
use App\Models\RentalSecurityDeposit;
use App\Models\RentalTask;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class RentalCreate extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?int $rentalId = null;

    public bool $isEditMode = false;

    public string $customerCity = '';

    // ── Step 1: Customer ──────────────────────────────────
    public string $customerType = 'walkin';

    public bool $showStitching = false;

    public $walkinPhoto = null;

    public string $advanceAccountId = '';

    public string $discountType = 'fixed'; // 'fixed' or 'percent'

    public string $discountValue = '0';

    public string $discountAmount = '0';

    public $walkinCnicFront = null;

    public $walkinCnicBack = null;

    public string $customerSearch = '';

    public ?int $customerId = null;

    public string $customerName = '';

    public string $customerPhone1 = '';

    public string $customerPhone2 = '';

    public string $customerWhatsapp = '';

    public string $customerCnic = '';

    public string $deliveryAddress = '';

    public string $phone1Gender = 'male';

    public string $phone2Gender = 'male';

    public string $whatsappGender = 'male';

    public ?array $foundCustomers = null;

    public array $securityDeposits = [];

    // ── Step 2: Dates & Details ───────────────────────────
    public string $billRef = '';

    public string $bookingDate = '';

    public string $pickupDate = '';

    public string $returnDate = '';

    public string $stitchingDate = '';

    public string $stitchingInstructions = '';

    public string $employeeId = '';

    public string $notes = '';

    // ── Step 3: Items ─────────────────────────────────────
    public string $productSearch = '';

    public array $searchResults = [];

    public string $pendingPrice = '';

    public ?int $pendingProductId = null;

    public string $pendingProductCode = '';

    public string $pendingProductName = '';

    public bool $showPriceInput = false;

    public array $items = [];

    // ── Linked Sale Items ─────────────────────────────────
    public array $saleItems = [];

    public string $saleProductSearch = '';

    public array $saleSearchResults = [];

    public ?int $salePendingProductId = null;

    public string $salePendingProductName = '';

    public string $salePendingProductCode = '';

    public string $saleNewItemQty = '1';

    public string $saleNewItemPrice = '0';

    public string $saleDiscount = '0';

    // ── Step 4: Payment ───────────────────────────────────
    public string $totalAmount = '0';

    public string $advancePaid = '0';

    public string $advancePaymentMethod = 'cash';

    public function mount(?Rental $rental = null): void
    {
        $this->bookingDate = now()->format('Y-m-d');
        $this->pickupDate = now()->addDays(2)->format('Y-m-d');
        $this->returnDate = now()->addDays(5)->format('Y-m-d');
        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->advanceAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
        $this->employeeId = (string) auth()->id();

        if ($rental && $rental->exists) {
            $this->isEditMode = true;
            $this->rentalId = $rental->id;

            $walkinCustomer = Customer::where('is_walkin', true)->first();
            $isWalkinRental = ($rental->customer_id === $walkinCustomer?->id);
            $this->customerType = $isWalkinRental ? 'walkin' : 'existing';
            $this->customerId = $isWalkinRental ? null : $rental->customer_id;

            $this->customerName = $rental->customer_name;
            $this->customerCity = $rental->customer_city ?? '';
            $this->customerPhone1 = $rental->customer_phone1;
            $this->customerPhone2 = $rental->customer_phone2 ?? '';
            $this->customerWhatsapp = $rental->customer_whatsapp ?? '';
            $this->customerCnic = $rental->customer_cnic ?? '';
            $this->deliveryAddress = $rental->delivery_address ?? '';
            $this->phone1Gender = $rental->phone1_gender ?? 'male';
            $this->phone2Gender = $rental->phone2_gender ?? 'male';
            $this->whatsappGender = $rental->whatsapp_gender ?? 'male';

            $this->billRef = $rental->bill_ref ?? '';
            $this->bookingDate = Carbon::parse($rental->booking_date)->format('Y-m-d');
            $this->pickupDate = Carbon::parse($rental->pickup_date)->format('Y-m-d');
            $this->returnDate = Carbon::parse($rental->return_date)->format('Y-m-d');
            $this->employeeId = (string) ($rental->employee_id ?? auth()->id());
            $this->notes = $rental->notes ?? '';

            if ($rental->stitching_date || $rental->stitching_instructions) {
                $this->showStitching = true;
                $this->stitchingDate = $rental->stitching_date
                    ? Carbon::parse($rental->stitching_date)->format('Y-m-d') : '';
                $this->stitchingInstructions = $rental->stitching_instructions ?? '';
            }

            $this->items = $rental->items->map(fn ($item) => [
                'rental_item_id' => $item->id,
                'product_id' => $item->product_id,
                'code' => $item->product_code,
                'name' => $item->product_name,
                'photo' => $item->product?->photo,
                'category' => $item->product?->category?->name ?? '',
                'size' => $item->product?->size ?? '',
                'color' => $item->product?->color ?? '',
                'rental_price' => (string) $item->rental_price,
                'note' => $item->note ?? '',
                'double_booked' => false,
                'addons' => $item->tasks->where('type', 'addon')->map(fn ($t) => [
                    'label' => $t->title,
                    'price' => (string) $t->cost,
                ])->values()->toArray(),
            ])->toArray();

            $this->securityDeposits = $rental->securityDeposits->map(fn ($d) => [
                'deposit_id' => $d->id,
                'item_name' => $d->item_name,
                'amount' => (string) $d->amount,
                'is_paid' => $d->is_paid,
            ])->toArray();

            $this->totalAmount = (string) $rental->total_amount;
            $this->advancePaid = (string) $rental->advance_paid;
            $this->discountType = $rental->discount_type ?? 'fixed';
            $this->discountValue = (string) ($rental->discount_value ?? 0);
            $this->discountAmount = (string) ($rental->discount_amount ?? 0);
        }
    }

    public function saveWalkinAsCustomer(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:150',
            'customerPhone1' => 'required|string|max:20',
        ]);

        $customer = Customer::create([
            'name' => $this->customerName,
            'phone1' => $this->customerPhone1,
            'phone2' => $this->customerPhone2 ?: null,
            'whatsapp' => $this->customerWhatsapp ?: null,
            'cnic' => $this->customerCnic ?: null,
            'address' => $this->deliveryAddress ?: null,
            'is_walkin' => false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Switch to registered and select this customer
        $this->customerType = 'existing';
        $this->customerId = $customer->id;
        $this->customerSearch = $customer->name;
    }

    // ── Navigation ────────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStep1();
            $this->autoSaveWalkinCustomer();
        }
        if ($this->step === 2) {
            $this->validateStep2();
        }
        if ($this->step === 3) {
            if (empty($this->items)) {
                $this->addError('items', 'Please add at least one item.');

                return;
            }
        }
        if ($this->step >= 4) {
            return;
        }

        $this->step++;

        if ($this->step === 2) {
            $this->dispatch('focus-step-2');
        }
        if ($this->step === 3) {
            $this->dispatch('step-changed-to-3');
        }
        if ($this->step === 4) {
            $this->dispatch('focus-step-4');
        }
    }

    private function autoSaveWalkinCustomer(): void
    {
        // Only auto-save walk-in with name+phone and not already saved
        if ($this->customerType !== 'walkin' || $this->customerId || empty($this->customerName) || empty($this->customerPhone1)) {
            return;
        }

        $customer = Customer::create([
            'name' => $this->customerName,
            'phone1' => $this->customerPhone1,
            'city' => $this->customerCity ?: null,
            'phone2' => $this->customerPhone2 ?: null,
            'whatsapp' => $this->customerWhatsapp ?: null,
            'cnic' => $this->customerCnic ?: null,
            'address' => $this->deliveryAddress ?: null,
            'is_walkin' => false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->customerId = $customer->id;
        $this->customerType = 'existing';
        $this->customerSearch = $customer->name;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    // ── Step 1: Customer ──────────────────────────────────
    public function setCustomerType(string $type): void
    {
        $this->customerType = $type;
        $this->customerId = null;
        $this->customerName = '';
        $this->customerCity = '';
        $this->customerPhone1 = '';
        $this->customerPhone2 = '';
        $this->walkinPhoto = null;
        $this->walkinCnicFront = null;
        $this->walkinCnicBack = null;
        $this->customerWhatsapp = '';
        $this->customerCnic = '';
        $this->deliveryAddress = '';
        $this->customerSearch = '';
        $this->foundCustomers = null;
        $this->phone1Gender = 'male';
        $this->phone2Gender = 'male';
        $this->whatsappGender = 'male';
    }

    public function searchCustomers(): void
    {
        if (strlen($this->customerSearch) < 2) {
            $this->foundCustomers = null;

            return;
        }

        $this->foundCustomers = Customer::where('is_walkin', false)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customerSearch}%")
                    ->orWhere('phone1', 'like', "%{$this->customerSearch}%")
                    ->orWhere('cnic', 'like', "%{$this->customerSearch}%");
            })
            ->limit(6)
            ->get(['id', 'name', 'phone1', 'cnic', 'city'])
            ->toArray();
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $id;
        $this->customerName = $customer->name;
        $this->customerPhone1 = $customer->phone1;
        $this->customerPhone2 = $customer->phone2 ?? '';
        $this->customerWhatsapp = $customer->whatsapp ?? '';
        $this->customerCity = $customer->city ?? '';
        $this->customerCnic = $customer->cnic ?? '';
        $this->city = $customer->city ?? '';
        $this->deliveryAddress = $customer->address ?? '';
        $this->customerSearch = $customer->name;
        $this->foundCustomers = null;
    }

    public function setGender(string $field, string $gender): void
    {
        $this->$field = $gender;
    }

    public function validateStep1(): void
    {
        $rules = [
            'customerName' => 'required|string|max:150',
            'customerPhone1' => 'required|string|max:20',
        ];

        if ($this->customerType === 'existing') {
            $rules['customerCnic'] = 'nullable|string|max:20';
        }

        $this->validate($rules, [
            'customerName.required' => 'Customer name is required.',
            'customerPhone1.required' => 'Phone number is required.',
            'customerCnic.nullable' => 'CNIC is required for rental customers.',
        ]);
    }

    // ── Step 2: Dates ─────────────────────────────────────
    public function validateStep2(): void
    {
        $this->validate([
            'bookingDate' => 'required|date',
            'pickupDate' => 'required|date',
            'returnDate' => 'required|date|after:pickupDate',
        ], [
            'pickupDate.required' => 'Pickup date is required to check availability.',
            'returnDate.required' => 'Return date is required to check availability.',
            'returnDate.after' => 'Return date must be after pickup date.',
        ]);
    }

    public function selectProductForPrice(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->pendingProductId = $product->id;
        $this->pendingProductCode = $product->code;
        $this->pendingProductName = $product->name;
        $this->pendingPrice = (string) $product->rental_price;
        $this->showPriceInput = true;
        $this->productSearch = '';   // ← ensure this is here
        $this->searchResults = [];
        $this->dispatch('focus-rental-price');
    }

    public function confirmAddItem(): void
    {
        if (! $this->pendingProductId) {
            return;
        }

        $isBooked = RentalItem::whereHas('rental', fn ($q) => $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->whereRaw('DATE(pickup_date) <= ?', [$this->returnDate])
            ->whereRaw('DATE(return_date) >= ?', [$this->pickupDate])
        )->where('product_id', $this->pendingProductId)->exists();

        $product = Product::with('category')->findOrFail($this->pendingProductId);

        $this->items[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category->name ?? '',
            'size' => $product->size ?? '',
            'color' => $product->color ?? '',
            'photo' => $product->photo,
            'rental_price' => $this->pendingPrice ?: (string) $product->rental_price,
            'note' => '',
            'addons' => [],
            'double_booked' => $isBooked,
        ];

        $this->productSearch = '';
        $this->pendingProductId = null;
        $this->pendingPrice = '';
        $this->showPriceInput = false;
        $this->searchResults = [];
        $this->recalcTotal();
        $this->dispatch('focus-rental-search');
    }

    // ── Step 3: Items with availability check ─────────────
    public function searchProducts(): void
    {
        $this->showPriceInput = false;
        $this->pendingProductId = null;

        if (strlen($this->productSearch) < 1) {
            $this->searchResults = [];

            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->toArray();
        $bookedProductIds = RentalItem::whereHas('rental', fn ($q) => $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->whereRaw('DATE(pickup_date) <= ?', [$this->returnDate])
            ->whereRaw('DATE(return_date) >= ?', [$this->pickupDate])
        )->pluck('product_id')->toArray();

        $this->searchResults = Product::with('category')
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->whereIn('type', ['rental', 'both'])
            ->where('code', $this->productSearch)   // exact match only
            ->whereNotIn('id', $alreadyAdded)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'rental_price' => $p->rental_price,
                'size' => $p->size,
                'color' => $p->color,
                'photo' => $p->photo,
                'category' => $p->category->name ?? '',
                'available' => ! in_array($p->id, $bookedProductIds),
            ])
            ->sortByDesc('available')
            ->values()
            ->toArray();
    }

    // public function addItem(int $productId): void
    // {
    //     $isBooked = RentalItem::whereHas('rental', function ($q) {
    //         $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
    //             ->whereRaw('DATE(pickup_date) <= ?', [$this->returnDate])
    //             ->whereRaw('DATE(return_date) >= ?', [$this->pickupDate]);
    //     })->where('product_id', $productId)->exists();

    //     $product = Product::with('category')->findOrFail($productId);

    //     $this->items[] = [
    //         'product_id' => $product->id,
    //         'code' => $product->code,
    //         'name' => $product->name,
    //         'category' => $product->category->name ?? '',
    //         'size' => $product->size ?? '',
    //         'color' => $product->color ?? '',
    //         'photo' => $product->photo,
    //         'rental_price' => (string) $product->rental_price,
    //         'note' => '',
    //         'addons' => [],
    //         'double_booked' => $isBooked, // flag for warning display
    //     ];

    //     $this->productSearch = '';
    //     $this->searchResults = [];
    //     $this->recalcTotal();
    // }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->recalcTotal();
    }

    public function addAddon(int $itemIndex): void
    {
        $this->items[$itemIndex]['addons'][] = ['label' => '', 'price' => '0'];
    }

    public function removeAddon(int $itemIndex, int $addonIndex): void
    {
        array_splice($this->items[$itemIndex]['addons'], $addonIndex, 1);
        $this->recalcTotal();
    }

    public function recalcTotal(): void
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += (float) ($item['rental_price'] ?? 0);
            foreach ($item['addons'] ?? [] as $addon) {
                $subtotal += (float) ($addon['price'] ?? 0);
            }
        }

        $discount = 0;
        if ($this->discountType === 'percent') {
            $discount = $subtotal * ((float) $this->discountValue / 100);
        } else {
            $discount = (float) $this->discountValue;
        }
        $discount = min($discount, $subtotal);
        $this->discountAmount = (string) round($discount, 2);
        $this->totalAmount = (string) max(0, $subtotal - $discount);
    }

    // ── Save ──────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'advancePaid' => 'required|numeric|min:0',
            'advancePaymentMethod' => 'required|string',
            'employeeId' => 'nullable|exists:users,id',
            'walkinPhoto' => 'nullable|image|max:3072',
            'walkinCnicFront' => 'nullable|image|max:3072',
            'walkinCnicBack' => 'nullable|image|max:3072',
        ]);

        $this->recalcTotal();
        $total = (float) $this->totalAmount;
        $advance = (float) $this->advancePaid;
        $remaining = max(0, $total - $advance);

        // ── EDIT MODE ─────────────────────────────────────────
        if ($this->isEditMode) {
            $rental = Rental::findOrFail($this->rentalId);

            $walkinPhotoPath = $rental->walkin_photo;
            $walkinCnicFrontPath = $rental->walkin_cnic_front;
            $walkinCnicBackPath = $rental->walkin_cnic_back;

            if ($this->walkinPhoto) {
                $walkinPhotoPath = $this->walkinPhoto->store('walkin/photos', 'public');
            }
            if ($this->walkinCnicFront) {
                $walkinCnicFrontPath = $this->walkinCnicFront->store('walkin/cnic', 'public');
            }
            if ($this->walkinCnicBack) {
                $walkinCnicBackPath = $this->walkinCnicBack->store('walkin/cnic', 'public');
            }

            $rental->update([
                'bill_ref' => $this->billRef ?: null,
                'customer_name' => $this->customerName,
                'customer_phone1' => $this->customerPhone1,
                'customer_city' => $this->customerCity ?: null,
                'customer_phone2' => $this->customerPhone2 ?: null,
                'customer_whatsapp' => $this->customerWhatsapp ?: null,
                'customer_cnic' => $this->customerCnic ?: null,
                'delivery_address' => $this->deliveryAddress ?: null,
                'phone1_gender' => $this->phone1Gender,
                'phone2_gender' => $this->phone2Gender,
                'whatsapp_gender' => $this->whatsappGender,
                'booking_date' => Carbon::parse($this->bookingDate)->toDateString(),
                'pickup_date' => Carbon::parse($this->pickupDate)->toDateString(),
                'return_date' => Carbon::parse($this->returnDate)->toDateString(),
                'stitching_date' => $this->stitchingDate ? Carbon::parse($this->stitchingDate)->toDateString() : null,
                'stitching_instructions' => $this->stitchingInstructions ?: null,
                'total_amount' => $total,
                'advance_paid' => $advance,
                'remaining_balance' => $remaining,
                'discount_type' => $this->discountType,
                'discount_value' => (float) $this->discountValue,
                'discount_amount' => (float) $this->discountAmount,
                'employee_id' => $this->employeeId ?: null,
                'notes' => $this->notes ?: null,
                'walkin_photo' => $walkinPhotoPath,
                'walkin_cnic_front' => $walkinCnicFrontPath,
                'walkin_cnic_back' => $walkinCnicBackPath,
                'updated_by' => auth()->id(),
            ]);

            // Sync items
            $keptIds = collect($this->items)->pluck('rental_item_id')->filter()->toArray();
            $rental->items()->whereNotIn('id', $keptIds)->delete();

            foreach ($this->items as $item) {
                $addonLabels = collect($item['addons'] ?? [])->filter(fn ($a) => ! empty($a['label']))->map(fn ($a) => $a['label'])->join(', ');
                $addonTotal = collect($item['addons'] ?? [])->sum(fn ($a) => (float) ($a['price'] ?? 0));

                if (! empty($item['rental_item_id'])) {
                    $ri = RentalItem::findOrFail($item['rental_item_id']);
                    $ri->update([
                        'rental_price' => (float) $item['rental_price'],
                        'custom_option_label' => $addonLabels ?: null,
                        'custom_option_price' => $addonTotal,
                    ]);
                    $ri->tasks()->where('type', 'addon')->delete();
                } else {
                    $ri = RentalItem::create([
                        'rental_id' => $rental->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'product_code' => $item['code'],
                        'rental_price' => (float) $item['rental_price'],
                        'custom_option_label' => $addonLabels ?: null,
                        'custom_option_price' => $addonTotal,
                        'pickup_status' => 'pending',
                    ]);
                }

                foreach ($item['addons'] ?? [] as $addon) {
                    if (! empty($addon['label'])) {
                        RentalTask::create([
                            'rental_id' => $rental->id,
                            'rental_item_id' => $ri->id,
                            'type' => 'addon',
                            'title' => $addon['label'],
                            'cost' => (float) ($addon['price'] ?? 0),
                            'status' => 'pending',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // Sync security deposits
            $keptDepositIds = collect($this->securityDeposits)->pluck('deposit_id')->filter()->toArray();
            $rental->securityDeposits()->whereNotIn('id', $keptDepositIds)->delete();
            foreach ($this->securityDeposits as $deposit) {
                if (empty($deposit['item_name'])) {
                    continue;
                }
                if (! empty($deposit['deposit_id'])) {
                    $rental->securityDeposits()->find($deposit['deposit_id'])?->update([
                        'item_name' => $deposit['item_name'],
                        'amount' => (float) $deposit['amount'],
                        'is_paid' => (bool) $deposit['is_paid'],
                    ]);
                } else {
                    $rental->securityDeposits()->create([
                        'item_name' => $deposit['item_name'],
                        'amount' => (float) $deposit['amount'],
                        'is_paid' => (bool) $deposit['is_paid'],
                        'is_refunded' => false,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $rental->tasks()->where('type', 'stitching')->delete();
            if (! empty($this->stitchingInstructions)) {
                RentalTask::create([
                    'rental_id' => $rental->id,
                    'type' => 'stitching',
                    'title' => $this->stitchingInstructions,
                    'cost' => 0,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }

            session()->flash('success', 'Rental updated successfully.');
            $this->redirect(route('rentals.show', $rental->id));

            return;
        }

        $customerId = $this->customerId;
        if ($this->customerType === 'walkin' || ! $customerId) {
            $walkIn = Customer::where('is_walkin', true)->first();
            $customerId = $walkIn?->id;
        }

        $walkinPhotoPath = null;
        $walkinCnicFrontPath = null;
        $walkinCnicBackPath = null;

        if ($this->customerType === 'walkin') {
            if ($this->walkinPhoto) {
                $walkinPhotoPath = $this->walkinPhoto->store('walkin/photos', 'public');
            }
            if ($this->walkinCnicFront) {
                $walkinCnicFrontPath = $this->walkinCnicFront->store('walkin/cnic', 'public');
            }
            if ($this->walkinCnicBack) {
                $walkinCnicBackPath = $this->walkinCnicBack->store('walkin/cnic', 'public');
            }
        }

        $rental = Rental::create([
            'bill_ref' => $this->billRef ?: null,
            'customer_id' => $customerId,
            'customer_name' => $this->customerName,
            'customer_phone1' => $this->customerPhone1,
            'customer_city' => $this->customerCity ?: null,
            'customer_phone2' => $this->customerPhone2 ?: null,
            'customer_whatsapp' => $this->customerWhatsapp ?: null,
            'customer_cnic' => $this->customerCnic ?: null,
            'delivery_address' => $this->deliveryAddress ?: null,
            'phone1_gender' => $this->phone1Gender,
            'phone2_gender' => $this->phone2Gender,
            'discount_type' => $this->discountType,
            'discount_value' => (float) $this->discountValue,
            'discount_amount' => (float) $this->discountAmount,
            'whatsapp_gender' => $this->whatsappGender,
            'booking_date' => Carbon::parse($this->bookingDate)->toDateString(),
            'pickup_date' => Carbon::parse($this->pickupDate)->toDateString(),
            'return_date' => Carbon::parse($this->returnDate)->toDateString(),
            'stitching_date' => $this->stitchingDate ? Carbon::parse($this->stitchingDate)->toDateString() : null,
            'stitching_instructions' => $this->stitchingInstructions ?: null,
            'status' => 'booked',
            'advance_payment_method' => Account::find($this->advanceAccountId)?->name ?? 'cash',
            'total_amount' => $total,
            'walkin_photo' => $walkinPhotoPath,
            'walkin_cnic_front' => $walkinCnicFrontPath,
            'walkin_cnic_back' => $walkinCnicBackPath,
            'advance_paid' => $advance,
            'remaining_balance' => $remaining,
            'employee_id' => $this->employeeId ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $addonLabels = collect($item['addons'] ?? [])
                ->filter(fn ($a) => ! empty($a['label']))
                ->map(fn ($a) => $a['label'])
                ->join(', ');

            $addonTotal = collect($item['addons'] ?? [])
                ->sum(fn ($a) => (float) ($a['price'] ?? 0));

            $rentalItem = RentalItem::create([
                'rental_id' => $rental->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_code' => $item['code'],
                'rental_price' => (float) $item['rental_price'],
                'custom_option_label' => $addonLabels ?: null,
                'custom_option_price' => $addonTotal,
                'pickup_status' => 'pending',
            ]);

            foreach ($item['addons'] ?? [] as $addon) {
                if (! empty($addon['label'])) {
                    RentalTask::create([
                        'rental_id' => $rental->id,
                        'rental_item_id' => $rentalItem->id,
                        'type' => 'addon',
                        'title' => $addon['label'],
                        'cost' => (float) ($addon['price'] ?? 0),
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);
                }
            }

        }

        // Security deposits — outside items loop
        foreach ($this->securityDeposits as $deposit) {
            if (! empty($deposit['item_name'])) {
                RentalSecurityDeposit::create([
                    'rental_id' => $rental->id,
                    'item_name' => $deposit['item_name'],
                    'amount' => (float) ($deposit['amount'] ?? 0),
                    'is_paid' => (bool) ($deposit['is_paid'] ?? false),
                    'is_refunded' => false,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        if (! empty($this->stitchingInstructions)) {
            RentalTask::create([
                'rental_id' => $rental->id,
                'type' => 'stitching',
                'title' => $this->stitchingInstructions,
                'cost' => 0,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
        }

        if ($advance > 0) {
            $accountName = Account::find($this->advanceAccountId)?->name ?? 'Cash';

            RentalPayment::create([
                'rental_id' => $rental->id,
                'amount' => $advance,
                'payment_date' => now()->toDateString(),
                'payment_method' => $accountName,
                'note' => 'Initial advance payment',
                'created_by' => auth()->id(),
            ]);

            if ($this->advanceAccountId) {
                AccountService::credit(
                    (int) $this->advanceAccountId,
                    $advance,
                    'rental_payment',
                    "Rental advance — {$this->customerName} (#{$rental->id})",
                    now()->toDateString(),
                    $rental,
                );
            }
        }

        // ── Create linked sale if sale items were added ────────
        if (! empty($this->saleItems)) {
            $saleSubtotal = collect($this->saleItems)->sum(fn ($i) => (float) ($i['total_price'] ?? 0));
            $saleDiscount = (float) $this->saleDiscount;
            $saleTotal = max(0, $saleSubtotal - $saleDiscount);

            $linkedSale = Sale::create([
                'rental_id' => $rental->id,
                'bill_ref' => $this->billRef ?: null,
                'customer_id' => $customerId,
                'customer_name' => $this->customerName,
                'customer_phone1' => $this->customerPhone1 ?: '0000-0000000',
                'customer_phone2' => $this->customerPhone2 ?: null,
                'customer_cnic' => $this->customerCnic ?: null,
                'delivery_address' => $this->deliveryAddress ?: null,
                'sale_date' => Carbon::parse($this->bookingDate)->toDateString(),
                'advance_payment_method' => Account::find($this->advanceAccountId)?->name ?? 'cash',
                'status' => 'completed',
                'total_amount' => $saleTotal,
                'discount' => $saleDiscount,
                'advance_paid' => 0,
                'remaining_balance' => $saleTotal,
                'employee_id' => $this->employeeId ?: null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            foreach ($this->saleItems as $item) {
                $qty = (int) ($item['qty'] ?? 1);

                SaleItem::create([
                    'sale_id' => $linkedSale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['item_name'],
                    'product_code' => $item['item_code'],
                    'sale_price' => (float) $item['unit_price'],
                    'qty' => $qty,
                ]);

                Product::where('id', $item['product_id'])->decrement('stock_qty', $qty);
            }
        }

        session()->flash('success', "Rental #{$rental->id} created successfully.");
        $this->redirect(route('rentals.show', $rental->id));
    }

    public function addSecurityDeposit(): void
    {
        $this->securityDeposits[] = [
            'item_name' => '',
            'amount' => '0',
            'is_paid' => false,
        ];
    }

    public function removeSecurityDeposit(int $index): void
    {
        array_splice($this->securityDeposits, $index, 1);
    }

    public function searchSaleProducts(): void
    {
        \Log::info('[SALE SEARCH] searchSaleProducts called', [
            'query' => $this->saleProductSearch,
            'length' => strlen($this->saleProductSearch),
        ]);

        if (strlen($this->saleProductSearch) < 2) {
            $this->saleSearchResults = [];
            \Log::info('[SALE SEARCH] Query too short, cleared results');

            return;
        }

        $alreadyAdded = collect($this->saleItems)->pluck('product_id')->filter()->toArray();

        // Debug: check what products exist at all
        $totalProducts = Product::count();
        $saleProducts = Product::whereIn('type', ['sale', 'both'])->count();
        $activeProducts = Product::whereIn('type', ['sale', 'both'])->where('is_active', true)->count();
        $withStock = Product::whereIn('type', ['sale', 'both'])->where('is_active', true)->where('stock_qty', '>', 0)->count();

        \Log::info('[SALE SEARCH] Product counts', [
            'total' => $totalProducts,
            'sale_type' => $saleProducts,
            'active_sale_type' => $activeProducts,
            'with_stock' => $withStock,
        ]);

        // Debug: search without stock filter
        $withoutStockFilter = Product::whereIn('type', ['sale', 'both'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', $this->saleProductSearch)
                    ->orWhere('name', 'like', "%{$this->saleProductSearch}%");
            })
            ->get(['id', 'code', 'name', 'type', 'stock_qty', 'is_active', 'is_abandoned']);

        \Log::info('[SALE SEARCH] Without stock filter', [
            'count' => $withoutStockFilter->count(),
            'items' => $withoutStockFilter->toArray(),
        ]);

        $this->saleSearchResults = Product::with('category')
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->whereIn('type', ['sale', 'both'])
            ->where('stock_qty', '>', 0)
            ->where(function ($q) {
                $q->where('code', $this->saleProductSearch)
                    ->orWhere('name', 'like', "%{$this->saleProductSearch}%");
            })
            ->whereNotIn('id', $alreadyAdded)
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'sale_price' => $p->sale_price,
                'stock_qty' => $p->stock_qty,
                'category' => $p->category->name ?? '',
            ])
            ->toArray();

        \Log::info('[SALE SEARCH] Results count:', ['count' => count($this->saleSearchResults)]);
    }

    public function selectSaleProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->salePendingProductId = $product->id;
        $this->salePendingProductName = $product->name;
        $this->salePendingProductCode = $product->code;
        $this->saleNewItemPrice = (string) $product->sale_price;
        $this->saleProductSearch = $product->code.' — '.$product->name;
        $this->saleSearchResults = [];
        $this->dispatch('focus-sale-qty');
    }

    public function addSaleItem(): void
    {
        if (! $this->salePendingProductId) {
            return;
        }

        $qty = max(1, (int) $this->saleNewItemQty);
        $price = max(0, (float) $this->saleNewItemPrice);

        array_unshift($this->saleItems, [
            'product_id' => $this->salePendingProductId,
            'item_name' => $this->salePendingProductName,
            'item_code' => $this->salePendingProductCode,
            'qty' => $qty,
            'unit_price' => (string) $price,
            'total_price' => (string) ($qty * $price),
        ]);

        $this->saleProductSearch = '';
        $this->saleSearchResults = [];
        $this->saleNewItemQty = '1';
        $this->saleNewItemPrice = '0';
        $this->salePendingProductId = null;
        $this->salePendingProductName = '';
        $this->salePendingProductCode = '';
        $this->dispatch('focus-sale-search');
    }

    public function removeSaleItem(int $index): void
    {
        array_splice($this->saleItems, $index, 1);
    }

    public function recalcSaleItems(): void
    {
        foreach ($this->saleItems as $i => $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $this->saleItems[$i]['total_price'] = (string) ($qty * $price);
        }
    }

    public function getSaleSubtotalProperty(): float
    {
        return collect($this->saleItems)->sum(fn ($i) => (float) ($i['total_price'] ?? 0));
    }

    public function getSaleTotalProperty(): float
    {
        return max(0, $this->getSaleSubtotalProperty() - (float) $this->saleDiscount);
    }

    public function render()
    {
        $employees = User::where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'designation']);

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('livewire.rentals.rental-create', compact('employees', 'accounts'));
    }
}
