@extends('company_admin.layout.main')

@section('title', 'Inspiration Challenges')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row justify_between">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Inspiration Challenges',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-12 col-sm-6 flexx justify_end gap_10 fit-content">
                        @if (auth('web')->user()->hasDirectPermission('view inspiration challenges user requests'))
                            <a href="{{ route('company_admin.inspiration-challenges.pending') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #04948C 0%, #03D3C7 100%); border: none;">
                                Requests<span class="badge badge-warning ml-2 count-badge">{{ $pending_challenges_count }}</span></a>
                        @endif
                        @if (auth('web')->user()->hasDirectPermission('create inspiration challenges'))
                            <a href="{{ route('company_admin.inspiration-challenges.create') }}" class="btn btn-primary">
                                Create
                            </a>
                        @endif
                        @if (auth('web')->user()->hasDirectPermission('manage inspiration challenges import'))
                            <a href="{{ route('company_admin.inspiration-challenges.import') }}" class="btn btn-info text-white">
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
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Points</th>
                                    <th>Attempted Users</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit inspiration challenges') ||
                                            auth('web')->user()->hasDirectPermission('delete inspiration challenges') ||
                                            auth('web')->user()->hasDirectPermission('view inspiration challenges'))
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
                                        <td>{{ $challenge?->createdBy?->full_name ?? (auth('web')->user()->full_name ?? 'Not Available') }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $challenge->status == 'approved' ? 'success' : ($challenge->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($challenge->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $challenge->attempted_points }}</td>
                                        <td>
                                            @if (auth('web')->user()->hasDirectPermission('view inspiration challenges attempted users'))
                                                <a class="badge badge-primary"
                                                    href="{{ $challenge->challenge_points_count > 0 ? route('company_admin.inspiration-challenges.attempted-users-list', $challenge->id) : '#' }}">
                                                    {{ format_number_short($challenge->challenge_points_count) }} </a>
                                            @else
                                                <span class="badge badge-primary">
                                                    {{ format_number_short($challenge->challenge_points_count) }}
                                                </span>
                                            @endif
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('edit inspiration challenges') ||
                                                auth('web')->user()->hasDirectPermission('delete inspiration challenges') ||
                                                auth('web')->user()->hasDirectPermission('view inspiration challenges'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('web')->user()->hasDirectPermission('edit inspiration challenges'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.inspiration-challenges.edit', $challenge) }}"><i
                                                                    class="dw dw-edit2"></i> Edit
                                                            </a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete inspiration challenges'))
                                                            <a class="dropdown-item delete-challenge" href="#"
                                                                data-id="{{ $challenge->id }}"
                                                                data-name="{{ $challenge->title }}"
                                                                data-url="{{ route('company_admin.inspiration-challenges.destroy', $challenge->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete
                                                            </a>
                                                        @endif

                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.inspiration-challenges.show', $challenge->id) }}"><i
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
@endsection

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
