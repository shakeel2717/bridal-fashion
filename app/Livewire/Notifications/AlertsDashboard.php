<?php

namespace App\Livewire\Notifications;

use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class AlertsDashboard extends Component
{
    public string $activeTab = 'overdue';

    public string $receivedBy = '';

    public function mount(): void
    {
        $this->receivedBy = (string) auth()->id();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function markReturned(int $rentalId): void
    {
        $rental = Rental::with('items')->findOrFail($rentalId);

        $rental->items()
            ->whereIn('pickup_status', ['picked_up', 'pending'])
            ->update([
                'pickup_status' => 'returned',
                'returned_at' => now(),
                'returned_received_by' => $this->receivedBy ?: auth()->id(),
            ]);

        $rental->update([
            'status' => 'returned',
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Rental marked as returned.');
    }

    public function markReady(int $rentalId): void
    {
        Rental::findOrFail($rentalId)->update([
            'status' => 'ready',
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Rental marked as ready.');
    }

    public function render()
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $overdue = Rental::with(['items'])
            ->whereRaw('DATE(return_date) < ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->orderByRaw('DATE(return_date)')
            ->get();

        $returnToday = Rental::with(['items'])
            ->whereRaw('DATE(return_date) = ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->get();

        $returnTomorrow = Rental::with(['items'])
            ->whereRaw('DATE(return_date) = ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->get();

        $pickupToday = Rental::with(['items'])
            ->whereRaw('DATE(pickup_date) = ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->get();

        $pickupTomorrow = Rental::with(['items'])
            ->whereRaw('DATE(pickup_date) = ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->get();

        $readyForPickup = Rental::with(['items'])
            ->where('status', 'ready')
            ->orderByRaw('DATE(pickup_date)')
            ->get();

        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $counts = [
            'overdue' => $overdue->count(),
            'return_today' => $returnToday->count(),
            'return_tomorrow' => $returnTomorrow->count(),
            'pickup_today' => $pickupToday->count(),
            'pickup_tomorrow' => $pickupTomorrow->count(),
            'ready' => $readyForPickup->count(),
        ];

        return view('livewire.notifications.alerts-dashboard', compact(
            'overdue', 'returnToday', 'returnTomorrow',
            'pickupToday', 'pickupTomorrow', 'readyForPickup',
            'employees', 'counts', 'today', 'tomorrow'
        ));
    }
}
