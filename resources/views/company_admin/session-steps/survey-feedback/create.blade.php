@extends('company_admin.layout.main')

@section('title', 'Add Survey / Feedback')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
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
                        'page_title' => 'Add Survey / Feedback',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.steps.survey-feedback.store') }}" method="POST"
                    enctype="multipart/form-data" id="survey-feedback-form">
                    @csrf
                    <!-- Common Fields -->
                    @include('company_admin.session-steps.survey-feedback._common-fields')
                    <div class="tab-content" id="surveyTabContent">
                        <!-- Custom Survey / Feedback Tab -->
                        <div class="tab-pane fade show active" id="custom" role="tabpanel">
                            <div class="col-12 mt-4">
                                <label class="form-control-label font-weight-bold">Questions</label>
                                <div id="questions-wrapper"></div>
                                <button type="button" class="btn btn-success mt-2" id="add-question">
                                    <i class="fa fa-plus"></i> Add Question
                                </button>
                                <div id="question-error" class="text-danger mt-2 d-none">You can add 10 questions maximum.
                                </div>
                                @error('questions')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div id="generic-error" class="text-danger mt-2 d-none"></div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">
                            <i class="fa fa-save"></i> Create Survey / Feedback
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
                button.innerText = 'Submitting...';
            }
        }
    </script>
    <script src="{{ asset('vendors/scripts/survey-feedback.js') }}"></script>
@endpush
