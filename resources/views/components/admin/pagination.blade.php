@props(['paginator', 'label' => 'records'])

@if ($paginator->total() > 0)
    <nav class="admin-pagination" aria-label="Pagination">
        <p class="admin-pagination__count">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} {{ $label }}
        </p>

        @if ($paginator->hasPages())
            <div class="admin-pagination__links">
                @if ($paginator->onFirstPage())
                    <span class="is-disabled" aria-hidden="true">Prev</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
                @endif

                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                @else
                    <span class="is-disabled" aria-hidden="true">Next</span>
                @endif
            </div>
        @endif
    </nav>
@endif
