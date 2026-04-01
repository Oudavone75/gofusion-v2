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

// close the modal on submit form
document.getElementById("exportForm").addEventListener("submit", function (e) {
    $("#exportModal").modal("hide");
});
