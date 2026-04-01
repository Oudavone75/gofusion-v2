@extends('company_admin.layout.main')

@section('title', 'Dashboard')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', ['page_title' => 'Dashboard'])
                </div>
            </div>
            <div class="mb-30">
                <div class="row">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <a href="{{ route('company_admin.campaigns.index') }}">
                                    <div class="stats-card stats-card-primary">
                                        <div class="stats-card-body">
                                            <div class="stats-header">
                                                <span class="stats-title">Total Campaigns</span>
                                                <div class="stats-icon">
                                                    <i class="micon dw dw-speaker-1"></i>
                                                </div>
                                            </div>
                                            <div class="stats-number">{{ $campaignCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('company_admin.departments.index') }}">
                                    <div class="stats-card stats-card-secondary">
                                        <div class="stats-card-body">
                                            <div class="stats-header">
                                                <span class="stats-title">Total Departments</span>
                                                <div class="stats-icon">
                                                    <i class="micon dw dw-building1"></i>
                                                </div>
                                            </div>
                                            <div class="stats-number">{{ $departmentCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <a href="{{ route('company_admin.employees.index') }}">
                                    <div class="stats-card stats-card-quaternary">
                                        <div class="stats-card-body">
                                            <div class="stats-header">
                                                <span class="stats-title">Total Employees</span>
                                                <div class="stats-icon">
                                                    <i class="micon icon-copy dw dw-user2"></i>
                                                </div>
                                            </div>
                                            <div class="stats-number">{{ $employeeCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('company_admin.inspiration-challenges.index') }}">
                                    <div class="stats-card stats-card-tertiary ">
                                        <div class="stats-card-body">
                                            <div class="stats-header">
                                                <span class="stats-title">Total Inspirational Challenges</span>
                                                <div class="stats-icon">
                                                    <i class="micon dw dw-startup"></i>
                                                </div>
                                            </div>
                                            <div class="stats-number">{{ $inspirationalChallengeCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($challenges->count() > 0)
                <div class="page-header">
                    <div class="row">
                        @include('admin.components.page-title', [
                            'page_title' => 'Challenges Created By Users',
                        ])
                    </div>
                </div>
                <div class="card-box mb-30">
                    <div class="pb-20">
                        <div class="table-responsive">
                            <table class="table table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($challenges as $challenge)
                                        <tr>
                                            <td>{{ $challenge->title }}</td>
                                            <td>{{ $challenge->description }}</td>
                                            <td>
                                                @if (auth('web')->user()->hasDirectPermission('manage inspiration challenges user requests'))
                                                    <!-- Accept Button -->
                                                    <form
                                                        action="{{ route('company_admin.inspiration-challenges.pending.status', [$challenge->id, 'accept']) }}"
                                                        onsubmit="addPoints(event,this,true)" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="points" id="points-status" value="">
                                                        <input type="hidden" name="description" id="description"
                                                            value="{{ $challenge?->description }}">
                                                        <input type="hidden" name="guideline_text" id="guideline_text"
                                                            value="">
                                                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                    </form>
                                                    <!-- Reject Button -->
                                                    <form
                                                        action="{{ route('company_admin.inspiration-challenges.pending.status', [$challenge->id, 'reject']) }}"
                                                        onsubmit="addPoints(event,this)" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                    </form>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.inspiration-challenges.pending.details', [$challenge->id]) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center pb-20">
                        <a href="{{ route('company_admin.inspiration-challenges.pending') }}" class="btn btn-primary">
                            <i class="micon dw dw-eye"></i> View All Challenges
                        </a>
                    </div>
                </div>
            @elseif($go_sessions->count() > 0)
                <div class="page-header">
                    <div class="row">
                        @include('company_admin.components.page-title', ['page_title' => 'Sessions'])
                    </div>
                </div>
                <div class="card-box mb-30">
                    <div class="pb-20">
                        <div class="table-responsive">
                            <table class="table table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Campaign Name</th>
                                        <th>Department Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($go_sessions as $session)
                                        <tr>
                                            <td>{{ $session->title }}</td>
                                            <td>{{ $session->campaignSeason->title }}</td>
                                            <td>{{ $session->campaignSeason->department->name ?? 'Not Available' }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $session->status == 'active' ? 'success' : ($session->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($session->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.sessions.edit', $session) }}"><i
                                                                class="dw dw-edit2"></i> Edit</a>
                                                        <a class="dropdown-item delete-session" href="#"
                                                            data-id="{{ $session->id }}" data-name="{{ $session->title }}"
                                                            data-url="{{ route('company_admin.sessions.destroy', $session->id) }}">
                                                            <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                            Delete</a>

                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.sessions.show', $session->id) }}"><i
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
                    </div>
                    <div class="text-center pb-20">
                        <a href="{{ route('company_admin.sessions.index') }}" class="btn btn-primary">
                            <i class="micon dw dw-eye"></i> View All Session
                        </a>
                    </div>
                </div>
            @else
                <div class="page-header">
                    <div class="row">
                        @include('admin.components.page-title', [
                            'page_title' => 'Challenges Created By Users',
                        ])
                    </div>
                </div>
                <div class="card-box mb-30">
                    <div class="pb-20">
                        <div class="table-responsive">
                            <table class="table table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        {{-- Performance Dashboard Section --}}
        @if(isset($allCampaigns) && $allCampaigns->count() > 0)
            @include('company_admin.partials.performance-dashboard')
        @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var barChart = null;
    var donutChart = null;
    var currentPage = 1;
    var statsUrl = '{{ route("company_admin.performance.dashboard-stats") }}';
    var employeeDetailBaseUrl = '{{ route("company_admin.performance.employee-detail", ":userId") }}';

    function loadPerformanceStats(campaignSeasonId, page) {
        page = page || 1;
        currentPage = page;

        $('#performanceLoading').removeClass('d-none');
        $('#performanceContent').addClass('d-none');
        $('#performanceNoData').addClass('d-none');

        $.ajax({
            url: statsUrl,
            type: 'GET',
            data: { campaign_season_id: campaignSeasonId, page: page },
            success: function (data) {
                $('#performanceLoading').addClass('d-none');

                if (data.totalEmployees === 0) {
                    $('#performanceNoData').removeClass('d-none');
                    return;
                }

                $('#performanceContent').removeClass('d-none');
                updateCards(data);
                updateBarChart(data.scoreDistribution);
                updateDonutChart(data.avgQuizScore, data.avgVideoScore);
                updateTable(data.employees, data.pagination, campaignSeasonId);
            },
            error: function () {
                $('#performanceLoading').addClass('d-none');
                $('#performanceNoData').removeClass('d-none');
            }
        });
    }

    function loadEmployeePage(page) {
        var campaignSeasonId = $('#performanceCampaignFilter').val();
        if (!campaignSeasonId) return;

        $.ajax({
            url: statsUrl,
            type: 'GET',
            data: { campaign_season_id: campaignSeasonId, page: page },
            success: function (data) {
                updateTable(data.employees, data.pagination, campaignSeasonId);
            }
        });
    }

    function updateCards(data) {
        $('#statAvgQuiz').text(data.avgQuizScore + '%');
        $('#statAvgVideo').text(data.avgVideoScore + '%');
        $('#statAvgGlobal').text(data.avgGlobalScore + '%');
        $('#statTotalEmployees').text(data.totalEmployees);
    }

    function updateBarChart(distribution) {
        var categories = Object.keys(distribution);
        var values = Object.values(distribution);

        if (barChart) barChart.destroy();

        barChart = new ApexCharts(document.querySelector('#chartScoreDistribution'), {
            chart: { type: 'bar', height: 300 },
            series: [{ name: 'Employees', data: values }],
            xaxis: { categories: categories, title: { text: 'Score Range (%)' } },
            yaxis: { title: { text: 'Number of Employees' } },
            colors: ['#5b73e8'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            dataLabels: { enabled: true },
        });
        barChart.render();
    }

    function updateDonutChart(quizAvg, videoAvg) {
        if (donutChart) donutChart.destroy();

        donutChart = new ApexCharts(document.querySelector('#chartQuizVsVideo'), {
            chart: { type: 'donut', height: 300 },
            series: [quizAvg, videoAvg],
            labels: ['Quiz Avg %', 'Video Avg %'],
            colors: ['#5b73e8', '#34c38f'],
            legend: { position: 'bottom' },
        });
        donutChart.render();
    }

    function updateTable(employees, pagination, campaignSeasonId) {
        var html = '';
        var startIndex = pagination.from || 0;
        $.each(employees, function (index, emp) {
            var detailUrl = employeeDetailBaseUrl.replace(':userId', emp.id) +
                '?campaign_season_id=' + campaignSeasonId;
            html += '<tr>' +
                '<td>' + (startIndex + index) + '</td>' +
                '<td>' + emp.name + '</td>' +
                '<td>' + emp.department + '</td>' +
                '<td>' + emp.job_title + '</td>' +
                '<td>' + emp.quiz_score + '%</td>' +
                '<td>' + emp.video_score + '%</td>' +
                '<td>' + emp.global_score + '%</td>' +
                '<td><a href="' + detailUrl + '" class="btn btn-sm btn-outline-primary">View Details</a></td>' +
                '</tr>';
        });
        $('#employeeTableBody').html(html);
        renderPagination(pagination);
    }

    function renderPagination(pagination) {
        // Info text
        if (pagination.total > 0) {
            $('#paginationInfo').text('Showing ' + pagination.from + ' to ' + pagination.to + ' of ' + pagination.total + ' employees');
        } else {
            $('#paginationInfo').text('');
        }

        // Pagination links
        var links = '';
        if (pagination.last_page > 1) {
            // Previous
            links += '<li class="page-item ' + (pagination.current_page === 1 ? 'disabled' : '') + '">';
            links += '<a class="page-link" href="#" data-page="' + (pagination.current_page - 1) + '">&laquo;</a></li>';

            // Page numbers
            for (var i = 1; i <= pagination.last_page; i++) {
                if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                    links += '<li class="page-item ' + (i === pagination.current_page ? 'active' : '') + '">';
                    links += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                    links += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Next
            links += '<li class="page-item ' + (pagination.current_page === pagination.last_page ? 'disabled' : '') + '">';
            links += '<a class="page-link" href="#" data-page="' + (pagination.current_page + 1) + '">&raquo;</a></li>';
        }
        $('#paginationLinks').html(links);
    }

    // Pagination click handler
    $(document).on('click', '#paginationLinks .page-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
            loadEmployeePage(page);
        }
    });

    // Initial load
    var initialCampaignId = $('#performanceCampaignFilter').val();
    if (initialCampaignId) {
        loadPerformanceStats(initialCampaignId);
    }

    // Campaign filter change
    $('#performanceCampaignFilter').on('change', function () {
        var campaignId = $(this).val();
        if (campaignId) {
            loadPerformanceStats(campaignId, 1);
        }
    });
});
</script>
@endpush
