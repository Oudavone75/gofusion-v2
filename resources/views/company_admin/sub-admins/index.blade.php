@extends('company_admin.layout.main')

@section('title', 'Sub-Admins')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Sub-Admins',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        <a href="{{ route('company_admin.sub-admins.create') }}" class="btn btn-primary">Create</a>
                    </div>
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sub_admins as $sub_admin)
                                    <tr>
                                        <td>{{ $sub_admin->first_name }}</td>
                                        <td>{{ $sub_admin->email }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $sub_admin->role?->name }}</span>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="switch-btn"
                                                data-url="{{ route('company_admin.sub-admins.toggle-status', $sub_admin->id) }}"
                                                {{ $sub_admin->status == 'active' ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                    href="#" role="button" data-toggle="dropdown">
                                                    <i class="dw dw-more"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                    <a class="dropdown-item"
                                                        href="{{ route('company_admin.sub-admins.view', $sub_admin->id) }}"><i
                                                            class="dw dw-eye"></i> View</a>
                                                    @if (auth('web')->user()->hasDirectPermission('edit survey feedback'))
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.sub-admins.edit', $sub_admin->id) }}"><i
                                                                class="dw dw-edit2"></i> Edit</a>
                                                    @endif
                                                    @if (auth('web')->user()->hasDirectPermission('delete survey feedback'))
                                                        <a class="dropdown-item delete-image-sub_admin" href="#"
                                                            onClick="deleteRecord(this)" data-id="{{ $sub_admin->id }}"
                                                            data-name="{{ $sub_admin->title }}"
                                                            data-url="{{ route('company_admin.sub-admins.delete', $sub_admin->id) }}">
                                                            <i class="icon-copy fa fa-trash" aria-hidden="true"></i> Delete
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
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
                        {{ $sub_admins->links('pagination::bootstrap-4') }}
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
    <script>
        toggleStatus();
    </script>
@endpush
