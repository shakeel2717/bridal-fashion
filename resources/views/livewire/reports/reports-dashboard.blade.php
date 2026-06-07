<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Reports & Overview</div>
            <div class="page-subtitle">Financial summary and business insights</div>
        </div>
    </div>

    {{-- Month Navigator --}}
    <div class="table-card mb-3" style="padding:12px 16px;">
        <div class="d-flex align-items-center justify-content-between">
            <button class="month-nav-btn" wire:click="previousMonth">
                <i class="bi bi-chevron-left"></i> Prev
            </button>
            <div style="font-size:16px; font-weight:700; color:var(--text-primary);">
                {{ $currentMonth->format('F Y') }}
            </div>
            <button class="month-nav-btn" wire:click="nextMonth"
                    @if(!$canGoNext) disabled style="opacity:0.4; cursor:not-allowed;" @endif>
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display:flex; border-bottom:1px solid var(--border); margin-bottom:20px; background:#fff; border-radius:8px 8px 0 0; overflow:hidden;">
        @foreach([
            'overview'     => ['Overview',      'bi-grid'],
            'income'       => ['Income',         'bi-arrow-up-circle'],
            'expenses'     => ['Expenses',       'bi-arrow-down-circle'],
            'accounts'     => ['Accounts',       'bi-bank2'],
            'salary'       => ['Salary',         'bi-people'],
        ] as $tab => $info)
        <button class="notif-tab-btn {{ $activeTab === $tab ? 'active' : '' }}"
                wire:click="setTab('{{ $tab }}')">
            <i class="bi {{ $info[1] }} me-1"></i>
            {{ $info[0] }}
        </button>
        @endforeach
    </div>

    {{-- ── OVERVIEW TAB ── --}}
    @if($activeTab === 'overview')

    {{-- Key Metrics --}}
    <div class="row g-3 mb-3">
        <div class="col-3">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:6px;">
                    Total Income
                </div>
                <div style="font-size:24px; font-weight:800; color:#276749;">
                    Rs. {{ number_format($totalIncome, 0) }}
                </div>
                <div style="font-size:11px; color:#38a169; margin-top:4px;">
                    Rental: Rs. {{ number_format($rentalIncome, 0) }}
                    · Sale: Rs. {{ number_format($saleIncome, 0) }}
                </div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:6px;">
                    Total Expenses
                </div>
                <div style="font-size:24px; font-weight:800; color:#c53030;">
                    Rs. {{ number_format($totalExpenses + $totalSalaries, 0) }}
                </div>
                <div style="font-size:11px; color:#e53e3e; margin-top:4px;">
                    Expenses: Rs. {{ number_format($totalExpenses, 0) }}
                    · Salary: Rs. {{ number_format($totalSalaries, 0) }}
                </div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:{{ $netProfit >= 0 ? '#f0fff4' : '#fff5f5' }}; border:1.5px solid {{ $netProfit >= 0 ? '#9ae6b4' : '#fed7d7' }}; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:{{ $netProfit >= 0 ? '#276749' : '#c53030' }}; margin-bottom:6px;">
                    Net Profit
                </div>
                <div style="font-size:24px; font-weight:800; color:{{ $netProfit >= 0 ? '#276749' : '#c53030' }};">
                    Rs. {{ number_format(abs($netProfit), 0) }}
                    {{ $netProfit < 0 ? '(Loss)' : '' }}
                </div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                    Income - Expenses - Salaries
                </div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#2c5282; margin-bottom:6px;">
                    Total Cash & Bank
                </div>
                <div style="font-size:24px; font-weight:800; color:#2c5282;">
                    Rs. {{ number_format($totalAccountBalance, 0) }}
                </div>
                <div style="font-size:11px; color:#3182ce; margin-top:4px;">
                    Across {{ $accounts->count() }} accounts
                </div>
            </div>
        </div>
    </div>

    {{-- Rental & Sale Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="table-card" style="padding:16px 20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-box-seam me-1"></i> Rentals — {{ $currentMonth->format('F Y') }}
                </div>
                <div class="row g-2">
                    @foreach([
                        ['Booked This Month',  $rentalsThisMonth,  '#2c5282'],
                        ['Returned',           $rentalsReturned,   '#276749'],
                        ['Currently Active',   $rentalsActive,     '#553c9a'],
                        ['Overdue',            $rentalsOverdue,    '#c53030'],
                    ] as [$label, $val, $color])
                    <div class="col-6">
                        <div style="background:#f7fafc; border-radius:8px; padding:12px;">
                            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;">{{ $label }}</div>
                            <div style="font-size:20px; font-weight:800; color:{{ $color }};">{{ $val }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="margin-top:12px; padding-top:12px; border-top:1px solid var(--border); font-size:12px; color:var(--text-muted);">
                    Pending Balance:
                    <strong style="color:#e53e3e;">Rs. {{ number_format($pendingRentalBalance, 0) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="table-card" style="padding:16px 20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cart me-1"></i> Sales — {{ $currentMonth->format('F Y') }}
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div style="background:#f7fafc; border-radius:8px; padding:12px;">
                            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;">Sales This Month</div>
                            <div style="font-size:20px; font-weight:800; color:#276749;">{{ $salesThisMonth }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f7fafc; border-radius:8px; padding:12px;">
                            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;">Revenue</div>
                            <div style="font-size:18px; font-weight:800; color:#276749;">
                                Rs. {{ number_format($salesRevenue, 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Account Balances Summary --}}
    <div class="table-card mb-3" style="padding:16px 20px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
            <i class="bi bi-bank2 me-1"></i> Account Balances
        </div>
        <div class="row g-2">
            @foreach($accounts as $account)
            <div class="col-3">
                <div style="background:#f7fafc; border-radius:8px; padding:12px 14px; border-left:3px solid {{ $account->type === 'cash' ? '#38a169' : ($account->type === 'bank' ? '#3182ce' : '#805ad5') }};">
                    <div style="font-size:10px; color:var(--text-muted); margin-bottom:2px;">
                        {{ $account->name }}
                    </div>
                    <div style="font-size:16px; font-weight:800; color:{{ $account->current_balance >= 0 ? 'var(--navy)' : '#e53e3e' }};">
                        Rs. {{ number_format($account->current_balance, 0) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Expense Breakdown --}}
    @if($expenseBreakdown->count() > 0)
    <div class="table-card" style="padding:16px 20px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
            <i class="bi bi-pie-chart me-1"></i> Expense Breakdown
        </div>
        @foreach($expenseBreakdown as $item)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:12px; height:12px; border-radius:3px; background:{{ $item['color'] }}; flex-shrink:0;"></div>
                <div style="font-size:13px; font-weight:600;">{{ $item['name'] }}</div>
                <div style="font-size:11px; color:var(--text-muted);">{{ $item['count'] }} entries</div>
            </div>
            <div style="font-size:13px; font-weight:700; color:#c53030;">
                Rs. {{ number_format($item['total'], 0) }}
            </div>
        </div>
        @endforeach
        <div style="display:flex; justify-content:space-between; padding-top:10px; font-size:13px; font-weight:700;">
            <span>Total Expenses</span>
            <span style="color:#c53030;">Rs. {{ number_format($totalExpenses, 0) }}</span>
        </div>
    </div>
    @endif

    @endif

    {{-- ── INCOME TAB ── --}}
    @if($activeTab === 'income')
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:6px;">Rental Income</div>
                <div style="font-size:24px; font-weight:800; color:#276749;">Rs. {{ number_format($rentalIncome, 0) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:6px;">Sale Income</div>
                <div style="font-size:24px; font-weight:800; color:#276749;">Rs. {{ number_format($saleIncome, 0) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#1a2340; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--navy-muted); margin-bottom:6px;">Total Income</div>
                <div style="font-size:24px; font-weight:800; color:#fff;">Rs. {{ number_format($totalIncome, 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions (credits only) --}}
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Income Transactions</span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions->where('type', 'credit') as $txn)
                <tr>
                    <td style="font-size:12px;">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                    <td style="font-size:12px;">{{ $txn->account->name }}</td>
                    <td>
                        <span class="txn-category-label">
                            {{ ucfirst(str_replace('_', ' ', $txn->category ?? 'other')) }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ $txn->description ?? '—' }}</td>
                    <td style="text-align:right; font-weight:700; color:#276749;">
                        + Rs. {{ number_format($txn->amount, 0) }}
                    </td>
                    <td style="font-size:11px; color:var(--text-muted);">
                        {{ $txn->createdBy?->name ?? 'System' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                        No income transactions this period
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── EXPENSES TAB ── --}}
    @if($activeTab === 'expenses')
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:6px;">Shop Expenses</div>
                <div style="font-size:24px; font-weight:800; color:#c53030;">Rs. {{ number_format($totalExpenses, 0) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:6px;">Salary Paid</div>
                <div style="font-size:24px; font-weight:800; color:#c53030;">Rs. {{ number_format($totalSalaries, 0) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#1a2340; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--navy-muted); margin-bottom:6px;">Total Outflow</div>
                <div style="font-size:24px; font-weight:800; color:#fff;">
                    Rs. {{ number_format($totalExpenses + $totalSalaries, 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown --}}
    <div class="row g-3">
        <div class="col-5">
            <div class="table-card" style="padding:16px 20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    By Category
                </div>
                @forelse($expenseBreakdown as $item)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:10px; height:10px; border-radius:2px; background:{{ $item['color'] }};"></div>
                        <span style="font-size:12px;">{{ $item['name'] }}</span>
                    </div>
                    <div>
                        <span style="font-size:12px; font-weight:700; color:#c53030;">
                            Rs. {{ number_format($item['total'], 0) }}
                        </span>
                        <span style="font-size:10px; color:var(--text-muted); margin-left:4px;">
                            ({{ $item['count'] }})
                        </span>
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                    No expenses this period
                </div>
                @endforelse
            </div>
        </div>

        <div class="col-7">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Expense Transactions</span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions->where('type','debit')->where('category','expense') as $txn)
                        <tr>
                            <td style="font-size:12px;">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                            <td style="font-size:12px;">{{ $txn->account->name }}</td>
                            <td>
                                <span class="txn-category-label" style="font-size:10px;">
                                    {{ ucfirst(str_replace('_', ' ', $txn->category)) }}
                                </span>
                            </td>
                            <td style="font-size:12px;">{{ $txn->description ?? '—' }}</td>
                            <td style="text-align:right; font-weight:700; color:#c53030; font-size:13px;">
                                - Rs. {{ number_format($txn->amount, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                                No expense transactions this period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── ACCOUNTS TAB ── --}}
    @if($activeTab === 'accounts')
    <div class="row g-3 mb-3">
        @foreach($accounts as $account)
        <div class="col-3">
            <div class="account-card">
                <div class="account-type-icon {{ $account->type }}">
                    @if($account->type === 'cash') <i class="bi bi-cash-stack"></i>
                    @elseif($account->type === 'bank') <i class="bi bi-bank"></i>
                    @elseif($account->type === 'mobile_wallet') <i class="bi bi-phone"></i>
                    @else <i class="bi bi-wallet2"></i>
                    @endif
                </div>
                <div class="account-name">{{ $account->name }}</div>
                <div class="account-type-label">{{ ucfirst(str_replace('_', ' ', $account->type)) }}</div>
                <div class="account-balance {{ $account->current_balance < 0 ? 'negative' : '' }}">
                    Rs. {{ number_format($account->current_balance, 0) }}
                </div>
                <div class="account-balance-label">Current Balance</div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:6px;">
                    Opening: Rs. {{ number_format($account->opening_balance, 0) }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- All account transactions this month --}}
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">All Transactions — {{ $currentMonth->format('F Y') }}</span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                    <th style="text-align:right;">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $txn)
                <tr>
                    <td style="font-size:12px;">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                    <td style="font-size:12px;">{{ $txn->account->name }}</td>
                    <td>
                        <span class="txn-type-badge {{ $txn->type }}">
                            {{ $txn->type === 'credit' ? '↑ In' : '↓ Out' }}
                        </span>
                    </td>
                    <td>
                        <span class="txn-category-label">
                            {{ ucfirst(str_replace('_', ' ', $txn->category ?? 'other')) }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ $txn->description ?? '—' }}</td>
                    <td style="text-align:right; font-weight:700; font-size:13px;
                               color:{{ $txn->type === 'credit' ? '#276749' : '#c53030' }};">
                        {{ $txn->type === 'credit' ? '+' : '-' }}
                        Rs. {{ number_format($txn->amount, 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; color:var(--text-muted);">
                        Rs. {{ number_format($txn->balance_after, 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                        No transactions this period
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── SALARY TAB ── --}}
    @if($activeTab === 'salary')
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:6px;">Paid This Month</div>
                <div style="font-size:24px; font-weight:800; color:#276749;">
                    Rs. {{ number_format($totalSalaries, 0) }}
                </div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#fffff0; border:1.5px solid #f6e05e; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#b7791f; margin-bottom:6px;">Pending Advances</div>
                <div style="font-size:24px; font-weight:800; color:#b7791f;">
                    Rs. {{ number_format($pendingAdvances, 0) }}
                </div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:10px; padding:16px 20px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:6px;">Pending Salaries</div>
                <div style="font-size:24px; font-weight:800; color:#c53030;">
                    Rs. {{ number_format($salaryRecords->where('status','draft')->sum('net_salary'), 0) }}
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Salary Records — {{ $currentMonth->format('F Y') }}</span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Salary Type</th>
                    <th>Days Present</th>
                    <th>Base</th>
                    <th>Earned</th>
                    <th>Advances</th>
                    <th>Net</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryRecords as $record)
                <tr>
                    <td style="font-weight:600; font-size:13px;">{{ $record->user->name }}</td>
                    <td>
                        <span class="salary-type-badge {{ $record->user->salary_type }}">
                            {{ ucfirst($record->user->salary_type) }}
                        </span>
                    </td>
                    <td style="text-align:center; font-size:13px;">{{ $record->days_present }}</td>
                    <td style="font-size:12px;">Rs. {{ number_format($record->base_salary, 0) }}</td>
                    <td style="font-size:12px;">Rs. {{ number_format($record->earned_salary, 0) }}</td>
                    <td style="font-size:12px; color:#e53e3e;">
                        @if($record->total_advances > 0)
                            - Rs. {{ number_format($record->total_advances, 0) }}
                        @else — @endif
                    </td>
                    <td style="font-weight:700; font-size:13px; color:var(--navy);">
                        Rs. {{ number_format($record->net_salary, 0) }}
                    </td>
                    <td>
                        <span class="salary-status-badge {{ $record->status }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                        No salary records for this month. Generate from Salary module.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</div>