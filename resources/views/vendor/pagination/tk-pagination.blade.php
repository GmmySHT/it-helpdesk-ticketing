@if ($paginator->hasPages())
    <nav class="tk-pagination" role="navigation" aria-label="Pagination Navigation">
        <ul class="tk-pagination-list">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="tk-page-item tk-disabled" aria-disabled="true">
                    <span class="tk-page-link" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li class="tk-page-item">
                    <a class="tk-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)

                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="tk-page-item tk-dots" aria-disabled="true">
                        <span class="tk-page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="tk-page-item tk-active" aria-current="page">
                                <span class="tk-page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="tk-page-item">
                                <a class="tk-page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif

            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="tk-page-item">
                    <a class="tk-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="tk-page-item tk-disabled" aria-disabled="true">
                    <span class="tk-page-link" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif

        </ul>
    </nav>
@endif
