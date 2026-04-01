@extends('company_admin.layout.main')

@section('title', 'Sessions')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Sessions',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 text-right">
                        @if (auth('web')->user()->hasDirectPermission('create sessions'))
                            <a href="{{ route('company_admin.sessions.create') }}" class="btn btn-primary">Create</a>
                        @endif
                        @if (auth('web')->user()->hasDirectPermission('import sessions'))
                            <a href="{{ route('company_admin.sessions.import.page') }}" class="btn btn-info ml-2">
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
                                    <th>Campaign Name</th>
                                    <th>Department Name</th>
                                    <th>Status</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit sessions') ||
                                            auth('web')->user()->hasDirectPermission('delete sessions') ||
                                            auth('web')->user()->hasDirectPermission('view sessions'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($go_sessions as $session)
                                    <tr>
                                        <td>{{ $session->title }}</td>
                                        <td>{{ $session->campaignSeason->title }}</td>
                                        <td>{{ $session->campaignSeason->department->name ?? 'Not Available' }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $session->status == 'active' ? 'success' : ($session->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($session->status) }}
                                            </span>
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('edit sessions') ||
                                                auth('web')->user()->hasDirectPermission('delete sessions') ||
                                                auth('web')->user()->hasDirectPermission('view sessions'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('web')->user()->hasDirectPermission('edit sessions'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.sessions.edit', $session) }}"><i
                                                                    class="dw dw-edit2"></i> Edit
                                                            </a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete sessions'))
                                                            <a class="dropdown-item delete-session" href="#"
                                                                data-id="{{ $session->id }}"
                                                                data-name="{{ $session->title }}"
                                                                data-url="{{ route('company_admin.sessions.destroy', $session->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete
                                                            </a>
                                                        @endif
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.sessions.show', $session->id) }}"><i
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
                        {{ $go_sessions->links('pagination::bootstrap-4') }}
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
