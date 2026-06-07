<?php

namespace App\Livewire\Rentals;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalPayment;
use App\Models\RentalTask;
use App\Models\User;
use Livewire\Component;
use Livewire\Exceptions\ValidationException;

class RentalCreate extends Component
{
    // Step
    public int $step = 1;

    public string $phone1Gender = 'male';

    public string $phone2Gender = 'male';

    public string $whatsappGender = 'male';

    public string $advancePaymentMethod = 'cash';

    // Customer
    public string $customerType = 'existing'; // existing | walkin

    public string $customerSearch = '';

    public ?int $customerId = null;

    public string $customerName = '';

    public string $customerPhone1 = '';

    public string $customerPhone2 = '';

    public string $customerWhatsapp = '';

    public string $customerCnic = '';

    public string $deliveryAddress = '';

    public ?array $foundCustomers = null;

    // Rental Details
    public string $billRef = '';

    public string $bookingDate = '';

    public string $pickupDate = '';

    public string $returnDate = '';

    public string $stitchingDate = '';

    public string $stitchingInstructions = '';

    public string $employeeId = '';

    public string $notes = '';

    // Items
    public string $productSearch = '';

    public array $searchResults = [];

    public array $items = [];
    // items structure: [{product_id, code, name, category, rental_price, custom_label, custom_price, size}]

    // Payment
    public string $advancePaid = '0';

    public string $totalAmount = '0';

    public function mount(): void
    {
        $this->bookingDate = now()->format('Y-m-d');
    }

    // ── Step Navigation ───────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStep1();
        } elseif ($this->step === 2) {
            if (empty($this->items)) {
                $this->addError('items', 'Please add at least one item.');

                return;
            }
        }
        $this->step++;
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
        $this->phone1Gender = 'male';
        $this->phone2Gender = 'male';
        $this->whatsappGender = 'male';
        $this->customerName = '';
        $this->customerPhone1 = '';
        $this->customerPhone2 = '';
        $this->customerWhatsapp = '';
        $this->customerCnic = '';
        $this->deliveryAddress = '';
        $this->customerSearch = '';
        $this->foundCustomers = null;
    }

    public function setGender(string $field, string $gender): void
    {
        $this->$field = $gender;
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
            ->get(['id', 'name', 'phone1', 'cnic'])
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
        $this->customerCnic = $customer->cnic ?? '';
        $this->deliveryAddress = $customer->address ?? '';
        $this->customerSearch = $customer->name;
        $this->foundCustomers = null;
    }

    public function validateStep1(): void
    {
        $rules = [
            'customerName' => 'required|string|max:150',
            'customerPhone1' => 'required|string|max:20',
            'customerCnic' => 'required|string|max:20',
        ];

        if ($this->customerType === 'walkin') {
            unset($rules['customerCnic']);
        }

        $this->validate($rules, [
            'customerName.required' => 'Customer name is required.',
            'customerPhone1.required' => 'Phone number is required.',
            'customerCnic.required' => 'CNIC is required for rental customers.',
        ]);
    }

    // ── Step 2: Items ─────────────────────────────────────
    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];

            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->toArray();

        $this->searchResults = Product::with('category')
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->whereIn('type', ['rental', 'both'])
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                    ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->whereNotIn('id', $alreadyAdded)
            ->limit(8)
            ->get(['id', 'code', 'name', 'rental_price', 'size', 'category_id'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'rental_price' => $p->rental_price,
                'size' => $p->size,
                'category' => $p->category->name ?? '',
            ])
            ->toArray();
    }

    public function addItem(int $productId): void
    {
        $product = Product::with('category')->findOrFail($productId);

        $this->items[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category->name ?? '',
            'size' => $product->size ?? '',
            'rental_price' => (string) $product->rental_price,
            'note' => '',
            'addons' => [], // [{label, price}]
        ];

        $this->productSearch = '';
        $this->searchResults = [];
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
        $total = 0;
        foreach ($this->items as $item) {
            $total += (float) ($item['rental_price'] ?? 0);
            foreach ($item['addons'] ?? [] as $addon) {
                $total += (float) ($addon['price'] ?? 0);
            }
        }
        $this->totalAmount = (string) $total;
    }

    public function updatedItems(): void
    {
        $this->recalcTotal();
    }

    public function validateStep2(): void
    {
        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one item.');
            throw new ValidationException(
                validator([], [])->errors()->add('items', 'Please add at least one item.')
            );
        }
    }

    // ── Step 3: Dates & Payment ───────────────────────────
    public function validateStep3(): void
    {
        $this->validate([
            'bookingDate' => 'required|date',
            'pickupDate' => 'nullable|date',
            'returnDate' => 'nullable|date',
            'advancePaid' => 'required|numeric|min:0',
            'employeeId' => 'nullable|exists:users,id',
        ]);
    }

    // ── Save ──────────────────────────────────────────────
    public function save(): void
    {
        $this->validateStep3();

        $this->recalcTotal();
        $total = (float) $this->totalAmount;
        $advance = (float) $this->advancePaid;
        $remaining = max(0, $total - $advance);

        $customerId = $this->customerId;
        if ($this->customerType === 'walkin' || ! $customerId) {
            $walkIn = Customer::where('is_walkin', true)->first();
            $customerId = $walkIn?->id;
        }

        $rental = Rental::create([
            'bill_ref' => $this->billRef ?: null,
            'customer_id' => $customerId,
            'customer_name' => $this->customerName,
            'customer_phone1' => $this->customerPhone1,
            'customer_phone2' => $this->customerPhone2 ?: null,
            'customer_whatsapp' => $this->customerWhatsapp ?: null,
            'customer_cnic' => $this->customerCnic ?: null,
            'delivery_address' => $this->deliveryAddress ?: null,
            'booking_date' => $this->bookingDate,
            'pickup_date' => $this->pickupDate ?: null,
            'phone1_gender' => $this->phone1Gender,
            'phone2_gender' => $this->phone2Gender,
            'whatsapp_gender' => $this->whatsappGender,
            'advance_payment_method' => $this->advancePaymentMethod,
            'return_date' => $this->returnDate ?: null,
            'stitching_date' => $this->stitchingDate ?: null,
            'stitching_instructions' => $this->stitchingInstructions ?: null,
            'status' => 'booked',
            'total_amount' => $total,
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

        // Record initial advance as payment if paid
        if ($advance > 0) {
            RentalPayment::create([
                'rental_id' => $rental->id,
                'amount' => $advance,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'note' => 'Initial advance payment',
                'created_by' => auth()->id(),
            ]);
        }

        session()->flash('success', "Rental #{$rental->id} created successfully.");
        $this->redirect(route('rentals.show', $rental->id));
    }

    public function render()
    {
        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'designation']);

        return view('livewire.rentals.rental-create', compact('employees'));
    }
}
