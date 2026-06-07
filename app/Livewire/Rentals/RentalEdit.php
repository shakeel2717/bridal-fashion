<?php

namespace App\Livewire\Rentals;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalTask;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class RentalEdit extends Component
{
    public Rental $rental;

    public string $phone1Gender = 'male';

    public string $phone2Gender = 'male';

    public string $whatsappGender = 'male';

    public string $advancePaymentMethod = 'cash';

    // Customer
    public string $customerName = '';

    public string $customerPhone1 = '';

    public string $customerPhone2 = '';

    public string $customerWhatsapp = '';

    public string $customerCnic = '';

    public string $deliveryAddress = '';

    // Rental details
    public string $billRef = '';

    public string $bookingDate = '';

    public string $pickupDate = '';

    public string $returnDate = '';

    public string $stitchingDate = '';

    public string $stitchingInstructions = '';

    public string $employeeId = '';

    public string $status = '';

    public string $notes = '';

    // Payment
    public string $totalAmount = '';

    public string $advancePaid = '';

    public string $remainingBalance = '';

    // Items
    public array $items = [];

    public string $productSearch = '';

    public array $searchResults = [];

    // Item to remove confirm
    public ?int $removeItemId = null;

    public function mount(Rental $rental): void
    {
        $this->rental = $rental;
        $this->loadRental();
    }

    public function loadRental(): void
    {
        $r = $this->rental;
        $this->advancePaymentMethod = $r->advance_payment_method ?? 'cash';

        $this->customerName = $r->customer_name;
        $this->phone1Gender = $r->phone1_gender ?? 'male';
        $this->phone2Gender = $r->phone2_gender ?? 'male';
        $this->whatsappGender = $r->whatsapp_gender ?? 'male';
        $this->customerPhone1 = $r->customer_phone1;
        $this->customerPhone2 = $r->customer_phone2 ?? '';
        $this->customerWhatsapp = $r->customer_whatsapp ?? '';
        $this->customerCnic = $r->customer_cnic ?? '';
        $this->deliveryAddress = $r->delivery_address ?? '';
        $this->billRef = $r->bill_ref ?? '';
        $this->bookingDate = $r->booking_date ? Carbon::parse($r->booking_date)->format('Y-m-d') : '';
        $this->pickupDate = $r->pickup_date ? Carbon::parse($r->pickup_date)->format('Y-m-d') : '';
        $this->returnDate = $r->return_date ? Carbon::parse($r->return_date)->format('Y-m-d') : '';
        $this->stitchingDate = $r->stitching_date ? Carbon::parse($r->stitching_date)->format('Y-m-d') : '';
        $this->stitchingInstructions = $r->stitching_instructions ?? '';
        $this->employeeId = (string) ($r->employee_id ?? '');
        $this->status = $r->status;
        $this->notes = $r->notes ?? '';
        $this->totalAmount = (string) $r->total_amount;
        $this->advancePaid = (string) $r->advance_paid;
        $this->remainingBalance = (string) $r->remaining_balance;

        // Load items
        $this->items = $r->items->map(fn ($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'code' => $item->product_code,
            'name' => $item->product_name,
            'rental_price' => (string) $item->rental_price,
            'note' => '',
            'addons' => $item->custom_option_label
                ? collect(explode(', ', $item->custom_option_label))
                    ->map(fn ($label) => ['label' => $label, 'price' => '0'])
                    ->toArray()
                : [],
        ])->toArray();
    }

