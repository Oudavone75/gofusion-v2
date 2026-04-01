@extends('admin.layout.main')

@section('title', 'Campaigns / Seasons')
@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => ucfirst(activeCampaignSeasonFilter()),
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end ">
                        @if (activeCampaignSeasonFilter() == 'campaign')
                            @include('admin.filters.company-filter', [
                                'route_name' => 'admin.campaign.index',
                            ])
                        @endif
                        <div class="dropdown mx-2">
                            <a class="btn btn-info dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                aria-expanded="false">
                                Filter: {{ ucfirst(activeCampaignSeasonFilter()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a class="dropdown-item"
                                    href="{{ route('admin.campaign.index') }}?type=campaign">Campaign</a>
                                <a class="dropdown-item" href="{{ route('admin.campaign.index') }}?type=season">Season</a>
                            </div>
                        </div>
                        @if (auth('admin')->user()->hasDirectPermission('create campaigns'))
                            <a href="{{ route('admin.campaign.create') }}" class="btn btn-primary">Create</a>
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
                                    @if (activeCampaignSeasonFilter() == 'campaign')
                                        <th>Company Name</th>
                                    @endif
                                    <th>Start Date</th>
                                    <th>End-Date</th>
                                    <th>Status</th>
                                    @if (auth('admin')->user()->hasDirectPermission('manage campaigns status') ||
                                            auth('admin')->user()->hasDirectPermission('edit campaigns') ||
                                            auth('admin')->user()->hasDirectPermission('delete campaigns') ||
                                            auth('admin')->user()->hasDirectPermission('view campaigns'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->title }}</td>
                                        @if (activeCampaignSeasonFilter() == 'campaign')
                                            <td>{{ $campaign->company->name ?? 'Not Available' }}</td>
                                        @endif
                                        <td>{{ $campaign->start_date }}</td>
                                        <td>{{ $campaign->end_date }}</td>
                                        <td>
                                            @if ($campaign->status == 'pending')
                                                @if (auth('admin')->user()->hasDirectPermission('manage campaigns status'))
                                                    <span onclick="changeStatus({{ $campaign->id }})"
                                                        id="change-status-{{ $campaign->id }}"
                                                        data-url="{{ route('admin.campaign.change-status', $campaign->id) }}"
                                                        style="cursor: pointer;"
                                                        class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($campaign->status) }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($campaign->status) }}
                                                    </span>
                                                @endif
                                            @else
                                                <span
                                                    class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($campaign->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        @if (auth('admin')->user()->hasDirectPermission('manage campaigns status') ||
                                                auth('admin')->user()->hasDirectPermission('edit campaigns') ||
                                                auth('admin')->user()->hasDirectPermission('delete campaigns') ||
                                                auth('admin')->user()->hasDirectPermission('view campaigns'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown" data-boundary="viewport">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.campaign.view', $campaign->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if ($campaign->status == 'pending')
                                                            @if (auth('admin')->user()->hasDirectPermission('edit campaigns'))
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.campaign.edit', $campaign->id) }}"><i
                                                                        class="dw dw-edit2"></i> Edit</a>
                                                            @endif
                                                            @if (auth('admin')->user()->hasDirectPermission('delete campaigns'))
                                                                <a class="dropdown-item delete-campaign" href="#"
                                                                    data-id="{{ $campaign->id }}"
                                                                    data-name="{{ $campaign->title }}"
                                                                    data-url="{{ route('admin.campaign.delete', $campaign->id) }}">
                                                                    <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                    Delete</a>
                                                            @endif
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
                        {{ $campaigns->links('pagination::bootstrap-4') }}
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
