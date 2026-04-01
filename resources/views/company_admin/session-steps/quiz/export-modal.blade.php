<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="icon-copy dw dw-download text-primary me-2"></i>
                    Export Quiz Data
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pt-2">
                <p class="text-muted mb-4">Select your export criteria to download the Quiz
                    attempted users data.</p>
                <form action="{{ route('company_admin.steps.quiz.export', $quiz_id) }}" method="GET"
                    id="exportForm">
                    <div class="form-group">
                        <label class="form-label fw-semibold">
                            Export Format <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="export_format" class="form-control form-select form-select-lg" required>
                            <option value="csv" selected>CSV (.csv)</option>
                            <option value="excel">Excel (.xlsx)</option>
                        </select>
                        <small class="form-text text-muted">CSV is recommended for large datasets
                            (faster)</small>
                    </div>

                    <!-- Date or Date Range Picker -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="icon-copy dw dw-calendar-1"></i>
                            Date / Date Range <span class="text-danger">*</span>
                        </label>

                        <div class="date-input-wrapper">
                            <input class="form-control form-control-lg datetimepicker-range"
                                placeholder="Select date or date range" type="text" id="dateRangePicker"
                                autocomplete="off">
                        </div>

                        <small class="form-text text-muted d-block mt-2">
                            <i class="icon-copy dw dw-information"></i>
                            Choose a single date or a date range for data export
                        </small>

                        <!-- Hidden fields -->
                        <input type="hidden" name="start_date" id="start_date">
                        <input type="hidden" name="end_date" id="end_date">
                    </div>

                    <!-- Export Preview Info -->
                    <div class="alert alert-light border" role="alert">
                        <div class="d-flex align-items-start">
                            <div>
                                <strong class="d-block mb-1"><i class="fa fa-info-circle"></i> Note:</strong>
                                <small class="text-muted">Leave dates empty to export all records. For large datasets
                                    (100k+ records), please use date filters or CSV format.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-lg" data-dismiss="modal" aria-label="Close">
                    Cancel
                </button>
                <button type="submit" form="exportForm" class="btn btn-primary btn-lg">
                    Export Now
                </button>
            </div>
        </div>
    </div>
</div>
