@extends('admin.layout.main')

@section('title', 'View Quiz')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'View Quiz',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <div class="row">
                <!-- Basic Quiz Info -->
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Title</label>
                    <p class="form-control-plaintext">{{ $quiz->title ?? '—' }}</p>
                </div>
                @if(!is_null($quiz->company_id))
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Company</label>
                    <p class="form-control-plaintext">{{ $quiz->company->name ?? '—' }}</p>
                </div>
                @endif
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold"> @if(!is_null($quiz->company_id)) Campaign @else Season @endif</label>
                    <p class="form-control-plaintext">{{ $quiz->campaignSeason->title ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Session</label>
                    <p class="form-control-plaintext">{{ $quiz->session->title ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Total Points</label>
                    <p class="form-control-plaintext">{{ $quiz->points ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Status</label>
                    <p class="form-control-plaintext text-capitalize">
                        <span
                            class="badge badge-{{ $quiz->status == 'active' ? 'success' : ($quiz->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                            {{ $quiz->status ?? '—' }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Created By</label>
                    <p class="form-control-plaintext">
                        {{ $quiz->createdBy->full_name ?? $quiz->createdByAdmin->name ?? '—' }}
                    </p>
                </div>

                <!-- Questions Section -->
                <div class="col-12 mt-4">
                    <h4 class="mb-3">Questions</h4>
                    <div class="questions-wrapper">
                        @foreach($quiz->questions as $index => $question)
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
                                    @foreach($question->options as $option)
                                    <div
                                        class="option-item mb-2 pl-3 {{ $option->is_correct ? 'border-left border-success' : '' }}">
                                        <div class="d-flex align-items-center">
                                            @if($option->is_correct)
                                            <span class="badge badge-success mr-2">Correct</span>
                                            @endif
                                            <p class="mb-0">{{ $option->option_text }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group mt-3">
                                    <label class="font-weight-bold">Explanation</label>
                                    <p class="form-control-plaintext">{{ $question->explanation ?? 'No explanation provided' }}</p>
                                </div>

                                <div class="mt-2">
                                    <span class="text-muted">Points: {{ $question->points }}</span>
                                </div>

                                @if(!empty($question->explanation))
                                <div class="mt-3 p-3 bg-light rounded border-left border-info" style="border-left-width: 4px !important;">
                                    <label class="font-weight-bold text-info mb-1">
                                        <i class="fa fa-lightbulb-o mr-1"></i> Explanation
                                    </label>
                                    <p class="mb-0">{{ $question->explanation }}</p>
                                </div>
                                @endif
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
