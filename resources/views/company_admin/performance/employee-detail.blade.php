@extends('company_admin.layout.main')

@section('title', 'Employee Performance Detail')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', ['page_title' => 'Employee Performance Detail'])
                </div>
            </div>

            {{-- Back Button + Campaign Filter --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="{{ route('company_admin.dashboard') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('company_admin.performance.employee-detail', $user->id) }}" class="d-flex justify-content-end">
                        <select name="campaign_season_id" class="form-control selectpicker mr-2" data-live-search="true" style="max-width: 400px;" onchange="this.form.submit()">
                            @foreach ($campaigns as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $selectedCampaignId ? 'selected' : '' }}>
                                    {{ $c->title }}
                                    ({{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            {{-- Employee Info Card --}}
            <div class="card-box mb-30">
                <div class="pd-20">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
                            <p class="text-muted mb-1">
                                <strong>Department:</strong> {{ $user->department?->name ?? 'N/A' }}
                            </p>
                            <p class="text-muted mb-1">
                                <strong>Job Title:</strong> {{ $user->job_title ?? 'N/A' }}
                            </p>
                            <p class="text-muted mb-0">
                                <strong>Campaign:</strong> {{ $campaign?->title ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            @if($performance)
                                <div class="d-inline-block text-center mx-3">
                                    <h5 class="text-primary mb-0">{{ $performance['quiz_score'] }}%</h5>
                                    <small class="text-muted">Quiz Score</small>
                                </div>
                                <div class="d-inline-block text-center mx-3">
                                    <h5 class="text-info mb-0">{{ $performance['video_score'] }}%</h5>
                                    <small class="text-muted">Video Score</small>
                                </div>
                                <div class="d-inline-block text-center mx-3">
                                    <h5 class="text-success mb-0">{{ $performance['global_score'] }}%</h5>
                                    <small class="text-muted">Global Score</small>
                                </div>
                            @else
                                <span class="text-muted">No performance data available</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($performance)
            {{-- Quiz Performance Section --}}
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h5 class="mb-3">Quiz Performance</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Quiz Score</h6>
                                    <h4 class="text-primary mb-0">{{ $performance['quiz_score'] }}%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Points Earned</h6>
                                    <h4 class="mb-0">{{ $performance['quiz_earned'] }} / {{ $performance['quiz_total'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Quizzes Taken</h6>
                                    <h4 class="mb-0">{{ $quizAttempts->total() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($quizAttempts->total() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Quiz Title</th>
                                        <th>Session</th>
                                        <th>Score</th>
                                        <th>Total</th>
                                        <th>Percentage</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizAttempts as $index => $attempt)
                                        <tr>
                                            <td>{{ $quizAttempts->firstItem() + $index }}</td>
                                            <td>{{ $attempt->quiz?->title ?? 'N/A' }}</td>
                                            <td>{{ $attempt->quiz?->session?->title ?? 'N/A' }}</td>
                                            <td>{{ $attempt->points ?? 0 }}</td>
                                            <td>{{ $attempt->total_points ?? 0 }}</td>
                                            <td>{{ round($attempt->percentage ?? 0, 2) }}%</td>
                                            <td>{{ $attempt->created_at?->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">Showing {{ $quizAttempts->firstItem() }} to {{ $quizAttempts->lastItem() }} of {{ $quizAttempts->total() }} entries</span>
                            {{ $quizAttempts->appends(request()->except('quiz_page'))->links() }}
                        </div>
                    @else
                        <p class="text-muted text-center">No quiz attempts found.</p>
                    @endif
                </div>
            </div>

            {{-- Video Performance Section --}}
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h5 class="mb-3">Video Performance</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Video Score</h6>
                                    <h4 class="text-info mb-0">{{ $performance['video_score'] }}%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Points Earned</h6>
                                    <h4 class="mb-0">{{ $performance['video_earned'] }} / {{ $performance['video_total'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0 bg-light">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1">Videos Completed</h6>
                                    <h4 class="mb-0">{{ $videoSubmissions->total() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($videoSubmissions->total() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Video</th>
                                        <th>Session</th>
                                        <th>Comment</th>
                                        <th>Matched Concepts</th>
                                        <th>Score %</th>
                                        <th>Points</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($videoSubmissions as $index => $video)
                                        @php
                                            $guideline = $video->goSessionStep?->imageSubmissionGuideline;
                                            $session = $video->goSessionStep?->goSession;
                                            $matchedConcepts = is_array($video->matched_concepts) ? implode(', ', $video->matched_concepts) : 'N/A';
                                        @endphp
                                        <tr>
                                            <td>{{ $videoSubmissions->firstItem() + $index }}</td>
                                            <td>{{ $guideline?->title ?? $guideline?->video_url ?? 'N/A' }}</td>
                                            <td>{{ $session?->title ?? 'N/A' }}</td>
                                            <td title="{{ $video->comment ?? '' }}">{{ Str::limit($video->comment ?? 'N/A', 50) }}</td>
                                            <td>{{ $matchedConcepts }}</td>
                                            <td>{{ round($video->percentage ?? 0, 2) }}%</td>
                                            <td>{{ $video->points ?? 0 }}</td>
                                            <td>{{ $video->total_points ?? 0 }}</td>
                                            <td>{{ $video->created_at?->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">Showing {{ $videoSubmissions->firstItem() }} to {{ $videoSubmissions->lastItem() }} of {{ $videoSubmissions->total() }} entries</span>
                            {{ $videoSubmissions->appends(request()->except('video_page'))->links() }}
                        </div>
                    @else
                        <p class="text-muted text-center">No video submissions found.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
