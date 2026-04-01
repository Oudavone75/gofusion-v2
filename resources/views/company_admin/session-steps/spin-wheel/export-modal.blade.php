<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="icon-copy dw dw-download text-primary me-2"></i>
                    Export SpinWheel Data
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pt-2">
                <p class="text-muted mb-4">Select your export criteria to download the SpinWheel
                    attempted users data.</p>

                <form action="{{ route('company_admin.steps.spin-wheel.export', $go_session_step_id) }}" method="GET" id="exportForm">
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

                    <!-- Date Range Picker -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="icon-copy dw dw-calendar-1"></i>
                            Date Range <span class="text-danger">*</span>
                        </label>
                        <div class="date-input-wrapper">
                            <input class="form-control form-control-lg datetimepicker-range"
                                placeholder="Select start and end date" type="text" id="dateRangePicker"
                                autocomplete="off">
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            <i class="icon-copy dw dw-information"></i>
                            Select the date range for data export
                        </small>

                        <!-- Hidden fields -->
                        <input type="hidden" name="start_date" id="start_date">
                        <input type="hidden" name="end_date" id="end_date">
                    </div>

                    <!-- Reward Type Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="icon-copy dw dw-gift"></i>
                            Reward Type
                            <span class="badge bg-light text-secondary ms-2">Optional</span>
                        </label>
                        <select name="reward_type" class="form-select form-select-lg">
                            <option value="">🎯 All Reward Types</option>
                            <option value="bonus_leaves">🎁 Bonus Leaves</option>
                            <option value="promo_codes">🎫 Promo Codes</option>
                            <option value="video_url">🎬 Video URL</option>
                        </select>
                        <small class="form-text text-muted d-block mt-2">
                            <i class="icon-copy dw dw-information"></i>
                            Leave as "All" to export all reward types
                        </small>
                    </div>

                    <!-- Export Preview Info -->
                    <div class="alert alert-light border" role="alert">
                        <div class="d-flex align-items-start">
                            <div>
                                <strong class="d-block mb-1">Export Format</strong>
                                <small class="text-muted">Data will be exported as an Excel file (.xlsx)
                                    with all attempted user information.</small>
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
