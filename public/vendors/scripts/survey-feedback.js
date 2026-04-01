$(document).ready(function () {
    // ==============================================
    // SHARED VARIABLES AND CONFIGURATIONS
    // ==============================================
    let questionCount = $('#questions-wrapper .question-container').length;
    const maxQuestions = 10;
    const maxOptions = 5;
    const minOptions = 2;

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
    function createOptionHTML(questionId, optionIndex, optionText = '', namePrefix = 'questions') {
        const placeholder = optionText || `Option ${optionIndex + 1}`;

        return `
        <div class="option-item mb-2">
            <div class="input-group">
                <input type="text" name="${namePrefix}[${questionId}][options][${optionIndex}]" class="form-control" placeholder="${placeholder}" value="${optionText}" >
                <div class="input-group-append">
                    <button class="btn btn-outline-danger remove-option" type="button">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    // Generate HTML for a complete question
    function createQuestionHTML(questionId, questionText = '', options = [], namePrefix = 'questions') {
        const displayNumber = questionId + 1;
        let optionsHTML = '';

        // If no options provided, create default 2 options
        if (options.length === 0) {
            optionsHTML = createOptionHTML(questionId, 0, '', namePrefix);
            optionsHTML += createOptionHTML(questionId, 1, '', namePrefix);
        } else {
            // Create options from provided data
            options.forEach((option, index) => {
                const text = option.text || option;
                optionsHTML += createOptionHTML(questionId, index, text, namePrefix);
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
        optionItem.remove();
        updateOptionNumbers(questionContainer);
    }

    // Update option numbers after removal
    function updateOptionNumbers(questionContainer) {
        questionContainer.find('.option-item').each(function (index) {
            const optionItem = $(this);
            const radioInput = optionItem.find('input[type="radio"]');
            const textInput = optionItem.find('input[type="text"]');

            radioInput.val(index);
            textInput.attr('placeholder', `Option ${index + 1}`);

            // Update option name attribute
            const currentName = textInput.attr('name');
            const newName = currentName.replace(/options\[\d+\]/, `options[${index}]`);
            textInput.attr('name', newName);
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
    // CUSTOM SURVEY / FEEDBACK FUNCTIONALITY
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
                if (field.startsWith('questions.')) {
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
    $('#survey-feedback-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        const submitButton = $('#submit-button');
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#generic-error').addClass('d-none');

        // Count questions based on survey
        const questionCount = $('#questions-wrapper .question-container').length;

        // if (questionCount < 2) {
        //     const message = 'Please add at least two questions';
        //     $('#generic-error').text(message).removeClass('d-none');
        //     errorMessage(message);
        //     return;
        // }
        submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        $('#custom').find('input, select, textarea').prop('disabled', true);

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
                console.log(response);
                if (response.redirect) {
                    suceessMessage(response.message);
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                }
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html('<i class="fa fa-save"></i> Create Survey');
                $('#custom').find('input, select, textarea').prop('disabled', false);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON;
                    displayValidationErrors(errors);
                } else {
                    const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    $('#generic-error').text(message).removeClass('d-none');
                    errorMessage(message);
                }
            }
        });
    });

    // Clear generic error when user starts typing
    $(document).on('input', 'input, textarea', function () {
        $('#generic-error').addClass('d-none');
    });
});
