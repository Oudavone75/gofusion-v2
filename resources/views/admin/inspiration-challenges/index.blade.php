@extends('admin.layout.main')

@section('title', 'Inspiration Challenges')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row justify_between">
                    @include('admin.components.page-title', [
                        'page_title' => 'Inspiration Challenges',
                        'paths' => breadcrumbs(),
                        ($fitContent = 'fit-content'),
                    ])
                    <div class="col-md-12 col-sm-6 flexx justify_end gap_10 fit-content">
                        {{-- Filter Button --}}
                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#filterModal">
                            <i class="icon-copy dw dw-filter mx-1"></i> Filter
                            @if (request()->filled('company_id') || (request()->has('type') && request()->get('type') != 'all'))
                                <span class="badge badge-warning ml-1" style="border-radius: 50%; padding: 2px 6px;">
                                    <i class="fa fa-check" style="font-size: 8px;"></i>
                                </span>
                            @endif
                        </button>

                        @if (auth('admin')->user()->hasDirectPermission('view inspiration challenges user requests'))
                            <a href="{{ route('admin.inspiration-challenges.pending') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #04948C 0%, #03D3C7 100%); border: none;">
                                Requests<span class="badge badge-warning ml-2 count-badge">{{ $pending_challenges_count }}</span></a>
                        @endif
                        @if (auth('admin')->user()->hasDirectPermission('create inspiration challenges'))
                            <a href="{{ route('admin.inspiration-challenges.create') }}" class="btn btn-primary">
                                Create
                            </a>
                        @endif
                        @if (auth('admin')->user()->hasDirectPermission('manage inspiration challenges import'))
                            <a href="{{ route('admin.inspiration-challenges.import') }}" class="btn btn-info text-white">
                                <span class="micon icon-copy dw dw-file mx-1"></span><span class="mtext">Import</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Company Name</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Points</th>
                                    <th>Attempted Users</th>
                                    @if (auth('admin')->user()->hasDirectPermission('edit inspiration challenges') ||
                                            auth('admin')->user()->hasDirectPermission('delete inspiration challenges') ||
                                            auth('admin')->user()->hasDirectPermission('view inspiration challenges'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($challenges as $challenge)
                                    <tr>
                                        <td>{{ $challenge->title }}</td>
                                        <td>{{ $challenge->company->name ?? 'Not Available' }}</td>
                                        <td>{{ $challenge?->createdBy?->full_name ?? (auth('admin')->user()->name ?? 'Not Available') }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $challenge->status == 'approved' ? 'success' : ($challenge->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($challenge->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $challenge->attempted_points }}</td>
                                        <td>
                                            <a class="badge badge-primary"
                                                href="{{ $challenge->challenge_points_count > 0 ? route('admin.inspiration-challenges.attempted-users-list', $challenge->id) : '#' }}">
                                                {{ format_number_short($challenge->challenge_points_count) }} </a>
                                        </td>
                                        @if (auth('admin')->user()->hasDirectPermission('edit inspiration challenges') ||
                                                auth('admin')->user()->hasDirectPermission('delete inspiration challenges') ||
                                                auth('admin')->user()->hasDirectPermission('view inspiration challenges'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('admin')->user()->hasDirectPermission('edit inspiration challenges'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.inspiration-challenges.edit', $challenge->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('admin')->user()->hasDirectPermission('delete inspiration challenges'))
                                                            <a class="dropdown-item delete-challenge" href="#"
                                                                data-id="{{ $challenge->id }}"
                                                                data-name="{{ $challenge->title }}"
                                                                data-url="{{ route('admin.inspiration-challenges.destroy', $challenge->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete</a>
                                                        @endif
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.inspiration-challenges.show', $challenge->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $challenges->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-radius-10">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel text-primary">
                        <i class="icon-copy dw dw-filter mx-1"></i> Filter Challenges
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold">Filter By Company</label>
                            <div class="filter-wrapper">
                                @include('admin.filters.company-filter', [
                                    'route_name' => 'admin.inspiration-challenges.index',
                                ])
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold">Filter By Type</label>
                            <div class="filter-wrapper">
                                @include('admin.filters.type-filter', [
                                    'route_name' => 'admin.inspiration-challenges.index',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if (request()->filled('company_id') || (request()->has('type') && request()->get('type') != 'all'))
                        <a href="{{ route('admin.inspiration-challenges.index') }}" class="btn btn-outline-danger">
                            Clear All Filters
                        </a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .filter-wrapper .dropdown .btn {
            width: 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #ced4da;
            color: #495057;
            padding: 10px 15px;
        }

        .filter-wrapper .dropdown-menu {
            width: 100%;
        }

        .modal-content {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('scripts')
    <!-- buttons for Export datatable -->
    <script src="{{ asset('src/plugins/datatables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/vfs_fonts.js') }}"></script>
    <!-- Datatable Setting js -->
    <script src="{{ asset('vendors/scripts/datatable-setting.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
