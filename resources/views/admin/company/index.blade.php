@extends('admin.layout.main')

@section('title', 'Companies')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Companies',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('admin')->user()->hasDirectPermission('create companies'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('admin.company.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Total Users</th>
                                    <th>Created By</th>
                                    @if (auth('admin')->user()->hasDirectPermission('edit companies') ||
                                            auth('admin')->user()->hasDirectPermission('delete companies') ||
                                            auth('admin')->user()->hasDirectPermission('view companies'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companies as $company)
                                    <tr>
                                        <td>{{ $company->name }}</td>
                                        <td>{{ $company->code }}</td>
                                        <td>{{ $company->mode->name == 'Event' ? 'Media Impact' : $company->mode->name }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $company->status == 'active' ? 'success' : ($company->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($company->status) }}
                                            </span>
                                        </td>
                                        @if (auth('admin')->user()->hasDirectPermission('view companies users'))
                                            <td><a class="badge badge-primary"
                                                    href="{{ route('admin.company.company-users', $company->id) }}">
                                                    {{ $company->users_count }} </a></td>
                                        @else
                                            <td>{{ $company->users_count }}</td>
                                        @endif
                                        <td>{{ $company->admin->name }}</td>
                                        @if (auth('admin')->user()->hasDirectPermission('edit companies') ||
                                                auth('admin')->user()->hasDirectPermission('delete companies') ||
                                                auth('admin')->user()->hasDirectPermission('view companies'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown" data-boundary="viewport">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('admin')->user()->hasDirectPermission('edit companies'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.company.edit', $company) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('admin')->user()->hasDirectPermission('delete companies'))
                                                            <a class="dropdown-item delete-company" href="#"
                                                                data-id="{{ $company->id }}"
                                                                data-url="{{ route('admin.company.delete', $company->id) }}"
                                                                data-name="{{ $company->name }}"><i
                                                                    class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete</a>
                                                        @endif
                                                        <a class="dropdown-item manage-join-links" href="#"
                                                            data-company-id="{{ $company->id }}"
                                                            data-company-name="{{ $company->name }}"
                                                            ><i class="icon-copy fa fa-link" aria-hidden="true"></i> Manage Join Links</a>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.company.show', $company) }}"><i
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
                        {{ $companies->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Manage Join Links Modal --}}
    @include('modals.join-links-modal')

    {{-- QR Code Download Modal --}}
    @include('modals.qr-code-modal')

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
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('vendors/scripts/company-join-token.js') }}"></script>

    <script>
        $(document).ready(function() {
            initJoinTokenManager({
                isSuperAdmin: true,
                generateUrl: "{{ route('admin.company.generate-join-token', ':id') }}",
                listUrl: "{{ route('admin.company.join-tokens', ':id') }}",
                revokeUrl: '/admin/company/revoke-join-token/:id',
                csrfToken: "{{ csrf_token() }}"
            });
        });

        function getCsrfToken() {
            return "{{ csrf_token() }}";
        }
    </script>
@endpush
