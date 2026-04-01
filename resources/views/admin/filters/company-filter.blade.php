@if (request()->get('type') != 'citizen')
    <div class="dropdown">
        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-toggle="dropdown"
            aria-expanded="false">
            Company: {{ request()->company_id ? $companies->firstWhere('id', request()->company_id)->name : 'All' }}
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item"
                href="{{ route($route_name, array_merge(request()->query(), ['company_id' => null])) }}">All
                Companies</a>
            @foreach ($companies as $company)
                <a class="dropdown-item"
                    href="{{ route($route_name, array_merge(request()->query(), ['company_id' => $company->id])) }}">
                    {{ $company->name }}
                </a>
            @endforeach
        </div>
    </div>
@endif
