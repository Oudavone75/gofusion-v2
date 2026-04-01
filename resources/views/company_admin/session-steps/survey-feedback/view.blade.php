@extends('company_admin.layout.main')

@section('title', 'View Survey / Feedback')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Survey / Feedback',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <!-- Basic Survey / Feedback Info -->
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">{{ $survey_feedback->company->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Campaign</label>
                        <p class="form-control-plaintext">{{ $survey_feedback->campaignSeason->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Session</label>
                        <p class="form-control-plaintext">{{ $survey_feedback->session->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Total Points</label>
                        <p class="form-control-plaintext">{{ $survey_feedback->points ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext text-capitalize">
                            <span
                                class="badge badge-{{ $survey_feedback->status == 'active' ? 'success' : ($survey_feedback->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                                {{ $survey_feedback->status ?? '—' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Created By</label>
                        <p class="form-control-plaintext">
                            {{ $survey_feedback->createdBy->name ?? ($survey_feedback->createdByAdmin->name ?? '—') }}
                        </p>
                    </div>

                    <!-- Questions Section -->
                    <div class="col-12 mt-4">
                        <h4 class="mb-3">Questions</h4>
                        <div class="questions-wrapper">
                            @foreach ($survey_feedback->questions as $index => $question)
                                <div class="question-container card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Question {{ $index + 1 }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Question Text</label>
                                            <p class="form-control-plaintext">{{ $question->question_text }}</p>
                                        </div>

                                        <div class="options-container">
                                            <label class="font-weight-bold">Options</label>
                                            <ul class="option-list">
                                                @foreach ($question->options as $option)
                                                    <li class="mb-2">
                                                        {{ $option->option_text }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