    // ── Product Search ────────────────────────────────────
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
            ->get()
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
            'id' => null, // new item, not yet saved
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'rental_price' => (string) $product->rental_price,
            'note' => '',
            'addons' => [],
        ];

        $this->productSearch = '';
        $this->searchResults = [];
        $this->recalcTotal();
    }

    public function confirmRemoveItem(int $index): void
    {
        $this->removeItemId = $index;
    }

    public function setGender(string $field, string $gender): void
    {
        $this->$field = $gender;
    }

    public function removeItem(int $index): void
    {
        // If existing DB item, delete it
        if (! empty($this->items[$index]['id'])) {
            RentalItem::find($this->items[$index]['id'])?->delete();
        }

        array_splice($this->items, $index, 1);
        $this->removeItemId = null;
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
        $this->remainingBalance = (string) max(0, $total - (float) $this->advancePaid);
    }

    // ── Save ──────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:150',
            'customerPhone1' => 'required|string|max:20',
            'bookingDate' => 'required|date',
            'status' => 'required|in:booked,ready,picked_up,partially_picked_up,returned,cancelled,abandoned',
            'totalAmount' => 'required|numeric|min:0',
            'advancePaid' => 'required|numeric|min:0',
            'employeeId' => 'nullable|exists:users,id',
        ]);

        $this->recalcTotal();

        // Update rental
        $this->rental->update([
            'customer_name' => $this->customerName,
            'customer_phone1' => $this->customerPhone1,
            'advance_payment_method' => $this->advancePaymentMethod,
            'customer_phone2' => $this->customerPhone2 ?: null,
            'customer_whatsapp' => $this->customerWhatsapp ?: null,
            'customer_cnic' => $this->customerCnic ?: null,
            'delivery_address' => $this->deliveryAddress ?: null,
            'bill_ref' => $this->billRef ?: null,
            'phone1_gender' => $this->phone1Gender,
            'phone2_gender' => $this->phone2Gender,
            'whatsapp_gender' => $this->whatsappGender,
            'booking_date' => Carbon::parse($this->bookingDate)->toDateString(),
            'pickup_date' => $this->pickupDate ? Carbon::parse($this->pickupDate)->toDateString() : null,
            'return_date' => $this->returnDate ? Carbon::parse($this->returnDate)->toDateString() : null,
            'stitching_date' => $this->stitchingDate ? Carbon::parse($this->stitchingDate)->toDateString() : null,
            'stitching_instructions' => $this->stitchingInstructions ?: null,
            'status' => $this->status,
            'total_amount' => (float) $this->totalAmount,
            'advance_paid' => (float) $this->advancePaid,
            'remaining_balance' => max(0, (float) $this->totalAmount - (float) $this->advancePaid),
            'employee_id' => $this->employeeId ?: null,
            'notes' => $this->notes ?: null,
            'updated_by' => auth()->id(),
        ]);

        // Update stitching task title if changed
        if (! empty($this->stitchingInstructions)) {
            $stitchingTask = $this->rental->tasks()->where('type', 'stitching')->first();
            if ($stitchingTask) {
                $stitchingTask->update(['title' => $this->stitchingInstructions]);
            } else {
                RentalTask::create([
                    'rental_id' => $this->rental->id,
                    'type' => 'stitching',
                    'title' => $this->stitchingInstructions,
                    'cost' => 0,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }
        }

        // Sync items
        foreach ($this->items as $item) {
            $addonLabels = collect($item['addons'] ?? [])
                ->filter(fn ($a) => ! empty($a['label']))
                ->map(fn ($a) => $a['label'])
                ->join(', ');

            $addonTotal = collect($item['addons'] ?? [])
                ->sum(fn ($a) => (float) ($a['price'] ?? 0));

            if (! empty($item['id'])) {
                // Update existing
                RentalItem::where('id', $item['id'])->update([
                    'rental_price' => (float) $item['rental_price'],
                    'custom_option_label' => $addonLabels ?: null,
                    'custom_option_price' => $addonTotal,
                ]);

                // Sync addon tasks
                $this->syncAddonTasks($item['id'], $item['addons'] ?? []);

            } else {
                // Create new item
                $rentalItem = RentalItem::create([
                    'rental_id' => $this->rental->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_code' => $item['code'],
                    'rental_price' => (float) $item['rental_price'],
                    'custom_option_label' => $addonLabels ?: null,
                    'custom_option_price' => $addonTotal,
                    'pickup_status' => 'pending',
                ]);

                // Create addon tasks for new items
                foreach ($item['addons'] ?? [] as $addon) {
                    if (! empty($addon['label'])) {
                        RentalTask::create([
                            'rental_id' => $this->rental->id,
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
        }

        session()->flash('success', 'Rental updated successfully.');
        $this->redirect(route('rentals.show', $this->rental->id));
    }

    protected function syncAddonTasks(int $rentalItemId, array $addons): void
    {
        // Delete existing pending addon tasks for this item
        RentalTask::where('rental_item_id', $rentalItemId)
            ->where('type', 'addon')
            ->where('status', 'pending')
            ->delete();

        // Recreate from current addons
        foreach ($addons as $addon) {
            if (! empty($addon['label'])) {
                RentalTask::create([
                    'rental_id' => $this->rental->id,
                    'rental_item_id' => $rentalItemId,
                    'type' => 'addon',
                    'title' => $addon['label'],
                    'cost' => (float) ($addon['price'] ?? 0),
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    public function render()
    {
        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('livewire.rentals.rental-edit', compact('employees'));
    }
}
