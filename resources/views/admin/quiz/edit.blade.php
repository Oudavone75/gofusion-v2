@extends('admin.layout.main')

@section('title', 'Edit Quiz')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Quiz',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.quiz.update', $quiz->id) }}" method="POST" enctype="multipart/form-data" id="quiz-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="quiz_type" id="quiz-type-input" value="{{ $quiz->quiz_type }}">
                <!-- Common Fields -->
                @if(session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
                @endif
                @include('admin.quiz._common-fields', ['quiz' => $quiz])
                <!-- Quiz Type Selection -->
                <ul class="nav nav-tabs mt-3 mb-3" id="quizTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $quiz->quiz_type === 'custom' ? 'active' : '' }}" id="custom-tab" data-toggle="tab" href="#custom" role="tab">
                            <i class="fa fa-edit"></i> Custom Questions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $quiz->quiz_type === 'ai' ? 'active' : '' }}" id="ai-tab" data-toggle="tab" href="#ai" role="tab">
                            <i class="fa fa-magic"></i> AI Generated Questions
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="quizTabContent">
                    <!-- Custom Quiz Tab -->
                    <div class="tab-pane fade {{ $quiz->quiz_type === 'custom' ? 'show active' : '' }}" id="custom" role="tabpanel">
                        <div class="col-12 mt-4">
                            <label class="form-control-label font-weight-bold">Questions</label>
                            <div id="questions-wrapper">
                                @if($quiz->quiz_type === 'custom')
                                @foreach($quiz->questions as $index => $question)
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
                                            <textarea name="questions[{{ $index }}][text]" class="form-control">{{ $question->question_text }}</textarea>
                                        </div>

                                        <div class="options-container">
                                            @foreach($question->options as $optIndex => $option)
                                            <div class="option-item mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <input type="radio" name="questions[{{ $index }}][correct]" value="{{ $optIndex }}" {{ $option->is_correct ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="questions[{{ $index }}][options][{{ $optIndex }}]" class="form-control" value="{{ $option->option_text }}">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-danger remove-option" type="button">
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

                                        <div class="form-group mt-3">
                                            <label>Explanation (Pedagogical feedback for the correct answer)</label>
                                            <textarea name="questions[{{ $index }}][explanation]" class="form-control" rows="3" placeholder="Explain why the correct answer is right...">{{ $question->explanation ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-success mt-2" id="add-question">
                                <i class="fa fa-plus"></i> Add Question
                            </button>
                            <div id="question-error" class="text-danger mt-2 d-none">You can add 10 questions maximum.</div>
                            @error('questions')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- AI Quiz Tab -->
                    <div class="tab-pane fade {{ $quiz->quiz_type === 'ai' ? 'show active' : '' }}" id="ai" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Theme*</label>
                                <select name="theme_id" class="custom-select2 form-control" style="width: 100%;">
                                    <option value="" disabled selected>Select Theme</option>
                                    @foreach ($themes as $theme)
                                    <option value="{{ $theme->id }}" {{ old('theme', $quiz->theme_id) == $theme->id ? 'selected' : '' }}>
                                        {{ $theme->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="language" value="FR">
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Difficulty*</label>
                                <select name="difficulty" class="form-control">
                                    <option value="easy" {{ (old('difficulty', $quiz->difficulty ?? '') === 'easy') ? 'selected' : '' }}>Easy</option>
                                    <option value="medium" {{ (old('difficulty', $quiz->difficulty ?? 'medium') === 'medium') ? 'selected' : '' }}>Medium</option>
                                    <option value="hard" {{ (old('difficulty', $quiz->difficulty ?? '') === 'hard') ? 'selected' : '' }}>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Number of Questions*</label>
                                <input type="number" name="num_questions" min="1" max="10" value="{{ old('num_questions', $quiz->num_questions ?? 5) }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Options per Question*</label>
                                <input type="number" name="num_options" min="2" max="5" value="{{ old('num_options', $quiz->num_options ?? 4) }}" class="form-control">
                            </div>

                            <div class="col-md-12 col-sm-12">
                                <label class="form-control-label">AI Instructions*</label>
                                <textarea name="ai_rules" class="form-control" rows="4" placeholder="Tell AI exactly what kind of quiz to create (e.g. 'Create a 5-question quiz about climate change with medium difficulty. Focus on renewable energy solutions.')">{{ old('ai_rules', $quiz->ai_rules ?? '') }}</textarea>
                                <small class="form-text text-muted">
                                    Be specific about topic, difficulty, question style, and any special requirements
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" id="generate-ai-quiz" class="btn btn-info">
                                    <i class="fa fa-magic"></i> Regenerate Questions
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="form-control-label font-weight-bold">Generated Questions</label>
                            <div id="ai-questions-wrapper" class="bg-light p-3 rounded">
                                @if($quiz->quiz_type === 'ai')
                                @foreach($quiz->questions as $index => $question)
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
                                            <textarea name="ai_questions[{{ $index }}][text]" class="form-control">{{ $question->question_text }}</textarea>
                                        </div>

                                        <div class="options-container">
                                            @foreach($question->options as $optIndex => $option)
                                            <div class="option-item mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <input type="radio" name="ai_questions[{{ $index }}][correct]" value="{{ $optIndex }}" {{ $option->is_correct ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="ai_questions[{{ $index }}][options][{{ $optIndex }}]" class="form-control" value="{{ $option->option_text }}">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-danger remove-option" type="button">
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

                                        <div class="form-group mt-3">
                                            <label>Explanation (Pedagogical feedback for the correct answer)</label>
                                            <textarea name="ai_questions[{{ $index }}][explanation]" class="form-control" rows="3" placeholder="Explain why the correct answer is right...">{{ $question->explanation ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="text-muted text-center">Questions will appear here after generation</p>
                                @endif
                            </div>
                            <div id="ai-error" class="text-danger mt-2 d-none"></div>
                        </div>
                    </div>
                </div>
                <div id="generic-error" class="text-danger mt-2 d-none"></div>
                <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                    <button type="submit" id="submit-button" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Quiz
                    </button>
                </div>
            </form>
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
<script src="{{ asset('vendors/scripts/quiz.js') }}"></script>
@endpush
