@extends('admin.layout.main')

@section('title', 'Campaigns Reward')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Rewards',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        @if (activeCampaignSeasonFilter() == 'campaign')
                            @include('admin.filters.company-filter', [
                                'route_name' => 'admin.rewards.index',
                            ])
                        @endif
                        <div class="dropdown mx-2">
                            <a class="btn btn-info dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                aria-expanded="false">
                                Filter: {{ ucfirst(activeCampaignSeasonFilter()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item"
                                    href="{{ route('admin.rewards.index') }}?type=campaign">Campaign</a>
                                <a class="dropdown-item" href="{{ route('admin.rewards.index') }}?type=season">Season</a>
                            </div>
                            @if (auth('admin')->user()->hasDirectPermission('view custom rewards'))
                                <a href="{{ route('admin.rewards.custom.index') }}" class="btn btn-primary">Custom Rewards</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    </div>
                </div>
            @endif

            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    @if (activeCampaignSeasonFilter() === 'campaign')
                                        <th>Company Name</th>
                                        <th>Department Name</th>
                                    @endif
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    @if (auth('admin')->user()->hasDirectPermission('give rewards'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                    @if (
                                        (activeCampaignSeasonFilter() === 'campaign' && $campaign->company_id !== null) ||
                                            (activeCampaignSeasonFilter() === 'season' && $campaign->company_id === null))
                                        <tr>
                                            <td>{{ $campaign->title }}</td>
                                            @if (activeCampaignSeasonFilter() === 'campaign')
                                                <td>{{ $campaign->company->name ?? 'Not Available' }}</td>
                                                <td>{{ $campaign->department->name ?? 'Not Available' }}</td>
                                            @endif
                                            <td>{{ $campaign->start_date }}</td>
                                            <td>{{ $campaign->end_date }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($campaign->status) }}</span>
                                            </td>
                                            @if (auth('admin')->user()->hasDirectPermission('give rewards'))
                                                <td>
                                                    @if (activeCampaignSeasonFilter() === 'campaign')
                                                        @if ($campaign->departments->isNotEmpty())
                                                            <a href="{{ route('admin.rewards.campaign.view', [$campaign, 'department']) }}"
                                                                class="btn btn-sm btn-info">Rewards</a>
                                                        @else
                                                            <a href="{{ route('admin.rewards.campaign.view', [$campaign, 'personal']) }}"
                                                                class="btn btn-sm btn-info">Rewards</a>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('admin.rewards.campaign.view', $campaign) }}"
                                                            class="btn btn-sm btn-info">Rewards</a>
                                                    @endif
                                                </td>
                                            @else
                                                <td></td>
                                            @endif
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('src/plugins/datatables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('vendors/scripts/datatable-setting.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
