{{--
    Livewire-aware simple pagination (Bootstrap 5 markup).
    Uses wire:click so pagination happens via AJAX inside the Livewire
    component instead of a full-page navigation. This avoids the relative-URL
    doubling bug (e.g. /reports/reports/purchase-sale?page=2 -> 404) that the
    plain href version produced on nested routes.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <ul class="pagination mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{!! __('pagination.previous') !!}</span>
                </li>
            @else
                <li class="page-item">
                    <button type="button" class="page-link" rel="prev"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            wire:loading.attr="disabled">
                        {!! __('pagination.previous') !!}
                    </button>
                </li>
            @endif

            {{-- Current page indicator --}}
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">{{ $paginator->currentPage() }}</span>
            </li>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <button type="button" class="page-link" rel="next"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            wire:loading.attr="disabled">
                        {!! __('pagination.next') !!}
                    </button>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{!! __('pagination.next') !!}</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
