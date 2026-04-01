@extends('company_admin.layout.main')

@section('title', 'Add Quiz')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        @if(session('error'))
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
                'page_title' => 'Add Quiz',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('company_admin.steps.quiz.store') }}" method="POST" enctype="multipart/form-data" id="quiz-form">
                @csrf
                <input type="hidden" name="quiz_type" id="quiz-type-input" value="custom">
                <!-- Common Fields -->
                @include('company_admin.session-steps.quiz._common-fields')
                <!-- Quiz Type Selection -->
                <ul class="nav nav-tabs mt-3 mb-3" id="quizTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tab" data-toggle="tab" href="#custom" role="tab">
                            <i class="fa fa-edit"></i> Custom Questions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ai-tab" data-toggle="tab" href="#ai" role="tab">
                            <i class="fa fa-magic"></i> AI Generated Questions
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="quizTabContent">
                    <!-- Custom Quiz Tab -->
                    <div class="tab-pane fade show active" id="custom" role="tabpanel">
                        <div class="col-12 mt-4">
                            <label class="form-control-label font-weight-bold">Questions</label>
                            <div id="questions-wrapper"></div>
                            <button type="button" class="btn btn-success mt-2" id="add-question">
                                <i class="fa fa-plus"></i> Add Question
                            </button>
                            <div id="question-error" class="text-danger mt-2 d-none">You can add 10 questions maximum.
                            </div>
                        </div>
                    </div>
                    <!-- AI Quiz Tab -->
                    <div class="tab-pane fade" id="ai" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Theme*</label>
                                <select name="theme_id" class="custom-select2 form-control" style="width: 100%;">
                                    <option value="" disabled selected>Select Theme</option>
                                    @foreach($themes as $theme)
                                    <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="language" value="FR">
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Difficulty*</label>
                                <select name="difficulty" class="custom-select2 form-control" style="width: 100%;">
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Number of Questions*</label>
                                <input type="number" name="num_questions" value="5"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Options per Question*</label>
                                <input type="number" name="num_options" min="2" max="5" value="4" class="form-control">
                            </div>

                            <div class="col-md-12 col-sm-12">
                                <label class="form-control-label">AI Instructions*</label>
                                <textarea name="ai_rules" class="form-control" rows="4"
                                    placeholder="Tell AI exactly what kind of quiz to create (e.g. 'Create a 5-question quiz about climate change with medium difficulty. Focus on renewable energy solutions.')">{{ old('ai_rules') }}</textarea>
                                <small class="form-text text-muted">
                                    Be specific about topic, difficulty, question style, and any special
                                    requirements
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" id="generate-ai-quiz" class="btn btn-info">
                                    <i class="fa fa-magic"></i> Generate Questions
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="form-control-label font-weight-bold">Generated Questions</label>
                            <div id="ai-questions-wrapper" class="bg-light p-3 rounded">
                                <p class="text-muted text-center">Questions will appear here after generation</p>
                            </div>
                            <div id="ai-error" class="text-danger mt-2 d-none"></div>
                        </div>
                    </div>
                </div>
                <div id="generic-error" class="text-danger mt-2 d-none"></div>
                <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                    <button type="submit" id="submit-button" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('vendors/scripts/quiz.js') }}"></script>
@endpush
