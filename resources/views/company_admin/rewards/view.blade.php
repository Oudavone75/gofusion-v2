@extends('company_admin.layout.main')

@section('title', 'Reward')
@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => $campaign->title . ' Reward',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link text-blue active" data-toggle="tab" href="#details" role="tab"
                                    aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-blue" data-toggle="tab" href="#department-rankings" role="tab"
                                    aria-selected="false">Department Ranking</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="details" role="tabpanel">
                                <form action="{{ route('company_admin.rewards.store', $campaign->id) }}"
                                    onsubmit="submitForm(event,this,'/company-admin/rewards',true)" method="POST">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-hover nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Ranking</th>
                                                    <th>Name</th>
                                                    <th>Points</th>
                                                    <th>Rewards (€)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($users_with_levels as $key => $level)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>{{ $level->first_name }} {{ $level->last_name }}</td>
                                                        <td>{{ $level->points }}</td>
                                                        <td>
                                                            <input type="number" name="reward[]"
                                                                {{ $is_reward_given ? 'disabled' : '' }} class="form-control"
                                                                required
                                                                value="{{ isset($level->reward) ? $level->reward : '' }}"
                                                                placeholder="Enter Points" min="0" step="0.01">
                                                            <input type="hidden" name="user_id[]"
                                                                value="{{ $level->user_id }}">
                                                        </td>
                                                        @if (!$is_reward_given && count($users_with_levels) > 0)
                                                            <td>
                                                                <div class="col-md-1">
                                                                    <button type="button" class="btn btn-danger remove-user">
                                                                        <i class="icon-copy fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">No data available.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @if (!$is_reward_given && count($users_with_levels) > 0)
                                        <div
                                            class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                                            <button class="btn btn-primary submit-btn" id="submit-button">Submit</button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                            <div class="tab-pane fade" id="department-rankings" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover nowrap">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Ranking</th>
                                                <th>Total Points(XP)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($departmentRankings as $index => $ranking)
                                                <tr>
                                                    <td>{{ $ranking->department_name }}</td>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $ranking->total_points }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No rankings available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
        document.addEventListener("DOMContentLoaded", initRewardTable);
        window.disableSubmitButton = disableSubmitButton;
    </script>
@endpush
