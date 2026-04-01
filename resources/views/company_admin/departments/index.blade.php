@extends('company_admin.layout.main')

@section('title', 'Departments')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Departments',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('create departments'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('company_admin.departments.create') }}" class="btn btn-primary">Create</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Company Name</th>
                                    <th>Created By</th>
                                    <th>Total Users</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit departments') ||
                                            auth('web')->user()->hasDirectPermission('delete departments'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($company_departments as $company_department)
                                    <tr>
                                        <td>{{ $company_department->name }}</td>
                                        <td>{{ $company_department->company->name }}</td>
                                        <td>{{ $company_department->createdBy->full_name ?? $company_department->createdByAdmin->name }}
                                        </td>
                                        <td>
                                            @if (auth('web')->user()->hasDirectPermission('view departments users'))
                                                <a class="badge badge-primary"
                                                    href="{{ route('company_admin.departments.department-users', $company_department->id) }}">
                                                    {{ $company_department->users_count }}
                                                </a>
                                            @else
                                                <span class="badge badge-primary">
                                                    {{ $company_department->users_count }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $company_department->status == 'active' ? 'success' : ($company_department->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($company_department->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $company_department->created_at }}</td>
                                        @if (auth('web')->user()->hasDirectPermission('edit departments') ||
                                                auth('web')->user()->hasDirectPermission('delete departments'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('web')->user()->hasDirectPermission('edit departments'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.departments.edit', $company_department) }}"><i
                                                                    class="dw dw-edit2"></i> Edit
                                                            </a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete departments'))
                                                            <a class="dropdown-item delete-department" href="#"
                                                                data-id="{{ $company_department->id }}"
                                                                data-name="{{ $company_department->name }}"
                                                                data-check-url="{{ route('company_admin.departments.has-active-campaigns', $company_department->id) }}"
                                                                data-url="{{ route('company_admin.departments.destroy', $company_department->id) }}"><i
                                                                    class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete
                                                            </a>
                                                        @endif
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
                        {{ $company_departments->links('pagination::bootstrap-4') }}
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
