$(document).ready(function () {
    var $sessionSelect = $("#session-select");

    // Initialize Bootstrap Select
    $sessionSelect.selectpicker({
        // Optional settings
        noneSelectedText: "Select sessions",
        liveSearch: true // if you want a search box
    });
});

function submitFileImportForm(
    e,
    ele,
    route = "",
    routeWithPrefix = false,
    type = "steps"
) {
    e.preventDefault();
    let $form = $(ele);
    let url = $form.attr("action");
    let formData = new FormData($form[0]);
    let $submit_btn = $("#submit-button");
    $submit_btn.prop("disabled", true).text("Importing...");
    $("#alert-box").addClass("d-none");
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $submit_btn
                .prop("disabled", false)
                .html(`<i class="fa fa-save"></i> Import`);
            suceessMessage(response.message || "Imported successfully!");

            const quizResult = response.result.quiz_result;
            const challengeResult = response.result.challenge_result;
            const spinWheelResult = response.result.spinWheel_result;
            const feedbackResult = response.result.feedback_result;
            const eventResult = response.result.event_result;
            const challengeToCreateResult =
                response.result.challenge_to_create_result;

            if (type == "inspirational") {
                const inspirationalResult =
                    response.result.inspirational_result;
                inspirationalSuccessAlertDetail(inspirationalResult);
            } else if (type == "sessions") {
                window.location.href = basePath() + "/sessions";
            } else {
                successAlertDetail(
                    quizResult,
                    challengeResult,
                    spinWheelResult,
                    feedbackResult,
                    eventResult,
                    challengeToCreateResult
                );
            }

            $("#alert-box").addClass("d-none");
        },
        error: function (xhr) {
            $("#success-box").addClass("d-none");
            $submit_btn
                .prop("disabled", false)
                .html(`<i class="fa fa-save"></i> Import`);
            if (xhr.status === 422) {
                handleFileImportValidationError(xhr);
            } else {
                $("#alert-box-heading").text("Invalid File Error:");
                if (
                    xhr.responseJSON?.result &&
                    xhr.responseJSON.result.length > 0
                ) {
                    $("#alert-box-heading").text(xhr.responseJSON?.message);

                    let messages = "";
                    xhr.responseJSON.result.forEach(function (err) {
                        messages += `<div>${err}</div>`;
                    });
                    $("#alert-box-message").html(messages);
                } else {
                    $("#alert-box-message").html(xhr.responseJSON?.message);
                }

                $("#alert-box").removeClass("d-none");
                errorMessage(
                    xhr.responseJSON?.message ?? "Something went wrong."
                );
            }
        },
    });
}

function sheetsResultHTML(result, sheet) {
    let html = "<ul>";
    if (result.inserted_sessions?.length) {
        html += result.inserted_sessions
            .map(
                (text) =>
                    `<li class="text-primary">${sheet} added for session : ${text}</li>`
            )
            .join("");
    }

    if (result.skipped_sessions?.length) {
        html += result.skipped_sessions
            .map(
                (text) =>
                    `<li class="text-info">${sheet} already exists for session : ${text}</li>`
            )
            .join("");
    }
    html += "</ul>";
    return html;
}

function successAlertDetail(
    quizResult,
    challengeResult,
    spinWheelResult,
    feedbackResult,
    eventResult,
    challengeToCreateResult
) {
    const htmlContent = `
        <div style="text-align:left">
            <h5 class="mb-1 text-success">📋 Quiz</h5>
            ${sheetsResultHTML(quizResult, "Quiz")}
            <hr>

            <h5 class="mt-3 mb-1 text-warning">🏆 Challenge</h5>
            ${sheetsResultHTML(challengeResult, "Challenge")}
            <hr>
            <h5 class="mt-3 mb-1 text-info">🎡 Spin Wheel</h5>
            ${sheetsResultHTML(spinWheelResult, "Spin Wheel")}
            <hr>
            <h5 class="mt-3 mb-1 text-secondary">📝 Feedback/Survey</h5>
            ${sheetsResultHTML(feedbackResult, "Survey / Feedback")}
        </div>
    `;

    Swal.fire({
        title: `<strong>Import Results</strong>`,
        icon: "success",
        html: `<div style="max-height:500px; overflow-y:auto; text-align:left;">${htmlContent}</div>`,
        width: 600,
        showCloseButton: true,
        focusConfirm: true,
        confirmButtonText: `<i class="fa fa-check"></i> OK`,
        didClose: () => {
            location.reload();
        },
    });
}

function inspirationalSuccessAlertDetail(result) {
    const htmlContent = `
        <div style="text-align:center">
            <p>${result} Inspirational challenges has been imported successfully!</p>
        </div>
    `;

    Swal.fire({
        title: `<strong>Import Results</strong>`,
        icon: "success",
        html: `<div style="max-height:500px; overflow-y:auto; text-align:left;">${htmlContent}</div>`,
        width: 600,
        showCloseButton: true,
        focusConfirm: true,
        confirmButtonText: `<i class="fa fa-check"></i> OK`,
        didClose: () => {
            window.location.href = basePath() + "/inspiration-challenges";
        },
    });
}

function basePath() {
    let path = window.location.pathname;

    if (path.startsWith("/company-admin")) {
        return "/company-admin";
    } else if (path.startsWith("/admin")) {
        return "/admin";
    }
    return null; // not found
}

function handleFileImportValidationError(xhr) {
    $(".form-control-feedback").text("").addClass("d-none");
    $("[name]").removeClass("form-control-danger");
    $(".select2-container").removeClass("border border-danger");
    $(".has-danger").removeClass("has-danger");

    const errors = xhr.responseJSON;
    $.each(errors, function (field, messages) {
        if (field === "session") {
            field = "session[]";
        }

        const $input = $(`[name="${field}"]`);
        const $feedback = $input.siblings(".form-control-feedback");
        const isSelect2 = $input.hasClass("custom-select2");

        $input.addClass("form-control-danger");
        $input.parent().addClass("has-danger");

        if (isSelect2) {
            $input.next(".select2-container").addClass("border border-danger");
        }

        $feedback.text(messages[0]).removeClass("d-none");
    });
}

function getSelectDataImportFile(
    selectElement,
    ele,
    title,
    label,
    format = "campaign"
) {
    let $newSelect = $("#" + ele);
    let url = selectElement.dataset.url;
    const id = selectElement.value;
    url = url.replace(label, id);

    // Show loading
    $newSelect.html('<option value="">Loading...</option>');
    $newSelect.selectpicker("refresh");

    $.ajax({
        url: url,
        type: "GET",
        success: function (data) {
            $newSelect.empty();

            // Add placeholder only if NOT session
            if (format !== "session") {
                $newSelect.append(
                    '<option value="" disabled selected>Select ' + title + "</option>"
                );
            }

            // Build options
            $.each(data, function (key, obj) {
                if (format !== "session") {
                    $newSelect.append(
                        '<option value="' + obj.id + '">' + obj.title + "</option>"
                    );
                } else {
                    let keyIndex = ++key;
                    $newSelect.append(
                        '<option value="' + obj.id + '">Session Number : ' +
                        keyIndex + " - " + obj.title + "</option>"
                    );
                }
            });

            // ✅ Refresh selectpicker after updating options
            $newSelect.selectpicker("refresh");
        },
        error: function () {
            $newSelect.html('<option value="">Error loading ' + title + "</option>");
            $newSelect.selectpicker("refresh");
        },
    });
}
