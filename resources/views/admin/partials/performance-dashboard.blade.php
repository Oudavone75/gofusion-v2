{{-- Performance Dashboard Section --}}
<div class="page-header">
    <div class="row">
        @include('admin.components.page-title', ['page_title' => 'Performance Overview'])
    </div>
</div>

<div class="card-box mb-30">
    <div class="pd-20">
        {{-- Type & Campaign Filter --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Type</label>
                <select id="performanceTypeFilter" class="form-control">
                    <option value="campaign" selected>Campaign</option>
                    <option value="season">Season</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Select Campaign</label>
                <select id="performanceCampaignFilter" class="form-control selectpicker" data-live-search="true">
                    @foreach ($allCampaigns as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $activeCampaignId ? 'selected' : '' }}>
                            {{ $c->title }}
                            ({{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div id="performanceLoading" class="text-center py-5 d-none">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2 text-muted">Loading performance data...</p>
        </div>

        {{-- No Data Message --}}
        <div id="performanceNoData" class="text-center py-5 d-none">
            <p class="text-muted">No performance data available for the selected campaign.</p>
        </div>

        {{-- Stats Content (hidden until loaded) --}}
        <div id="performanceContent" class="d-none">
            {{-- Score Cards --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-card-primary">
                        <div class="stats-card-body">
                            <div class="stats-header">
                                <span class="stats-title">Avg Quiz Score</span>
                                <div class="stats-icon">
                                    <i class="micon dw dw-open-book"></i>
                                </div>
                            </div>
                            <div class="stats-number" id="statAvgQuiz">0%</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-card-secondary">
                        <div class="stats-card-body">
                            <div class="stats-header">
                                <span class="stats-title">Avg Video Score</span>
                                <div class="stats-icon">
                                    <i class="micon dw dw-video-player"></i>
                                </div>
                            </div>
                            <div class="stats-number" id="statAvgVideo">0%</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-card-quaternary">
                        <div class="stats-card-body">
                            <div class="stats-header">
                                <span class="stats-title">Avg Global Score</span>
                                <div class="stats-icon">
                                    <i class="micon dw dw-analytics-21"></i>
                                </div>
                            </div>
                            <div class="stats-number" id="statAvgGlobal">0%</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-card-tertiary">
                        <div class="stats-card-body">
                            <div class="stats-header">
                                <span class="stats-title">Total Employees</span>
                                <div class="stats-icon">
                                    <i class="micon icon-copy dw dw-user2"></i>
                                </div>
                            </div>
                            <div class="stats-number" id="statTotalEmployees">0</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="row mb-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">Score Distribution</h6>
                            <div id="chartScoreDistribution"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">Quiz vs Video Average</h6>
                            <div id="chartQuizVsVideo"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Employee Performance Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Employee Performance</h6>
                    <div class="table-responsive">
                        <table id="employeePerformanceTable" class="table table-striped table-hover nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Job Title</th>
                                    <th>Quiz Score %</th>
                                    <th>Video Score %</th>
                                    <th>Global Score %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div id="employeePagination" class="d-flex justify-content-between align-items-center mt-3 px-2">
                        <span id="paginationInfo" class="text-muted"></span>
                        <ul id="paginationLinks" class="pagination mb-0"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
