@extends('admin.layout.main')

@section('title', !is_null($campaign->company_id) ? 'View Campaign' : 'View Season')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => !is_null($campaign->company_id) ? 'View Campaign' : 'View Season',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="tab">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link text-blue active" data-toggle="tab" href="#home" role="tab"
                                aria-selected="true">Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-blue" data-toggle="tab" href="#profile" role="tab"
                                aria-selected="false">Department Ranking</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="home" role="tabpanel">
                            <div class="row pd-20">
                                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                                    <label class="form-control-label bold">Title</label>
                                    <p class="form-control-plaintext">{{ $campaign->title ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                                    <label class="form-control-label bold">Description</label>
                                    <p class="form-control-plaintext">{{ $campaign->description ?? '—' }}</p>
                                </div>
                                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                                    <label class="form-control-label bold">Custom Reward</label>
                                    <p class="form-control-plaintext">{{ $campaign->custom_reward ?? '—' }}</p>
                                </div>
                                @if (!is_null($campaign->company_id))
                                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                                        <label class="form-control-label bold">Company</label>
                                        <p class="form-control-plaintext">{{ $campaign->company->name ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                                        <label class="form-control-label bold">Departments</label>
                                        <p class="form-control-plaintext">
                                            {{ $campaign->departments->isNotEmpty()
                                                ? $campaign->departments->pluck('name')->join(', ')
                                                : 'Not Available' }}
                                        </p>
                                    </div>
                                @endif
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-control-label bold">Start Date</label>
                                    <p class="form-control-plaintext">{{ $campaign->start_date }}</p>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-control-label bold">End Date</label>
                                    <p class="form-control-plaintext">{{ $campaign->end_date }}</p>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-control-label bold">Status</label>
                                    <p class="form-control-plaintext text-capitalize">
                                        <span
                                            class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                                            {{ $campaign->status ?? '—' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="row">
                                    @foreach ($campaign->campaignsSeasonsRewardRanges as $key => $range)
                                        <div class="col-md-12 col-sm-12 mt-2 d-flex align-items-center reward-field">
                                            <div class="col-md-3">
                                                <label class="form-control-label bold">From Ranking</label>
                                                <input type="number" name="from_ranking[]" value="{{ $range->rank_start }}"
                                                    class="form-control" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-control-label bold">To Ranking</label>
                                                <input type="number" name="to_ranking[]" value="{{ $range->rank_end }}"
                                                    class="form-control" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-control-label bold">Reward <small>(In €)</small></label>
                                                <input type="number" name="reward[]" value="{{ $range->reward }}"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="profile" role="tabpanel">
                            <div class="pd-20">
                                <table class="table table-bordered">
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
@endsection
