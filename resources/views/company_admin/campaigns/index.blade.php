@extends('company_admin.layout.main')

@section('title', 'Campaigns')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Campaigns',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('create campaigns'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('company_admin.campaigns.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Title</th>
                                    <th>Start Date</th>
                                    <th>End-Date</th>
                                    <th>Status</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit campaigns') ||
                                            auth('web')->user()->hasDirectPermission('delete campaigns') ||
                                            auth('web')->user()->hasDirectPermission('view campaigns'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaign_seasons as $campaign_season)
                                    <tr>
                                        <td>{{ $campaign_season->title }}</td>
                                        <td>{{ $campaign_season->start_date }}</td>
                                        <td>{{ $campaign_season->end_date }}</td>
                                        <td>
                                            @if ($campaign_season->status == 'pending')
                                                <span onclick="changeStatus({{ $campaign_season->id }})"
                                                    id="change-status-{{ $campaign_season->id }}"
                                                    data-url="{{ route('company_admin.campaigns.change-status', $campaign_season->id) }}"
                                                    style="cursor: pointer;"
                                                    class="badge badge-{{ $campaign_season->status == 'active' ? 'success' : ($campaign_season->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($campaign_season->status) }}
                                                </span>
                                            @else
                                                <span
                                                    class="badge badge-{{ $campaign_season->status == 'active' ? 'success' : ($campaign_season->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($campaign_season->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('edit campaigns') ||
                                                auth('web')->user()->hasDirectPermission('delete campaigns') ||
                                                auth('web')->user()->hasDirectPermission('view campaigns'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('web')->user()->hasDirectPermission('edit campaigns'))
                                                            @if ($campaign_season->status == 'pending')
                                                                <a class="dropdown-item"
                                                                    href="{{ route('company_admin.campaigns.edit', $campaign_season) }}"><i
                                                                        class="dw dw-edit2"></i> Edit
                                                                </a>
                                                            @endif
                                                            @if (auth('web')->user()->hasDirectPermission('delete campaigns'))
                                                                <a class="dropdown-item delete-campaign" href="#"
                                                                    data-id="{{ $campaign_season->id }}"
                                                                    data-name="{{ $campaign_season->title }}"
                                                                    data-url="{{ route('company_admin.campaigns.destroy', $campaign_season->id) }}">
                                                                    <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                    Delete
                                                                </a>
                                                            @endif
                                                        @endif
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.campaigns.view', $campaign_season->id) }}"><i
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
                        {{ $campaign_seasons->links('pagination::bootstrap-4') }}
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
