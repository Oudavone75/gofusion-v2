document.getElementById('exportForm').addEventListener('submit', function (e) {
    let range = document.getElementById('dateRangePicker').value.trim();
    let start = '';
    let end = '';

    if (range.includes(' - ')) {
        // User selected a range
        let dates = range.split(' - ');
        start = dates[0].trim();
        end = dates[1].trim();
    } else {
        // User selected a single date → use same for start & end
        start = range;
        end = range;
    }

    document.getElementById('start_date').value = start;
    document.getElementById('end_date').value = end;

    $('#exportModal').modal('hide');
});

document.addEventListener('DOMContentLoaded', function () {
    const exportModal = document.getElementById('exportModal');

    // Focus on date picker when modal opens
    exportModal.addEventListener('shown.bs.modal', function () {
        document.getElementById('dateRangePicker').focus();
    });

    // Reset form when modal closes
    exportModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('exportForm').reset();
    });
});
