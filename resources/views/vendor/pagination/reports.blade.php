{{-- resources/views/vendor/pagination/reports.blade.php --}}
{{-- Custom minimal pagination for the reports page --}}

@if ($paginator->hasPages())
<nav aria-label="{{ __('Pagination') }}">
    <ul class="pagination mb-0">

        {{-- Previous page --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    <i class="fas fa-chevron-left" style="font-size:0.65rem;"></i>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    <i class="fas fa-chevron-left" style="font-size:0.65rem;"></i>
                </a>
            </li>
        @endif

        {{-- Page numbers (window of 5) --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next page --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    <i class="fas fa-chevron-right" style="font-size:0.65rem;"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    <i class="fas fa-chevron-right" style="font-size:0.65rem;"></i>
                </span>
            </li>
        @endif

    </ul>
</nav>
@endif
