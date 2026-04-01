@extends('company_admin.layout.main')

@section('title', 'Edit Survey Feedback')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <!-- Error Messages -->
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
                        'page_title' => 'Edit Survey Feedback',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                @if ($survey_feedback->attempts_count > 0)
                    <div class="alert alert-warning" role="alert">
                        <i class="fa fa-exclamation-triangle"></i> This survey_feedback has
                        {{ $survey_feedback->attempts_count }} attempt(s) and cannot be edited.
                    </div>
                @else
                    <form action="{{ route('company_admin.steps.survey-feedback.update', $survey_feedback->id) }}"
                        method="POST" enctype="multipart/form-data" id="survey-feedback-form">
                        @csrf
                        @method('PUT')

                        <!-- Survey Feedback Information (Read-only) -->
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Campaign*</label>
                                <input type="text" class="form-control"
                                    value="{{ $survey_feedback->campaignSeason->title ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Session*</label>
                                <input type="text" class="form-control"
                                    value="{{ $survey_feedback->session->title ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Total Points (Out of 300)*</label>
                                <input type="number" name="points" value="{{ old('points', $survey_feedback->points) }}"
                                    class="form-control"
                                    placeholder="Enter total points (1-300)">
                                <div class="form-control-feedback d-none"></div>
                            </div>
                        </div>

                        <!-- Questions Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <label class="form-control-label font-weight-bold">Questions</label>
                                <div id="questions-wrapper">
                                    @foreach ($survey_feedback->questions as $index => $question)
                                        <div class="question-container card mt-3" data-question-id="{{ $index }}">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">Question {{ $index + 1 }}</h5>
                                                <button type="button" class="btn btn-danger btn-sm remove-question">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Question Text</label>
                                                    <textarea name="questions[{{ $index }}][text]" class="form-control">{{ old("questions.{$index}.text", $question->question_text) }}</textarea>
                                                </div>

                                                <div class="options-container">
                                                    @foreach ($question->options as $optionIndex => $option)
                                                        <div class="option-item mb-2">
                                                            <div class="input-group">
                                                                <input type="text"
                                                                    name="questions[{{ $index }}][options][{{ $optionIndex }}]"
                                                                    class="form-control"
                                                                    placeholder="Option {{ $optionIndex + 1 }}"
                                                                    value="{{ old("questions.{$index}.options.{$optionIndex}", $option->option_text) }}"
                                                                >
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-outline-danger remove-option"
                                                                        type="button">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <button type="button" class="btn btn-sm btn-secondary add-option mt-2">
                                                    <i class="fa fa-plus"></i> Add Option
                                                </button>
                                                <div class="option-error text-danger mt-2 d-none"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-success mt-2" id="add-question">
                                    <i class="fa fa-plus"></i> Add Question
                                </button>
                                <div id="question-error" class="text-danger mt-2 d-none">You can add 10 questions maximum.
                                </div>
                                <div class="form-control-feedback d-none"></div>
                            </div>
                        </div>

                        <div id="generic-error" class="text-danger mt-2 d-none"></div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Survey Feedback
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function disableSubmitButton(form) {
            const button = form.querySelector('#submit-button');
            if (button) {
                button.disabled = true;
                button.innerText = 'Updating...';
            }
        }

        // Initialize question count based on existing questions
        document.addEventListener('DOMContentLoaded', function() {
            const questionCount = document.querySelectorAll('.question-container').length;
            window.questionCount = questionCount || 0;
        });
    </script>
    <script src="{{ asset('vendors/scripts/survey-feedback.js') }}"></script>
@endpush
