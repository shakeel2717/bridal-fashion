@props([
    'period' => 'day',
    'label' => '',
    'range' => '',
    'canNext' => true,
    'customFrom' => '',
    'customTo' => '',
    'current' => true,
])

{{-- Row 1: period tabs --}}
<div class="period-bar">
    <div class="period-tabs">
        @foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'custom' => 'Custom'] as $key => $text)
            <button type="button"
                    class="period-tab {{ $period === $key ? 'active' : '' }}"
                    wire:click="setPeriod('{{ $key }}')">
                {{ $text }}
            </button>
        @endforeach
    </div>

    {{-- Row 2: < label > navigation --}}
    <div class="period-nav">
        <button type="button" class="period-arrow" wire:click="periodPrev" title="Previous">
            <i class="bi bi-chevron-left"></i>
        </button>

        <button type="button"
                class="period-current {{ $current ? 'is-current' : '' }}"
                wire:click="periodReset"
                title="Jump back to the current period">
            <span class="period-current-label">{{ $label }}</span>
            <span class="period-current-range">{{ $range }}</span>
        </button>

        <button type="button"
                class="period-arrow"
                wire:click="periodNext"
                @disabled(! $canNext)
                title="Next">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    @if ($period === 'custom')
        <div class="period-custom">
            <input type="date" wire:model.live="customFrom" class="form-control form-control-sm">
            <span>to</span>
            <input type="date" wire:model.live="customTo" class="form-control form-control-sm">
        </div>
    @endif

    <div class="period-extra">
        {{ $slot }}
    </div>
</div>
