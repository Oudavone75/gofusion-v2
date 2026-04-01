<div class="dropdown">
    <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
        Type: {{ request()->has('type') && request()->type == 'citizen' ? 'Citizen' : 'All' }}
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item"
            href="{{ route($route_name, array_merge(request()->query(), ['type' => 'all'])) }}">All</a>
        <a class="dropdown-item"
            href="{{ route($route_name, array_merge(request()->query(), ['type' => 'citizen'])) }}">Citizen</a>
    </div>
</div>
