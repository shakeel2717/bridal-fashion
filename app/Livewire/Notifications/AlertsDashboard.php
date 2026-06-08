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
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $employees = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $overdue = Rental::whereRaw('DATE(return_date) < ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->orderBy('return_date')
            ->get();

        $returnToday = Rental::whereRaw('DATE(return_date) = ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->get();

        $returnTomorrow = Rental::whereRaw('DATE(return_date) = ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->get();

        $pickupToday = Rental::whereRaw('DATE(pickup_date) = ?', [$today])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->get();

        $pickupTomorrow = Rental::whereRaw('DATE(pickup_date) = ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->get();

        $ready = Rental::where('status', 'ready')
            ->with('items')
            ->get();

        // Upcoming pickups — all future pickups beyond tomorrow, date-wise
        $upcomingPickups = Rental::whereRaw('DATE(pickup_date) > ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned', 'picked_up'])
            ->with('items')
            ->orderBy('pickup_date')
            ->get()
            ->groupBy(fn ($r) => Carbon::parse($r->pickup_date)->format('Y-m-d'));

        // Upcoming returns — all future returns beyond tomorrow, date-wise
        $upcomingReturns = Rental::whereRaw('DATE(return_date) > ?', [$tomorrow])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')
            ->orderBy('return_date')
            ->get()
            ->groupBy(fn ($r) => Carbon::parse($r->return_date)->format('Y-m-d'));

        $counts = [
            'overdue' => $overdue->count(),
            'return_today' => $returnToday->count(),
            'return_tomorrow' => $returnTomorrow->count(),
            'pickup_today' => $pickupToday->count(),
            'pickup_tomorrow' => $pickupTomorrow->count(),
            'ready' => $ready->count(),
            'upcoming_pickups' => $upcomingPickups->sum(fn ($g) => $g->count()),
            'upcoming_returns' => $upcomingReturns->sum(fn ($g) => $g->count()),
        ];

        return view('livewire.notifications.alerts-dashboard', compact(
            'overdue', 'returnToday', 'returnTomorrow',
            'pickupToday', 'pickupTomorrow', 'ready',
            'upcomingPickups', 'upcomingReturns',
            'employees', 'counts'
        ));
    }
}
