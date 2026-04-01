@extends('admin.layout.main')

@section('title', 'Contact Requests')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="min-height-200px">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', ['page_title' => 'Contact Requests', 'paths' => breadcrumbs()])
            </div>
        </div>
        <div class="card-box mb-30">
            <div class="pb-20">
                @if ($company_contacts->contains(fn($c) => !$c->mark_as_read))
                    <div class="d-flex justify-content-end px-3 pt-3 mb-3">
                        <form action="{{ route('admin.company-contact.mark-all-as-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="dw dw-check"></i> Mark All as Read
                            </button>
                        </form>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover nowrap">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company_contacts as $contact)
                            <tr style="{{ !$contact->mark_as_read ? 'background-color: #fff3cd;' : '' }}">
                                <td>{!! !$contact->mark_as_read ? '<strong>' . e($contact->first_name) . '</strong>' : e($contact->first_name) !!}</td>
                                <td>{!! !$contact->mark_as_read ? '<strong>' . e($contact->last_name) . '</strong>' : e($contact->last_name) !!}</td>
                                <td>{!! !$contact->mark_as_read ? '<strong>' . e($contact->email) . '</strong>' : e($contact->email) !!}</td>
                                <td>
                                    @if (!$contact->mark_as_read)
                                        <span class="badge badge-danger">Unread</span>
                                    @else
                                        <span class="badge badge-success">Read</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($contact->created_at)->format('d F Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="{{ route('admin.company-contact.show', $contact) }}"><i class="dw dw-eye"></i> View</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $company_contacts->links('pagination::bootstrap-4') }}
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
