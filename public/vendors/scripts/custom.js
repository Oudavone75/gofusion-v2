var TYPE_QUERY_PARAM = "?type=campaign"; // Don't remove: Query params for handling redirect
function getCsrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
}

function handleTypeChange(ele, parent = false) {
    const isSeason = $(ele).val() === "season";
    if (parent) {
        $(".company-select, .department-select").toggleClass(
            "d-none",
            isSeason
        );
    } else {
        isSeason ? loadSeasons() : loadSeasons(true);

        $(".company-select").toggleClass("d-none", isSeason);
        $(".department-select").toggleClass("d-none", isSeason);
        $(".campaign-select-label").text(isSeason ? "Season" : "Campaign");
    }

    TYPE_QUERY_PARAM = isSeason ? "?type=season" : "?type=campaign";
}

$(document).ready(function () {
    //Delete Department
    $(".delete-department").click(function (e) {
        e.preventDefault();
        let departmentName = $(this).data("name");
        let checkUrl = $(this).data("check-url");
        let deleteUrl = $(this).data("url");
        // First check if company has active campaigns
        checkDepartmentActiveCampaigns(departmentName, checkUrl, deleteUrl);
    });

    function checkDepartmentActiveCampaigns(
        departmentName,
        checkUrl,
        deleteUrl
    ) {
        $.ajax({
            url: checkUrl,
            method: "GET",
            beforeSend: function () {
                Swal.fire({
                    title: "Checking...",
                    html: "Please wait while we check for active campaigns...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                if (response.has_active_campaigns) {
                    Swal.fire({
                        title: "Active Campaigns Found!",
                        html: `<strong>${departmentName}</strong> has active campaigns running. Are you sure you want to delete this company? This action will:<br><br>
                                   • Delete all active campaigns<br>
                                   • Delete the company admin<br>
                                   • Delete all company departments<br>
                                   • Remove company association from employees<br>
                                   • Delete all company data<br><br>
                                   <span style="color: #d33; font-weight: bold;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: "#6c757d",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Yes, delete everything!",
                        cancelButtonText: "Cancel",
                        customClass: {
                            confirmButton: "swal-confirm-custom",
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteDepartment(departmentName, deleteUrl);
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Delete Department",
                        html: `Are you sure you want to delete <strong>${departmentName}</strong>? This will:<br><br>
                                   • Delete all department campaigns<br>
                                   • Delete all department data<br><br>
                                   <span style="color: #d33; font-weight: bold;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: "#6c757d",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                        customClass: {
                            confirmButton: "swal-confirm-custom",
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteDepartment(departmentName, deleteUrl);
                        }
                    });
                }
            },
            error: function (xhr) {
                Swal.close();
                let errorMessage =
                    xhr.responseJSON?.message ||
                    "Failed to check active campaigns!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    function deleteDepartment(departmentName, deleteUrl) {
        $.ajax({
            url: deleteUrl,
            method: "POST",
            data: {
                _token: getCsrfToken(),
                _method: "DELETE",
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing",
                    html: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.fire({
                    title: "Deleted!",
                    html: `<strong>${departmentName}</strong> has been deleted successfully.`,
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message || "Something went wrong!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    $(".delete-company").click(function (e) {
        e.preventDefault();
        let companyId = $(this).data("id");
        let companyName = $(this).data("name");
        let deleteUrl = $(this).data("url");
		// First check if company has active campaigns
		checkActiveCampaigns(companyId, companyName, deleteUrl);
	});

	function checkActiveCampaigns(companyId, companyName, deleteUrl) {
        $.ajax({
            url: `/admin/company/has-active-campaigns/${companyId}`,
            method: "GET",
            beforeSend: function () {
                Swal.fire({
                    title: "Checking...",
                    html: "Please wait while we check for active campaigns...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                if (response.has_active_campaigns) {
                    Swal.fire({
                        title: "Active Campaigns Found!",
                        html: `<strong>${companyName}</strong> has active campaigns running. Are you sure you want to delete this company? This action will:<br><br>
                                   • Delete all active campaigns<br>
                                   • Delete the company admin<br>
                                   • Delete all company departments<br>
                                   • Remove company association from employees<br>
                                   • Delete all company data<br><br>
                                   <span style="color: #d33; font-weight: bold;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: "#6c757d",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Yes, delete everything!",
                        cancelButtonText: "Cancel",
                        customClass: {
                            confirmButton: "swal-confirm-custom",
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteCompany(companyName, deleteUrl);
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Delete Company",
                        html: `Are you sure you want to delete <strong>${companyName}</strong>? This will:<br><br>
                                   • Delete the company admin<br>
                                   • Delete all company departments<br>
                                   • Remove company association from employees<br>
                                   • Delete all company data<br><br>
                                   <span style="color: #d33; font-weight: bold;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: "#6c757d",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                        customClass: {
                            confirmButton: "swal-confirm-custom",
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteCompany(companyName, deleteUrl);
                        }
                    });
                }
            },
            error: function (xhr) {
                Swal.close();
                let errorMessage =
                    xhr.responseJSON?.message ||
                    "Failed to check active campaigns!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }
    function deleteCompany(companyName, deleteUrl) {
        $.ajax({
            url: deleteUrl,
            method: "DELETE",
            data: {
                _token: getCsrfToken(),
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Deleting Company...",
                    html: "Please wait while we delete the company and all associated data...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.fire({
                    title: "Deleted!",
                    html: `<strong>${companyName}</strong> and all associated data have been deleted successfully.`,
                    icon: "success",
                    timer: 3000,
                    showConfirmButton: true,
                    confirmButtonText: "OK",
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message ||
                    "Something went wrong while deleting the company!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    $(".delete-challenge").click(function (e) {
        e.preventDefault();
        let challengeName = $(this).data("name");
        let deleteUrl = $(this).data("url");
        Swal.fire({
            title: "Do you want to delete the challenge?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes,Delete It!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                deleteChallenge(challengeName, deleteUrl);
            }
        });
    });
    $(".btn-change-challenge-status").click(function (e) {
        e.preventDefault();
        let challengeName = $(this).data("name");
        let status = $(this).data("status");
        let approveUrl = $(this).data("url");
        Swal.fire({
            title: `Do you want to ${status} this challenge?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: `Yes,${status} It!`,
            cancelButtonText: "Cancel",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                approveChallenge(challengeName, approveUrl, status);
            }
        });
    });

    function deleteChallenge(challengeName, deleteUrl) {
        $.ajax({
            url: deleteUrl,
            method: "POST",
            data: {
                _token: getCsrfToken(),
                _method: "DELETE",
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing",
                    html: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                if (response.data_exist_of_this_challenge) {
                    Swal.fire({
                        title: "Error!",
                        html: "Challenge has been completed by the users, so it cannot be deleted!",
                        icon: "error",
                    });
                    return;
                }
                Swal.fire({
                    title: "Deleted!",
                    html: `<strong>${challengeName}</strong> has been deleted successfully.`,
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message || "Something went wrong!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    function approveChallenge(challengeName, approveurl, status) {
        $.ajax({
            url: approveurl,
            method: "PATCH",
            data: {
                _token: getCsrfToken(),
                _method: "PATCH",
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing",
                    html: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.fire({
                    title: "Approved!",
                    html: `<strong>${challengeName}</strong> has been ${status} successfully.`,
                    icon: "success",
                }).then(() => {
                    window.location.href = "/admin/inspiration-challenges";
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message || "Something went wrong!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    $("#company-select").on("change", function () {
        var companyId = $(this).val();
        var $departmentSelect = $("#department-select");

        $departmentSelect.html("<option>Loading...</option>");
        $departmentSelect.selectpicker("refresh");

        $.ajax({
            url: "/admin/campaign/departments/by-company/" + companyId,
            type: "GET",
            success: function (data) {
                $departmentSelect.empty();
                // $departmentSelect.append('<option value="" disabled selected>Select Department</option>');
                // $departmentSelect.append('<option value="" selected>All Departments</option>');
                $.each(data, function (key, department) {
                    $departmentSelect.append(
                        '<option value="' +
                            department.id +
                            '">' +
                            department.name +
                            "</option>"
                    );
                });

                $departmentSelect.selectpicker("refresh");
            },
            error: function () {
                $departmentSelect.html(
                    '<option value="">Error loading departments</option>'
                );
                $departmentSelect.selectpicker("refresh");
            },
        });
    });

    $(".delete-campaign").click(function (e) {
        e.preventDefault();
        let campaignName = $(this).data("name");
        let deleteUrl = $(this).data("url");
        Swal.fire({
            title: "Do you want to delete the campaign?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes,Delete It!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                deleteCampaign(campaignName, deleteUrl);
            }
        });
    });

    function deleteCampaign(campaignName, deleteUrl) {
        $.ajax({
            url: deleteUrl,
            method: "POST",
            data: {
                _token: getCsrfToken(),
                _method: "DELETE",
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing",
                    html: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                console.log(response);
                if (response.data_exist_of_this_campaign) {
                    Swal.fire({
                        title: "Error!",
                        html: "Campaign have active sessions, so it cannot be deleted!",
                        icon: "error",
                    });
                    return;
                }
                Swal.fire({
                    title: "Deleted!",
                    html: `<strong>${campaignName}</strong> has been deleted successfully.`,
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message || "Something went wrong!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }

    $(".delete-session").click(function (e) {
        e.preventDefault();
        let name = $(this).data("name");
        let deleteUrl = $(this).data("url");
        Swal.fire({
            title: "Do you want to delete the session?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes,Delete It!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSession(name, deleteUrl);
            }
        });
    });

    function deleteSession(name, deleteUrl) {
        $.ajax({
            url: deleteUrl,
            method: "POST",
            data: {
                _token: getCsrfToken(),
                _method: "DELETE",
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing",
                    html: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                if (response.data_exist_of_this_session) {
                    Swal.fire({
                        title: "Error!",
                        html: "Session have steps, so it cannot be deleted!",
                        icon: "error",
                    });
                    return;
                }
                if (response.message) {
                    Swal.fire({
                        title: "Error!",
                        html: response.message,
                        icon: "error",
                    });
                    return;
                }
                Swal.fire({
                    title: "Deleted!",
                    html: `<strong>${name}</strong> has been deleted successfully.`,
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function (xhr) {
                let errorMessage =
                    xhr.responseJSON?.message || "Something went wrong!";
                Swal.fire({
                    title: "Error!",
                    html: errorMessage,
                    icon: "error",
                });
            },
        });
    }
    $("#add-department-field").click(function () {
        var newField = `<div class="col-md-6 col-sm-12 d-flex align-items-center department-field">
            <div class="col-md-10">
                <label class="form-control-label">Department Name</label>
                <input type="text" name="department[]" class="form-control" placeholder="Enter Department Name">
                <div class="form-control-feedback mt-2 d-none"></div>
            </div>
            <div class="col-md-2" style="margin-top:21px;">
                <button type="button" class="btn btn-danger mt-2 remove-department-field">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>`;
        $("#department-wrapper").append(newField);
    });

    $(document).on("click", ".remove-department-field", function () {
        $(this).closest(".department-field").remove();
    });

    // Delegate the click event to the #add-reward-field button
    $("#department-wrapper").on("click", "#add-reward-field", function () {
        // Remove the + button from all rows before adding a new one
        $("#department-wrapper .reward-field").each(function () {
            $(this).find(".btn-success").remove();
        });

        // Add a new row with input fields
        var newField = `<div class="col-md-12 col-sm-12 mt-2 d-flex align-items-center reward-field">
            <div class="col-md-3">
                <label class="form-control-label">From Ranking</label>
                <input type="number" name="from_ranking[]" value="{{ old('from_ranking[]') }}"
                    class="form-control"
                    placeholder="Enter From Ranking" min="1" >
                <div class="form-control-feedback mt-2 d-none"></div>
            </div>
            <div class="col-md-3">
                <label class="form-control-label">To Ranking</label>
                <input type="number" name="to_ranking[]" value="{{ old('to_ranking[]') }}"
                    class="form-control"
                    placeholder="Enter To Ranking" min="2">
                <div class="form-control-feedback mt-2 d-none"></div>
            </div>
            <div class="col-md-3">
                <label class="form-control-label">Reward <small>(In €)</small> </label>
                <input type="number" name="reward[]" value="{{ old('reward[]') }}"
                    class="form-control" min="1"
                    placeholder="Enter Reward">
                <div class="form-control-feedback mt-2 d-none"></div>
            </div>
            <div class="col-md-1" style="margin-top:27px;">
                <button type="button" class="btn btn-success" id="add-reward-field">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
            <div class="col-md-2" style="margin-top:21px;">
                <button type="button" class="btn btn-danger mt-2 remove-reward-field">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>`;

        // Append the new field at the end
        $("#department-wrapper").append(newField);
    });

    // Delegate the click event for removing the row
    $("#department-wrapper").on("click", ".remove-reward-field", function () {
        // Remove the reward field (the row) containing the button
        $(this).closest(".reward-field").remove();
        console.log("removed");
        console.log($("#department-wrapper .reward-field").length);
        // Ensure the last row has the + button
        if ($("#department-wrapper .reward-field").length > 0) {
            // Add the + button to the last row
            var lastRow = $("#department-wrapper .reward-field").last();
            console.log(lastRow.length);
            if (!lastRow.find(".btn-success").length) {
                lastRow
                    .find(".col-md-1")
                    .last()
                    .append(
                        '<button type="button" class="btn btn-success" id="add-reward-field"><i class="fa fa-plus"></i></button>'
                    );
            } else if (
                lastRow.find(".btn-success").length === 1 &&
                $("#department-wrapper .reward-field").length === 1
            ) {
                console.log("last row");
                lastRow
                    .last()
                    .append(
                        '<button type="button" class="btn btn-success" id="add-reward-field"><i class="fa fa-plus"></i></button>'
                    );
            }
        }
    });
});

function changeStatus(id) {
    if (id == null) {
        return;
    }
    let url = $("#change-status-" + id).data("url");
    Swal.fire({
        title: "Are you sure?",
        text: "You want to change the status to active!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, change it!",
    }).then((result) => {
        if (result.isConfirmed) {
            changeCampaignStatus(id, url);
        }
    });
}

function changeCampaignStatus(id, url) {
    console.log(url);
    $.ajax({
        url: url,
        method: "POST",
        data: {
            _token: getCsrfToken(),
        },
        beforeSend: function () {
            Swal.fire({
                title: "Processing",
                html: "Please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    title: "Success!",
                    html: response.message,
                    icon: "success",
                });
                setTimeout(() => {
                    Swal.close();
                    window.location.reload();
                    return;
                }, 2000);
            } else {
                Swal.fire({
                    title: "Error!",
                    html: response.message,
                    icon: "error",
                });
            }
        },
        error: function (xhr) {
            let errorMessage =
                xhr.responseJSON?.message || "Something went wrong!";
            Swal.fire({
                title: "Error!",
                html: errorMessage,
                icon: "error",
            });
        },
    });
}

function getSelectData(selectElement, ele, title, label) {
    let $newSelect = $("#" + ele);
    let url = selectElement.dataset.url;
    const id = selectElement.value;
    url = url.replace(label, id);
    $newSelect.html('<option value="">Loading...</option>');
    $.ajax({
        url: url,
        type: "GET",
        success: function (data) {
            $newSelect.empty();
            $newSelect.append(
                '<option value="" disabled selected>Select ' +
                    title +
                    "</option>"
            );
            $.each(data, function (key, obj) {
                $newSelect.append(
                    '<option value="' + obj.id + '">' + obj.title + "</option>"
                );
            });
        },
        error: function () {
            $newSelect.html(
                '<option value="">Error loading ' + title + "</option>"
            );
        },
    });
}

function deleteRecord(ele) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            deleteData(ele);
        }
    });
}

function deleteData(ele) {
    let url = $(ele).data("url");
    $.ajax({
        url: url,
        method: "POST",
        data: {
            _token: getCsrfToken(),
            _method: "DELETE",
        },
        beforeSend: function () {
            Swal.fire({
                title: "Processing",
                html: "Please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },
        success: function (response) {
            if (response.record_exist) {
                Swal.fire({
                    title: "Error!",
                    html: "Cannot delete this step because it has attempts!",
                    icon: "error",
                });
                return;
            }
            Swal.fire({
                title: "Deleted!",
                html: response.message,
                icon: "success",
            }).then(() => {
                window.location.reload();
            });
        },
        error: function (xhr) {
            let errorMessage =
                xhr.responseJSON?.message || "Something went wrong!";
            Swal.fire({
                title: "Error!",
                html: errorMessage,
                icon: "error",
            });
        },
    });
}
function updateFileName(input) {
    const fileName = input.files[0]?.name || "Choose file";
    input.nextElementSibling.innerText = fileName;
}

function submitForm(e, ele, route = "", routeWithPrefix = false) {
    e.preventDefault();
    let $form = $(ele);
    let url = $form.attr("action");
    let formData = new FormData($form[0]);
    let $submit_btn = $("#submit-button");
    $submit_btn.prop("disabled", true).text("Submitting...");
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
            console.log(response);
            $submit_btn.prop("disabled", false).text("Submit");
            suceessMessage(response.message || "Submitted successfully!");
            // $form[0].reset();
            if (route == "") {
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                setTimeout(() => {
                    window.location.href = routeWithPrefix
                        ? route
                        : "/admin/" + route;
                }, 1000);
            }
        },
        error: function (xhr) {
            $submit_btn.prop("disabled", false).text("Submit");
            if (xhr.status === 422) {
                handleValidationError(xhr);
            } else {
                errorMessage(
                    xhr.responseJSON?.message ?? "Something went wrong."
                );
            }
        },
    });
}

function handleValidationError(xhr) {
    console.log("Error:", xhr.responseJSON);
    $(".form-control-feedback").text("").addClass("d-none");
    $("[name]").removeClass("form-control-danger");
    $(".select2-container").removeClass("border border-danger");
    $(".bootstrap-select .dropdown-toggle")
        .removeClass("border-danger")
        .css("border-color", "");
    $(".has-danger").removeClass("has-danger");
    $(".has-error").removeClass("has-error"); // Add this

    const errors = xhr.responseJSON;
    $.each(errors, function (field, messages) {
        // Handle array item errors like 'keywords.0' -> show on keywords container
        let arrayMatch = field.match(/^(\w+)\.\d+$/);
        if (arrayMatch) {
            let $container = $(`#${arrayMatch[1]}-field`);
            if ($container.length) {
                let $feedback = $container.find('.form-control-feedback');
                if ($feedback.length && $feedback.hasClass('d-none')) {
                    $feedback.text(messages[0]).removeClass('d-none');
                }
                $container.addClass('has-error');
                return;
            }
        }

        // Handle both 'departments' and 'departments[]'
        let $input = $(`[name="${field}"]`);
        if ($input.length === 0) {
            $input = $(`[name="${field}[]"]`);
        }

        // Handle array parent errors like 'keywords' -> find keywords[] inputs
        if ($input.length === 0) {
            let $container = $(`#${field}-field`);
            if ($container.length) {
                let $feedback = $container.find('.form-control-feedback');
                if ($feedback.length) {
                    $feedback.text(messages[0]).removeClass('d-none');
                }
                $container.addClass('has-error');
                return;
            }
        }

        if ($input.length === 0) return; // Skip if not found

        const isSelect2 = $input.hasClass("custom-select2");
        const isSelectPicker = $input.hasClass("selectpicker");
        let $feedback;
        let $container;

        $input.addClass("form-control-danger");
        $input.parent().addClass("has-danger");

        if (isSelect2) {
            $input.next(".select2-container").addClass("border border-danger");
            $feedback = $input.siblings(".form-control-feedback");
            $container = $input.closest(".col-md-6, .col-sm-12");
        } else if (isSelectPicker) {
            // Bootstrap select creates a wrapper next to the select
            const $wrapper = $input.parent(".bootstrap-select");

            if ($wrapper.length) {
                // If wrapped (rendered HTML from inspect)
                $wrapper.addClass("has-danger");
                $wrapper
                    .find(".dropdown-toggle")
                    .addClass("border-danger")
                    .css("border-color", "#dc3545");
                $feedback = $wrapper.siblings(".form-control-feedback");
                $container = $wrapper.closest(
                    ".col-md-6, .col-sm-12, .department-select"
                );
            } else {
                // If not wrapped yet (original HTML)
                const $next = $input.next(".bootstrap-select");
                $next.addClass("has-danger");
                $next
                    .find(".dropdown-toggle")
                    .addClass("border-danger")
                    .css("border-color", "#dc3545");
                $feedback = $input.siblings(".form-control-feedback");
                $container = $input.closest(
                    ".col-md-6, .col-sm-12, .department-select"
                );
            }
        } else {
            $feedback = $input.siblings(".form-control-feedback");
            $container = $input.closest(".col-md-6, .col-sm-12");
        }

        // Add error class to container for label styling
        if ($container && $container.length) {
            $container.addClass("has-error");
        }

        if ($feedback && $feedback.length) {
            $feedback.text(messages[0]).removeClass("d-none");
        }
    });
}

function addPoints(e, ele, isAccept = false) {
    const description = $(ele).find(".description").val();
    console.log(description);
    e.preventDefault();
    if (!isAccept) {
        Swal.fire({
            title: '<div class="text-left pb-2 w-100"><h5 class="modal-title font-weight-700"><i class="icon-copy dw dw-warning text-danger me-2"></i> Reject Challenge</h5></div>',
            html: `
            <div class="text-left mt-3" style="text-align: left;">
                <p class="text-muted mb-4">Are you sure you want to reject this challenge? This action cannot be undone.</p>
            </div>
        `,
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: "Yes, Reject It",
            cancelButtonText: "Cancel",
            showLoaderOnConfirm: true,
            focusConfirm: false,
            width: "500px",
            customClass: {
                container: 'swal2-custom-container',
                popup: 'border-0',
                actions: 'w-100 d-flex justify-content-between px-4 pb-2',
                confirmButton: "btn btn-danger btn-lg px-4 order-2",
                cancelButton: "btn btn-light btn-lg px-4 order-1",
                title: 'px-4 pt-4'
            },
            buttonsStyling: false,
            didOpen: (modal) => {
                modal.style.borderRadius = '16px';
            },
        }).then((result) => {
            if (result.isConfirmed) {
                submitForm(e, ele);
            }
        });
        return;
    }
    Swal.fire({
        title: '<div class="text-left pb-2 w-100"><h5 class="modal-title font-weight-700"><i class="icon-copy dw dw-star text-primary me-2"></i> Award Challenge</h5></div>',
        html: `
        <div class="text-left" style="text-align: left;">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">User Description</label>
                <textarea id="swal-user-description" class="form-control" style="height:100px; white-space:pre-wrap; background-color: #f8f9fa;" readonly>${
                    description || ""
                }</textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Points <span class="text-danger">*</span></label>
                <input id="swal-points" type="number" class="form-control form-control-lg" placeholder="Enter points" min="1" max="300"
                oninput="this.value = this.value < 1 ? '' : this.value">
                <small class="form-text text-muted">Assign score points between 1 and 300.</small>
            </div>

            <div class="form-group mb-2">
                <label class="form-label fw-bold">AI Description <span class="text-danger">*</span></label>
                <textarea id="swal-ai-description" class="form-control" placeholder="Enter AI feedback description" style="height:110px;"></textarea>
                <small class="form-text text-muted">Provide a brief explanation for the assigned points.</small>
            </div>
        </div>
    `,
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: "Accept",
        cancelButtonText: "Cancel",
        showLoaderOnConfirm: true,
        focusConfirm: false,
        width: "550px",
        customClass: {
            container: 'swal2-custom-container',
            popup: 'border-0',
            actions: 'w-100 d-flex justify-content-between px-4 pb-2',
            confirmButton: "btn btn-primary btn-lg px-4 order-2",
            cancelButton: "btn btn-light btn-lg px-4 order-1",
            title: 'px-4 pt-4'
        },
        buttonsStyling: false,
        didOpen: (modal) => {
            modal.style.borderRadius = '16px';
        },
        preConfirm: () => {
            const points = document.getElementById("swal-points").value;
            const aiDescription = document.getElementById(
                "swal-ai-description"
            ).value;

            if (!points) {
                Swal.showValidationMessage("Points are required");
                return false;
            } else if (isNaN(points) || points < 1 || points > 300) {
                Swal.showValidationMessage("Points must be between 1 and 300");
                return false;
            }
            if (!aiDescription) {
                Swal.showValidationMessage("AI Description is required");
                return false;
            }
            return { points, aiDescription };
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
        if (result.isConfirmed) {
            const { points, aiDescription } = result.value;
            $(ele)
                .find('input[name="description"]')
                .val($("#swal-user-description").val());
            $(ele).find('input[name="points"]').val(points);
            $(ele).find('input[name="guideline_text"]').val(aiDescription);

            submitForm(e, ele);
        }
    });
}

function loadSeasons(loadDefault = false) {
    var $seasonSelect = $(".campaign-select");
    if (loadDefault) {
        $seasonSelect.html(
            '<option value="" disabled selected>Select Campaign</option>'
        );
        return;
    }
    // Show loading
    $seasonSelect.html('<option value="">Loading...</option>');

    $.ajax({
        url: "/admin/get-seasons/",
        type: "GET",
        success: function (data) {
            $seasonSelect.empty();
            $seasonSelect.append(
                '<option value="" disabled selected>Select Season</option>'
            );
            $.each(data, function (key, season) {
                $seasonSelect.append(
                    '<option value="' +
                        season.id +
                        '">' +
                        season.title +
                        "</option>"
                );
            });
        },
        error: function () {
            $seasonSelect.html(
                '<option value="">Error loading departments</option>'
            );
        },
    });
}

function setupEventDropdown() {
    $(document).ready(function () {
        function toggleEventDropdown() {
            const selectedText = $("#category-select option:selected")
                .text()
                .trim();

            if (selectedText === "Event") {
                $("#event-dropdown-wrapper").css({
                    display: "flex",
                    "flex-direction": "column",
                    height: "auto",
                    overflow: "visible",
                });
            } else {
                $("#event-dropdown-wrapper").css({
                    display: "none",
                    height: "0",
                    overflow: "hidden",
                });

                $("#event-select").val("").trigger("change");
            }
        }

        $("#category-select").on("change", toggleEventDropdown);
        setTimeout(toggleEventDropdown, 100);
    });
}

function toggleStatus() {
    $(document).on("change", ".switch-btn", function () {
        const isChecked = $(this).is(":checked");
        const url = $(this).data("url");

        $.ajax({
            url: url,
            type: "POST",
            data: {
                status: isChecked ? "active" : "inactive",
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                suceessMessage(response.message || "Status updated!");
            },
            error: function (xhr) {
                errorMessage("Failed to update status.");
                $(this).prop("checked", !isChecked);
            }.bind(this),
        });
    });
}

function initGalleryDropzone() {
    Dropzone.instances.forEach((dz) => dz.destroy());
    Dropzone.autoDiscover = false;

    let galleryDropzone = new Dropzone("#gallery-dropzone", {
        url: galleryStoreUrl,
        paramName: "gallery_images",
        maxFilesize: 50,
        acceptedFiles: "image/*",
        uploadMultiple: true,
        parallelUploads: 100,
        autoProcessQueue: false,
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        init: function () {
            let myDropzone = this;
            document
                .querySelector("#upload-btn")
                .addEventListener("click", function () {
                    // send all files in one request
                    myDropzone.processQueue();
                });

            this.on("sendingmultiple", function (files, xhr, formData) {
                let $uploadBtn = $("#upload-btn");
                $uploadBtn.prop("disabled", true).text("Uploading...");
            });

            this.on("successmultiple", function (files, response) {
                toastr.success(response.message);
                setTimeout(() => {
                    window.location.href = galleryIndexUrl;
                }, 1500);
            });

            this.on("errormultiple", function (files, response) {
                let msg = response.message || "Something went wrong!";
                toastr.error(msg);
            });
        },
    });
}

function copyToClipboard(element) {
    const url = element.getAttribute("data-url");

    if (navigator.clipboard && window.isSecureContext) {
        // Modern secure browsers
        navigator.clipboard
            .writeText(url)
            .then(() => {
                toastr.success("Link copied to clipboard!");
            })
            .catch((err) => {
                console.error("Clipboard API failed:", err);
                fallbackCopyText(url);
            });
    } else {
        // Fallback for non-HTTPS or unsupported browsers
        fallbackCopyText(url);
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed"; // avoid scrolling to bottom
    textArea.style.left = "-9999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand("copy");
        toastr.success("Link copied to clipboard!");
    } catch (err) {
        console.error("Fallback copy failed:", err);
    }

    document.body.removeChild(textArea);
}

function updateCategoryName(select) {
    var selectedOption = select.options[select.selectedIndex];
    var categoryName = selectedOption.getAttribute("data-name");
    if (categoryName == "Event") {
        $(".event-column").removeClass("d-none");
        showEventDropdown();
        $("#video-url").addClass("d-none");
        $("#title").addClass("d-none");
        $("#validation-mode").addClass("d-none");
    } else {
        $(".event-column").addClass("d-none");
        hideEventDropdown();
        $("#video-url").removeClass("d-none");
        $("#title").removeClass("d-none");
        $("#validation-mode").removeClass("d-none");
    }
    document.getElementById("category-name").value = categoryName || "";
}

function showEventDropdown() {
    let $el = $("#event-select");

    // Show wrapper
    $(".event-column").removeClass("d-none");

    // Destroy if already initialized
    if ($el.hasClass("select2-hidden-accessible")) {
        $el.select2("destroy");
    }

    // Re-init
    $el.select2({
        width: "100%",
        placeholder: "Select Event Type",
    });
}

function hideEventDropdown() {
    let $el = $("#event-select");

    // Destroy Select2 instance
    if ($el.hasClass("select2-hidden-accessible")) {
        $el.select2("destroy");
    }

    // Hide wrapper
    $(".event-column").addClass("d-none");
}

function initCategorySelect() {
    var select = document.getElementById("category-select");
    if (select) {
        select.addEventListener("change", function () {
            updateCategoryName(this);
        });
        updateCategoryName(select);
    }
}

function handleEventTypeChange(element) {
    let selectedType = $(element).val();

    if (selectedType === "onsite") {
        $("#event-location-label").text("Event Location*");
        $("#event-location").attr("placeholder", "Enter event location");
    } else if (selectedType === "online") {
        $("#event-location-label").text("Event URL*");
        $("#event-location").attr("placeholder", "Enter event URL");
    }
}

// Toggle minus button visibility depending on row count
function toggleRemoveButtons() {
    const rows = document.querySelectorAll("table tbody tr");
    const removeButtons = document.querySelectorAll(".remove-user");

    if (rows.length <= 1) {
        removeButtons.forEach((btn) => (btn.style.display = "none"));
    } else {
        removeButtons.forEach((btn) => (btn.style.display = "inline-block"));
    }
}

// Handle row removal, hiding submit button, and showing "No data available"
function handleRowRemoval(e) {
    if (e.target.closest(".remove-user")) {
        e.preventDefault();
        const row = e.target.closest("tr");
        row.remove();

        const tbody = document.querySelector("table tbody");
        const hasRows =
            tbody.querySelectorAll("input[name='user_id[]']").length > 0;

        const submitBtn = document.querySelector(".submit-btn");

        if (!hasRows) {
            if (submitBtn) {
                submitBtn.style.display = "none"; // hide submit button
            }

            if (!tbody.querySelector(".no-data-row")) {
                const tr = document.createElement("tr");
                tr.classList.add("no-data-row");
                tr.innerHTML = `<td colspan="9" class="text-center text-muted">No data available.</td>`;
                tbody.appendChild(tr);
            }
        }

        // Re-check buttons after row removal
        toggleRemoveButtons();
    }
}

// Initialize all event listeners for reward table
function initRewardTable() {
    document.addEventListener("click", handleRowRemoval);
    toggleRemoveButtons(); // run once on page load
}

function disableSubmitButton(form) {
    const button = form.querySelector("#submit-button");
    if (button) {
        button.disabled = true;
        button.innerText = "Submitting...";
    }
}

function handleModeChange(element) {
    let selectedType = $(element).val();

    if (selectedType === "photo") {
        $("#photo-field").removeClass("d-none");
        $("#ai-field").removeClass("d-none");
        $("#video-field").addClass("d-none");
        $("#keywords-field").addClass("d-none");
    } else if (selectedType === "video") {
        $("#photo-field").addClass("d-none");
        $("#ai-field").addClass("d-none");
        $("#video-field").removeClass("d-none");
        $("#keywords-field").removeClass("d-none");
    } else {
        $("#photo-field").addClass("d-none");
        $("#ai-field").addClass("d-none");
        $("#video-field").addClass("d-none");
        $("#keywords-field").addClass("d-none");
    }
}

function addKeywordInput(container) {
    var html = '<div class="keyword-entry input-group mb-2">' +
        '<input type="text" name="keywords[]" class="form-control" placeholder="Enter keyword">' +
        '<div class="input-group-append">' +
        '<button type="button" class="btn btn-danger btn-sm" onclick="removeKeywordInput(this)">' +
        '<i class="fa fa-times"></i>' +
        '</button>' +
        '</div>' +
        '</div>';
    $(container).append(html);
}

function removeKeywordInput(button) {
    $(button).closest('.keyword-entry').remove();
}

function handleNotificationTypeChange(select) {
    const scheduleContainer = document.getElementById(
        "schedule-datetime-container"
    );
    if (select.value === "scheduled") {
        scheduleContainer.classList.remove("d-none");
    } else {
        scheduleContainer.classList.add("d-none");
        document.getElementById("scheduled_at").value = ""; // Clear field when hidden
    }
}

function changeAppealingStatus(id, points = 0, status) {
    if (!id) return;

    let url = $(`#${status}-btn-${id}`).data("url");

    if (status === "approve") {
        // ✅ Approval Flow
        Swal.fire({
            title: `Are you sure you want to approve this appeal?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, approve it",
            confirmButtonColor: "#28a745",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                changeAppealingUserStatus(id, url, points, status, null);
            }
        });
    } else if (status === "reject") {
        // ❌ Rejection Flow – Ask for reason
        Swal.fire({
            title: "Reject Reason",
            input: "textarea",
            inputPlaceholder: "Enter your reason for rejection...",
            inputAttributes: { "aria-label": "Reason for rejection" },
            showCancelButton: true,
            confirmButtonText: "Submit",
            confirmButtonColor: "#dc3545",
            cancelButtonText: "Cancel",
            inputValidator: (value) => {
                if (!value) {
                    return "Please provide a reason before rejecting.";
                }
            },
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value;
                changeAppealingUserStatus(id, url, points, status, reason);
            }
        });
    }
}

function changeAppealingUserStatus(id, url, points, status, reason = null) {
    $.ajax({
        url: url,
        method: "POST",
        data: {
            _token: getCsrfToken(),
            points: points,
            status: status,
            reason: reason,
        },
        beforeSend: function () {
            Swal.fire({
                title: "Processing...",
                html: "Please wait...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
        },
        success: function (response) {
            Swal.close();
            if (response.success) {
                Swal.fire({
                    title: "Success!",
                    html: response.message,
                    icon: "success",
                });
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Swal.fire({
                    title: "Error!",
                    html: response.message,
                    icon: "error",
                });
            }
        },
        error: function (xhr) {
            const errorMessage =
                xhr.responseJSON?.message || "Something went wrong!";
            Swal.fire({
                title: "Error!",
                html: errorMessage,
                icon: "error",
            });
        },
    });
}

function initAjaxSearch(options) {
    let timer;

    // Search functionality
    $(options.input).on("keyup", function () {
        clearTimeout(timer);
        let search = $(this).val();
        timer = setTimeout(() => {
            fetchData(options.url, search, options);
        }, options.delay || 400);
    });

    // Pagination click handler (event delegation)
    if (options.pagination) {
        $(document).on("click", options.pagination + " a", function (e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let search = $(options.input).val();
            fetchData(url, search, options);
        });
    }

    // Centralized AJAX function
    function fetchData(url, search, opts) {
        $.ajax({
            url: url,
            type: "GET",
            data: { search: search },
            success: function (response) {
                let $response = $("<div>").html(response);

                // Update table body
                let $tableBody = $response.find(opts.tableBody);
                if ($tableBody.length) {
                    $(opts.tableBody).replaceWith($tableBody);
                }

                // Update pagination if provided
                if (opts.pagination) {
                    let $paginationContainer = $response.find(opts.pagination);
                    if ($paginationContainer.length) {
                        // Replace entire pagination container to handle empty states
                        $(opts.pagination).replaceWith($paginationContainer);
                    }
                }

                // Reinitialize Switchery if enabled
                if (opts.switchery === true) {
                    $(".switchery").remove();
                    $(".switch-btn").each(function () {
                        new Switchery(this);
                    });
                }
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr);
            },
        });
    }
}

$(document).ready(function () {
    var $departmentSelect = $("#department-select");

    // Initialize Bootstrap Select
    $departmentSelect.selectpicker({
        // Optional settings
        noneSelectedText: "Select Departments",
        liveSearch: true, // if you want a search box
    });
});
document.getElementById("exportForm").addEventListener("submit", function (e) {
    let range = document.getElementById("dateRangePicker").value;

    if (range.includes(" - ")) {
        let dates = range.split(" - ");
        document.getElementById("start_date").value = dates[0].trim();
        document.getElementById("end_date").value = dates[1].trim();
    }
    $("#exportModal").modal("hide");
});
// Initialize the modal and handle form submission
document.addEventListener("DOMContentLoaded", function () {
    // When modal is shown, focus on date picker
    const exportModal = document.getElementById("exportModal");
    exportModal.addEventListener("shown.bs.modal", function () {
        document.getElementById("dateRangePicker").focus();
    });

    // Reset form when modal is closed
    exportModal.addEventListener("hidden.bs.modal", function () {
        document.getElementById("exportForm").reset();
    });
});
