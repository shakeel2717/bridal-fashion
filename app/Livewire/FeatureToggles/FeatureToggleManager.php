<?php

namespace App\Livewire\FeatureToggles;

use App\Models\FeatureToggle;
use App\Models\User;
use Livewire\Component;

class FeatureToggleManager extends Component
{
    public ?int $selectedUserId = null;

    public array $toggles = [];

    public const FEATURES = [
        // Modules
        'customers' => ['label' => 'Customers',         'icon' => 'bi-people',          'desc' => 'View and manage customers'],
        'rentals' => ['label' => 'Rentals',           'icon' => 'bi-box-seam',         'desc' => 'Create and manage rentals'],
        'sales' => ['label' => 'Sales',             'icon' => 'bi-cart3',            'desc' => 'Create and manage sales'],
        'products' => ['label' => 'Products',          'icon' => 'bi-tags',             'desc' => 'View and manage products'],
        'categories' => ['label' => 'Categories',        'icon' => 'bi-folder2',          'desc' => 'Manage product categories'],
        'vendors' => ['label' => 'Vendors',           'icon' => 'bi-shop',             'desc' => 'View and manage vendors'],
        'employees' => ['label' => 'Employees',         'icon' => 'bi-person-badge',     'desc' => 'View and manage employees'],
        'attendance' => ['label' => 'Attendance',        'icon' => 'bi-calendar-check',   'desc' => 'Mark and view attendance'],
        'salary' => ['label' => 'Salary',            'icon' => 'bi-cash-stack',       'desc' => 'View and generate salary'],
        'advances' => ['label' => 'Advances',          'icon' => 'bi-credit-card',      'desc' => 'Manage salary advances'],
        'purchase_orders' => ['label' => 'Purchase Orders',   'icon' => 'bi-bag-check',        'desc' => 'Manage vendor purchase orders'],
        'accounts' => ['label' => 'Accounts',          'icon' => 'bi-bank2',            'desc' => 'View account balances'],
        'expenses' => ['label' => 'Expenses',          'icon' => 'bi-receipt-cutoff',   'desc' => 'Record and view expenses'],
        'notifications' => ['label' => 'Alerts',            'icon' => 'bi-bell',             'desc' => 'View alerts dashboard'],
        'reports' => ['label' => 'Reports',           'icon' => 'bi-bar-chart-line',   'desc' => 'View financial reports'],

        // Dashboard Stat Cards
        'stat_total_customers' => ['label' => 'Stat: Total Customers',   'icon' => 'bi-person-lines-fill', 'desc' => 'Show total customers card on dashboard'],
        'stat_total_products' => ['label' => 'Stat: Total Products',    'icon' => 'bi-tags-fill',         'desc' => 'Show total products card on dashboard'],
        'stat_active_rentals' => ['label' => 'Stat: Active Rentals',    'icon' => 'bi-box-seam-fill',     'desc' => 'Show active rentals card on dashboard'],
        'stat_monthly_revenue' => ['label' => 'Stat: Monthly Revenue',   'icon' => 'bi-cash-coin',         'desc' => 'Show monthly revenue — confidential'],
        'stat_pickup_today' => ['label' => 'Stat: Pickup Today',      'icon' => 'bi-truck',             'desc' => 'Show pickup today card on dashboard'],
        'stat_pickup_tomorrow' => ['label' => 'Stat: Pickup Tomorrow',   'icon' => 'bi-truck',             'desc' => 'Show pickup tomorrow card on dashboard'],
        'stat_overdue' => ['label' => 'Stat: Overdue Returns',   'icon' => 'bi-exclamation-circle', 'desc' => 'Show overdue returns card on dashboard'],
        'stat_pending_balance' => ['label' => 'Stat: Pending Balance',   'icon' => 'bi-wallet2',           'desc' => 'Show pending balance — confidential'],
        'stat_total_cash' => ['label' => 'Stat: Cash & Bank',       'icon' => 'bi-bank2',             'desc' => 'Show total cash & bank — confidential'],
        'stat_expenses' => ['label' => 'Stat: Monthly Expenses',  'icon' => 'bi-receipt',           'desc' => 'Show expenses card — confidential'],
        'stat_total_sales' => ['label' => 'Stat: Sales Count',       'icon' => 'bi-cart-fill',         'desc' => 'Show sales count card on dashboard'],
        'stat_pending_po' => ['label' => 'Stat: PO Balance Due',    'icon' => 'bi-bag-x-fill',        'desc' => 'Show PO balance due — confidential'],

        // Dashboard Bottom Cards
        'dash_overdue_card' => ['label' => 'Dashboard: Overdue List',    'icon' => 'bi-exclamation-triangle', 'desc' => 'Show overdue returns list at bottom'],
        'dash_pickup_card' => ['label' => 'Dashboard: Pickup List',     'icon' => 'bi-box-arrow-up',         'desc' => 'Show pickup today list at bottom'],
        'dash_return_card' => ['label' => 'Dashboard: Return Tomorrow', 'icon' => 'bi-calendar2-event',      'desc' => 'Show return tomorrow list at bottom'],
    ];

