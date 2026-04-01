@extends('admin.layout.main')

@section('title', 'Notifications')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="min-height-200px">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', ['page_title' => 'Notifications', 'paths' => breadcrumbs()])
                <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                    @if(activeCampaignSeasonFilter() == "campaign")
                    @include('admin.filters.company-filter', ['route_name' => 'admin.notifications.index'])
                    @endif
                    <div class="dropdown mx-2">
                        <a class="btn btn-info dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                            aria-expanded="false">
                            Filter: {{ucfirst(activeCampaignSeasonFilter())}}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <a class="dropdown-item"
                                href="{{ route('admin.notifications.index') }}?type=campaign">Campaign</a>
                            <a class="dropdown-item" href="{{ route('admin.notifications.index') }}?type=season">Season</a>
                        </div>
                    </div>
                    @if(auth('admin')->user()->hasDirectPermission('create notifications'))
                        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">Create</a>
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
                                @if(activeCampaignSeasonFilter() == "campaign")
                                <th>Company Name</th>
                                @endif
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                            <tr>
                                <td>{{ $notification->title }}</td>
                                @if(activeCampaignSeasonFilter() == "campaign")
                                <td>{{ $notification->company->name ?? 'Not Available' }}</td>
                                @endif
                                <td>
                                    <a class="badge badge-primary"
                                        href="{{ route('admin.notifications.recipients', $notification->id) }}">
                                        {{ $notification->users_count }}
                                    </a>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $notification->status == 'sent' ? 'success' : ($notification->status == 'scheduled' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($notification->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                            role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item"
                                                href="{{ route('admin.notifications.show', $notification->id) }}"><i
                                                    class="dw dw-eye"></i> View</a>
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
                    {{ $notifications->links('pagination::bootstrap-4') }}
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
