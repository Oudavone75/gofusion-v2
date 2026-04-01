@extends('company_admin.layout.main')

@section('title', 'Custom Rewards')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            @if (session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Custom Rewards',
                        'paths' => breadcrumbs(),
                    ])

                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        @if (auth('web')->user()->hasDirectPermission('create custom rewards'))
                            <a href="{{ route('company_admin.rewards.custom.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>
                                        Campaign Name
                                    </th>
                                    <th>
                                        Reward / Incentives
                                    </th>
                                    <th>Status</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit custom rewards') ||
                                            auth('web')->user()->hasDirectPermission('view custom rewards'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign?->title }}</td>
                                        <td>{{ $campaign?->custom_reward ?? 'Not Available' }}</td>
                                        @if (auth('web')->user()->hasDirectPermission('manage custom rewards status'))
                                            <td>
                                                <input type="checkbox" class="switch-btn"
                                                    data-url="{{ route('company_admin.rewards.custom.toggle-status', $campaign->id) }}"
                                                    {{ $campaign->custom_reward_status ? 'checked' : '' }}>
                                            </td>
                                        @else
                                            <td>
                                                <span
                                                    class="badge badge-{{ $campaign->custom_reward_status ? 'success' : 'danger' }} d-inline px-2 py-1">
                                                    {{ $campaign->custom_reward_status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        @endif

                                        @if (auth('web')->user()->hasDirectPermission('edit custom rewards') ||
                                                auth('web')->user()->hasDirectPermission('view custom rewards'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown"
                                                        data-boundary="viewport">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.rewards.custom.view', $campaign->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if (auth('web')->user()->hasDirectPermission('edit custom rewards'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.rewards.custom.edit', $campaign->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit
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
    <script>
        toggleStatus();
    </script>
@endpush