    public function selectUser(int $id): void
    {
        $this->selectedUserId = $id;
        $this->loadToggles();
    }

    public function loadToggles(): void
    {
        $user = User::findOrFail($this->selectedUserId);

        $this->toggles = [];

        foreach (self::FEATURES as $feature => $info) {
            // Check user-specific toggle first, then global
            $userToggle = FeatureToggle::where('user_id', $this->selectedUserId)
                ->where('feature', $feature)->first();

            $globalToggle = FeatureToggle::whereNull('user_id')
                ->where('feature', $feature)->first();

            $this->toggles[$feature] = [
                'enabled' => $userToggle
                    ? $userToggle->is_enabled
                    : ($globalToggle?->is_enabled ?? true),
                'overridden' => $userToggle !== null,
                'global' => $globalToggle?->is_enabled ?? true,
            ];
        }
    }

    public function toggle(string $feature): void
    {
        if (! $this->selectedUserId) {
            return;
        }

        $current = $this->toggles[$feature]['enabled'] ?? true;
        $newVal = ! $current;

        FeatureToggle::updateOrCreate(
            ['user_id' => $this->selectedUserId, 'feature' => $feature],
            ['is_enabled' => $newVal, 'created_by' => auth()->id()]
        );

        $this->toggles[$feature]['enabled'] = $newVal;
        $this->toggles[$feature]['overridden'] = true;
    }

    public function resetToGlobal(string $feature): void
    {
        FeatureToggle::where('user_id', $this->selectedUserId)
            ->where('feature', $feature)
            ->delete();

        $globalToggle = FeatureToggle::whereNull('user_id')
            ->where('feature', $feature)->first();

        $this->toggles[$feature]['enabled'] = $globalToggle?->is_enabled ?? true;
        $this->toggles[$feature]['overridden'] = false;
    }

    public function enableAll(): void
    {
        foreach (self::FEATURES as $feature => $info) {
            FeatureToggle::updateOrCreate(
                ['user_id' => $this->selectedUserId, 'feature' => $feature],
                ['is_enabled' => true, 'created_by' => auth()->id()]
            );
            $this->toggles[$feature]['enabled'] = true;
            $this->toggles[$feature]['overridden'] = true;
        }
        session()->flash('success', 'All features enabled.');
    }

    public function disableAll(): void
    {
        foreach (self::FEATURES as $feature => $info) {
            FeatureToggle::updateOrCreate(
                ['user_id' => $this->selectedUserId, 'feature' => $feature],
                ['is_enabled' => false, 'created_by' => auth()->id()]
            );
            $this->toggles[$feature]['enabled'] = false;
            $this->toggles[$feature]['overridden'] = true;
        }
        session()->flash('success', 'All features disabled.');
    }

    public function resetAllToGlobal(): void
    {
        FeatureToggle::where('user_id', $this->selectedUserId)->delete();
        $this->loadToggles();
        session()->flash('success', 'Reset to global defaults.');
    }

    public function updateGlobal(string $feature, bool $value): void
    {
        FeatureToggle::updateOrCreate(
            ['user_id' => null, 'feature' => $feature],
            ['is_enabled' => $value, 'created_by' => auth()->id()]
        );
    }

    public function render()
    {
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $globalToggles = FeatureToggle::whereNull('user_id')
            ->get()
            ->keyBy('feature');

        return view('livewire.feature-toggles.feature-toggle-manager',
            compact('employees', 'globalToggles'));
    }
}
