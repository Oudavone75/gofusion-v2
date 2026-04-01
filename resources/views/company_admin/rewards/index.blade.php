@extends('company_admin.layout.main')

@section('title', 'Campaigns Reward')
@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Campaigns Reward',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        @if (auth('web')->user()->hasDirectPermission('view custom rewards'))
                        <a href="{{ route('company_admin.rewards.custom.index') }}" class="btn btn-primary">Custom Rewards</a>
                        @endif
                    </div>
                </div>
            </div>
            @if (session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
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
                                    <th>Company Name</th>
                                    <th>Department Name</th>
                                    <th>Start Date</th>
                                    <th>End-Date</th>
                                    <th>Status</th>
                                    @if (auth('web')->user()->hasDirectPermission('give rewards'))
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
                                        <td>{{ $campaign->company->name ?? 'Not Available' }}</td>
                                        <td>{{ $campaign->department->name ?? 'Not Available' }}</td>
                                        <td>{{ $campaign->start_date }}</td>
                                        <td>{{ $campaign->end_date }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst($campaign->status) }}
                                            </span>
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('give rewards'))
                                            <td>
                                                @if ($campaign->departments->isNotEmpty())
                                                    <a href="{{ route('company_admin.rewards.campaign.view', [$campaign, 'department']) }}"
                                                        class="btn btn-sm btn-info">Rewards</a>
                                                @else
                                                    <a href="{{ route('company_admin.rewards.campaign.view', [$campaign, 'personal']) }}"
                                                        class="btn btn-sm btn-info">Rewards</a>
                                                @endif
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
