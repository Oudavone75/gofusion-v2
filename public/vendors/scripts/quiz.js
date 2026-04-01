$(document).ready(function () {
    // ==============================================
    // SHARED VARIABLES AND CONFIGURATIONS
    // ==============================================
    let questionCount = $('#questions-wrapper .question-container').length;
    const maxQuestions = 10;
    const maxOptions = 5;
    const minOptions = 4;

    // ==============================================
    // SHARED UTILITY FUNCTIONS
    // ==============================================

    // Show/hide error messages
    function showError(container, message) {
        container.find('.option-error').text(message).removeClass('d-none');
    }

    function hideError(container) {
        container.find('.option-error').addClass('d-none');
    }

    // Generate HTML for a single option
    function createOptionHTML(questionId, optionIndex, optionText = '', isCorrect = false, namePrefix = 'questions') {
        const checkedAttr = isCorrect ? 'checked' : '';
        const placeholder = optionText || `Option ${optionIndex + 1}`;

        return `
        <div class="option-item mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <input type="radio" name="${namePrefix}[${questionId}][correct]" value="${optionIndex}" ${checkedAttr}>
                    </div>
                </div>
                <input type="text" name="${namePrefix}[${questionId}][options][${optionIndex}]" class="form-control" placeholder="${placeholder}" value="${optionText}">
                <div class="input-group-append">
                    <button class="btn btn-outline-danger remove-option" type="button">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    // Generate HTML for a complete question
    function createQuestionHTML(questionId, questionText = '', options = [], namePrefix = 'questions', explanation = '') {
        const displayNumber = questionId + 1;
        let optionsHTML = '';

        // If no options provided, create default 2 options
        if (options.length === 0) {
            optionsHTML = createOptionHTML(questionId, 0, '', false, namePrefix);
            optionsHTML += createOptionHTML(questionId, 1, '', false, namePrefix);
            optionsHTML += createOptionHTML(questionId, 2, '', false, namePrefix);
            optionsHTML += createOptionHTML(questionId, 3, '', false, namePrefix);
        } else {
            // Create options from provided data
            options.forEach((option, index) => {
                const text = option.text || option;
                const isCorrect = option.is_correct || false;
                optionsHTML += createOptionHTML(questionId, index, text, isCorrect, namePrefix);
            });
        }

        return `
        <div class="question-container card mt-3" data-question-id="${questionId}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Question ${displayNumber}</h5>
                <button type="button" class="btn btn-danger btn-sm remove-question">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="${namePrefix}[${questionId}][text]" class="form-control">${questionText}</textarea>
                </div>

                <div class="options-container">
                    ${optionsHTML}
                </div>

                <button type="button" class="btn btn-sm btn-secondary add-option mt-2">
                    <i class="fa fa-plus"></i> Add Option
                </button>
                <div class="option-error text-danger mt-2 d-none"></div>

                <div class="form-group mt-3">
                    <label>Explanation (Pedagogical feedback for the correct answer)</label>
                    <textarea name="${namePrefix}[${questionId}][explanation]" class="form-control" rows="3" placeholder="Explain why the correct answer is right...">${explanation}</textarea>
                </div>
            </div>
        </div>`;
    }

    // Add option to any question
    function addOptionToQuestion(questionContainer, namePrefix = 'questions') {
        const questionId = questionContainer.data('question-id');
        const optionsCount = questionContainer.find('.option-item').length;

        if (optionsCount >= maxOptions) {
            showError(questionContainer, `Maximum ${maxOptions} options per question`);
            return;
        }

        hideError(questionContainer);
        const newOptionHTML = createOptionHTML(questionId, optionsCount, '', false, namePrefix);
        questionContainer.find('.options-container').append(newOptionHTML);
        // If this is the first option being added, make it correct by default
        if (optionsCount === 0) {
            questionContainer.find(`input[type="radio"]`).first().prop('checked', true);
        }
    }

    // Remove option from any question
    function removeOptionFromQuestion(optionItem) {
        const questionContainer = optionItem.closest('.question-container');
        const optionsCount = questionContainer.find('.option-item').length;

        if (optionsCount <= minOptions) {
            showError(questionContainer, `Each question must have at least ${minOptions} options`);
            return;
        }

        hideError(questionContainer);
        const optionIndex = optionItem.index();
        optionItem.remove();
        updateOptionNumbers(questionContainer);
        const correctRadio = questionContainer.find(`input[type="radio"]:checked`);
        if (!correctRadio.length) {
            questionContainer.find(`input[type="radio"]`).first().prop('checked', true);
        }
    }

    // Update option numbers after removal
    function updateOptionNumbers(questionContainer) {
        const questionId = questionContainer.data('question-id');
        const namePrefix = questionContainer.closest('.tab-pane').is('#ai') ? 'ai_questions' : 'questions';

        questionContainer.find('.option-item').each(function (index) {
            const optionItem = $(this);

            // Update radio button
            const radioInput = optionItem.find('input[type="radio"]');
            radioInput.val(index);

            // Update text input name
            const textInput = optionItem.find('input[type="text"]');
            textInput.attr('name', `${namePrefix}[${questionId}][options][${index}]`);

            // Update the radio button name if it's the correct answer
            if (radioInput.is(':checked')) {
                radioInput.closest('.input-group').find(`input[name="${namePrefix}[${questionId}][correct]"]`).val(index);
            }
        });
    }

    // Update question numbers after removal
    function updateQuestionNumbers(wrapperSelector, namePrefix = 'questions') {
        $(wrapperSelector + ' .question-container').each(function (index) {
            const container = $(this);
            container.data('question-id', index);
            container.find('h5').text(`Question ${index + 1}`);

            // Update all input names
            container.find(`[name^="${namePrefix}["]`).each(function () {
                const currentName = $(this).attr('name');
                const newName = currentName.replace(new RegExp(`${namePrefix}\\[\\d+\\]`), `${namePrefix}[${index}]`);
                $(this).attr('name', newName);
            });
        });
    }

    // ==============================================
    // TAB SWITCHING AND FORM VALIDATION
    // ==============================================
    $('#quizTab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');

        if (target === '#custom') {
            $('#quiz-type-input').val('custom');
            $('#ai [required]').removeAttr('required');
            $('#custom [data-required]').attr('required', 'required');
        } else if (target === '#ai') {
            $('#quiz-type-input').val('ai');
            $('#ai [data-required]').attr('required', 'required');
            $('#custom [required]').removeAttr('required');
        }
    });

    $('#ai [data-required]').removeAttr('required');


    // Validation error display function
    function displayValidationErrors(errors) {
        // Clear previous error highlights
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Process each error
        for (const field in errors) {
            if (errors.hasOwnProperty(field)) {
                const errorMessage = errors[field][0]; // Get first error message

                // Handle special cases for questions and options
                if (field.startsWith('questions.') || field.startsWith('ai_questions.')) {
                    const parts = field.split('.');
                    const questionIndex = parts[1];
                    const fieldType = parts[2];
                    if (fieldType === 'text') {
                        // Question text error
                        const textarea = $(`[name="${parts[0]}[${questionIndex}][text]"]`);
                        textarea.addClass('is-invalid');
                        textarea.after(`<div class="invalid-feedback">Please enter a question text.</div>`);
                    } else if (fieldType === 'correct') {
                        // Correct option error
                        const questionContainer = $(`[name="${parts[0]}[${questionIndex}][text]"]`).closest('.question-container');
                        questionContainer.find('.option-error').text('Please select a correct option').removeClass('d-none');
                    } else if (fieldType === 'options') {
                        // Option text error
                        const optionIndex = parts[3];
                        const input = $(`[name="${parts[0]}[${questionIndex}][options][${optionIndex}]"]`);
                        input.addClass('is-invalid');
                    }
                } else {
                    // Regular field error
                    const input = $(`[name="${field}"]`);
                    input.addClass('is-invalid');
                    input.after(`<div class="invalid-feedback">${errorMessage}</div>`);
                }
            }
        }

        // Scroll to first error
        setTimeout(() => {
            const firstError = $('.is-invalid').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }, 100);
    }

    // ==============================================
    // FORM SUBMISSION
    // ==============================================
    $('#quiz-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        const submitButton = $('#submit-button');
        const isCustomQuiz = $('.nav-tabs .active').attr('id') === 'custom-tab';
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#generic-error').addClass('d-none');

        // Count questions based on quiz type
        const questionCount = isCustomQuiz
            ? $('#questions-wrapper .question-container').length
            : $('#ai-questions-wrapper .question-container').length;

        if (questionCount < 2) {
            $('#generic-error').text('Please add at least two questions').removeClass('d-none');
            return;
        }
        submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $(isCustomQuiz ? '#ai' : '#custom')
            .find('input, select, textarea')
            .prop('disabled', true);

        $('#quiz-type-input').val(isCustomQuiz ? 'custom' : 'ai');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            success: function (response) {
                suceessMessage(response?.message || 'Submitted successfully!');
                if (response?.redirect) {
                    setTimeout(() => {
                        window.location.href = response?.redirect;
                    }, 1000);
                }

            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html('<i class="fa fa-save"></i> Create Quiz');
                $(isCustomQuiz ? '#ai' : '#custom')
                    .find('input, select, textarea')
                    .prop('disabled', false);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON;
                    displayValidationErrors(errors);
                } else {
                    const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    errorMessage(message);
                }
            }
        });
    });

    // ==============================================
    // CUSTOM QUIZ FUNCTIONALITY
    // ==============================================
    $('#add-question').click(function () {
        if (questionCount >= maxQuestions) {
            $('#question-error').removeClass('d-none');
            return;
        }

        const newQuestionHTML = createQuestionHTML(questionCount, '', [], 'questions');
        $('#questions-wrapper').append(newQuestionHTML);
        questionCount++;
        $('#question-error').addClass('d-none');
    });

    // Remove custom question
    $(document).on('click', '#questions-wrapper .remove-question', function () {
        $(this).closest('.question-container').remove();
        questionCount--;
        updateQuestionNumbers('#questions-wrapper', 'questions');
        $('#question-error').addClass('d-none');
    });

    // Add option to custom question
    $(document).on('click', '#questions-wrapper .add-option', function () {
        const questionContainer = $(this).closest('.question-container');
        addOptionToQuestion(questionContainer, 'questions');
    });

    // Remove option from custom question
    $(document).on('click', '#questions-wrapper .remove-option', function () {
        const optionItem = $(this).closest('.option-item');
        removeOptionFromQuestion(optionItem);
    });

    // ==============================================
    // AI QUIZ FUNCTIONALITY
    // ==============================================
    $('#generate-ai-quiz').click(function () {
        const form = $(this).closest('form');
        const theme = form.find('[name="theme_id"]').val();
        const language = form.find('[name="language"]').val();
        const aiRules = form.find('[name="ai_rules"]').val();
        const numOptions = form.find('[name="num_options"]').val();
        const numQuestions = form.find('[name="num_questions"]').val();
        const difficulty = form.find('[name="difficulty"]').val();
        // Validate required fields
        if (!theme || !aiRules || !numOptions || !numQuestions || !difficulty) {
            $('#ai-error').text('Please fill all required fields to generate quiz').removeClass('d-none');
            return;
        }
        if (numQuestions < 2) {
            $('#ai-error').text('Number of questions must be at least 2').removeClass('d-none');
            return;
        }

        if (numQuestions > 10) {
            $('#ai-error').text('Number of questions cannot exceed 10').removeClass('d-none');
            return;
        }

        if (numOptions < 4) {
            $('#ai-error').text('Number of options must be at least 4').removeClass('d-none');
            return;
        }

        if (numOptions > 5) {
            $('#ai-error').text('Number of options cannot exceed 5').removeClass('d-none');
            return;
        }
        // Show loading
        $('#ai-loading').removeClass('d-none');
        $('#ai-error').addClass('d-none');
        $('#ai-questions-wrapper').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        $('#generate-ai-quiz').prop('disabled', true);

        //==============================================
        // REAL API IMPLEMENTATION (COMMENTED OUT FOR NOW)
        // ==============================================
        const apiUrl = 'https://gofusion-python.6lgx.com/generate-quiz';
        const authToken = 'f95ae7bc-f273-4e0b-91d7-6a1e3f9057f1';

        $.ajax({
            url: apiUrl,
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                theme: theme,
                language: language,
                description: aiRules,
                num_options: numOptions,
                num_questions: numQuestions,
                difficulty: difficulty
            }),
            success: function (response) {
                $('#ai-loading').addClass('d-none');

                if (response.success && response.questions && response.questions.length > 0) {
                    renderAIQuestions(response.questions);
                    $('#generate-ai-quiz').prop('disabled', false);
                    $('#generate-ai-quiz').text('Regenerate');
                } else {
                    $('#ai-error').text(response.error || 'Failed to generate quiz questions').removeClass('d-none');
                }
            },
            error: function (xhr) {
                $('#ai-loading').addClass('d-none');
                $('#generate-ai-quiz').prop('disabled', false);
                $('#ai-questions-wrapper').empty();
                let errorMsg = 'Error generating quiz';

                if (xhr.status === 401) {
                    errorMsg = 'Authentication failed - please check your API token';
                } else if (xhr.responseJSON?.status === 'error') {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMsg += ': ' + xhr.statusText;
                }

                $('#ai-error').text(errorMsg).removeClass('d-none');
            }
        });

    });

    // Render AI questions using shared function
    function renderAIQuestions(questions) {
        let html = '';

        questions.forEach((question, index) => {
            html += createQuestionHTML(index, question.question, question.options, 'ai_questions', question.explanation || '');
        });

        $('#ai-questions-wrapper').html(html);
    }

    // Remove AI question
    $(document).on('click', '#ai-questions-wrapper .remove-question', function () {
        $(this).closest('.question-container').remove();
        updateQuestionNumbers('#ai-questions-wrapper', 'ai_questions');
    });

    // Add option to AI question
    $(document).on('click', '#ai-questions-wrapper .add-option', function () {
        const questionContainer = $(this).closest('.question-container');
        addOptionToQuestion(questionContainer, 'ai_questions');
    });

    // Remove option from AI question
    $(document).on('click', '#ai-questions-wrapper .remove-option', function () {
        const optionItem = $(this).closest('.option-item');
        removeOptionFromQuestion(optionItem);
    });

    // Real-time validation for radio buttons
    $(document).on('change', 'input[type="radio"]', function () {
        const questionContainer = $(this).closest('.question-container');
        hideError(questionContainer);
    });

    // Clear generic error when user starts typing
    $(document).on('input', 'input, textarea', function () {
        $('#generic-error').addClass('d-none');
    });


    function makeQuizQuestions(questions) {
        let html = '';

        questions.forEach((q, index) => {
            const questionId = Date.now() + '_' + index;
            const displayNumber = index + 1;//q.number || (index + 1);
            const questionText = q.question;
            const namePrefix = 'questions';

            let optionsHTML = '';

            for (let i = 1; i <= 5; i++) {
                const optionKey = `option ${i}`;
                const optionText = q[optionKey] || '';
                const optionValue = optionText;
                const isCorrect = q.correct === optionValue;
                const checkedAttr = isCorrect ? 'checked' : '';
                const placeholder = `Option ${i}`;
                const optionIndex = i - 1;

                if (optionText != '') {
                    optionsHTML += `
                    <div class="option-item mb-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" name="${namePrefix}[${questionId}][correct]" value="${optionIndex}" ${checkedAttr}>
                            </div>
                        </div>
                        <input type="text" name="${namePrefix}[${questionId}][options][${optionIndex}]" class="form-control" placeholder="${placeholder}" value="${optionText}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-danger remove-option" type="button">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
                }
            }

            html += `
        <div class="question-container card mt-3" data-question-id="${questionId}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Question ${displayNumber}</h5>
                <button type="button" class="btn btn-danger btn-sm remove-question">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="${namePrefix}[${questionId}][text]" class="form-control">${questionText}</textarea>
                </div>
                <div class="options-container">
                    ${optionsHTML}
                </div>
                <div class="option-error text-danger mt-2 d-none"></div>
                <div class="form-group mt-3">
                    <label>Explanation (Pedagogical feedback for the correct answer)</label>
                    <textarea name="${namePrefix}[${questionId}][explanation]" class="form-control" rows="3" placeholder="Explain why the correct answer is right...">${q.explanation || ''}</textarea>
                </div>
            </div>
        </div>`;
        });

        return html;
    }

    $('input[name="num_questions"], input[name="from"], input[name="to"]').on('input', function () {
        validateImportInputs();
    });

    $('#import-file-quiz').on('click', function () {

        if (validateImportInputs()) {
            $('#import-file-quiz').prop('disabled', true);
            $('#import-questions-wrapper').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

            let importUrl = $(this).data('url');
            let formData = new FormData();

            // Collect input field values
            formData.append('num_questions', $('#import_num_questions').val());
            formData.append('from', $('[name="from"]').val());
            formData.append('to', $('[name="to"]').val());

            let fileInput = $('.form-control[type="file"]')[0];
            if (fileInput && fileInput.files.length > 0) {
                formData.append('file', fileInput.files[0]);
            }

            $.ajax({
                url: importUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    let questions = response.data;
                    let questionsHTML = makeQuizQuestions(questions);
                    $('#import-questions-wrapper').html(questionsHTML);
                    $('#import-file-quiz').prop('disabled', false);
                },
                error: function (xhr) {
                    $('#import-file-quiz').prop('disabled', false);
                    $('#import-questions-wrapper').html('<p class="text-danger">Import failed. Check input or try again.</p>');
                }
            });
        }
    });

    function validateImportInputs() {
        let isValid = true;

        // Clear all previous error states
        $('input[name="num_questions"], input[name="from"], input[name="to"]').each(function () {
            $(this).removeClass('form-control-danger')
                .parent().removeClass('has-danger');
            $(this).next('.form-control-feedback').text('').addClass('d-none');
        });

        const numQuestionsEl = $('#import_num_questions');
        const fromEl = $('input[name="from"]');
        const toEl = $('input[name="to"]');

        const numQuestions = parseInt(numQuestionsEl.val(), 10);
        const from = parseInt(fromEl.val(), 10);
        const to = parseInt(toEl.val(), 10);

        const showError = (el, message) => {
            el.addClass('form-control-danger');
            el.parent().addClass('has-danger');
            el.next('.form-control-feedback').text(message).removeClass('d-none');
            isValid = false;
        };

        if (isNaN(numQuestions) || numQuestions < 2) {
            showError(numQuestionsEl, 'Number of questions must be at least 2 to be imported');
        } else if (numQuestions > 10) {
            showError(numQuestionsEl, 'Number of questions must be less than or equal to 10');
        }

        if (isNaN(from)) {
            showError(fromEl, 'Start number (from) is required');
        }
        if (isNaN(to)) {
            showError(toEl, 'End number (to) is required');
        }

        // Proceed only if from and to are valid numbers
        if (!isNaN(from) && !isNaN(to)) {
            if (from >= to) {
                showError(fromEl, '"From" should be less than "To"');
                showError(toEl, '"To" should be greater than "From"');
            }

            if (from < to && to > from) {
                const sum = to - from + 1;
                if (sum !== numQuestions) {
                    const message = `The range from ${from} to ${to} covers ${sum} question(s), which must equal the number of questions (${numQuestions})`;
                    showError(fromEl, message);
                    showError(toEl, message);
                }
            }

        }
        return isValid;
    }
});
