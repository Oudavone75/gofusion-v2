@extends('admin.layout.main')

@section('title', 'Performance Export')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', ['page_title' => 'Performance Export'])
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h5 class="h5 mb-3">Export Performance Data</h5>
                    <p class="text-muted mb-4">
                        Select a campaign and optional date range to export quiz and video performance data to Excel.
                        The export will contain 3 sheets: Quiz Score, Video Score, and Global Score.
                    </p>

                    <div id="exportAlert" class="alert alert-dismissible fade show d-none" role="alert">
                        <span id="exportAlertMessage"></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="exportForm">
                        @csrf

                        <div class="form-group">
                            <label class="form-label fw-semibold">
                                Campaign / Season <span class="text-danger">*</span>
                            </label>
                            <select name="campaign_season_id" id="campaign_season_id" class="form-control selectpicker"
                                data-live-search="true" required>
                                <option value="">Select Campaign</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">
                                        {{ $campaign->title }}
                                        ({{ \Carbon\Carbon::parse($campaign->start_date)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($campaign->end_date)->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('campaign_season_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="icon-copy dw dw-calendar-1"></i>
                                Date Range (Optional)
                            </label>

                            <div class="date-input-wrapper">
                                <input class="form-control form-control-lg datetimepicker-range"
                                    placeholder="Select date range (optional)" type="text" id="dateRangePicker"
                                    autocomplete="off">
                            </div>

                            <small class="form-text text-muted d-block mt-2">
                                <i class="icon-copy dw dw-information"></i>
                                Leave empty to export all records for the selected campaign.
                            </small>

                            <input type="hidden" name="start_date" id="start_date">
                            <input type="hidden" name="end_date" id="end_date">
                        </div>

                        <div class="alert alert-light border" role="alert">
                            <div class="d-flex align-items-start">
                                <div>
                                    <strong class="d-block mb-1"><i class="fa fa-info-circle"></i> Export Info:</strong>
                                    <small class="text-muted">
                                        The Excel file will contain 3 sheets:<br>
                                        <strong>Quiz Score</strong> - Employee name, question, answer, score per
                                        response<br>
                                        <strong>Video Score</strong> - Video URL, user comment, keywords, matched concepts,
                                        score<br>
                                        <strong>Global Score</strong> - Per employee quiz score %, video score %, global score %
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="exportBtn">
                                <i class="icon-copy dw dw-download"></i> Export Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#exportForm').on('submit', function (e) {
                e.preventDefault();

                var campaignSeasonId = $('#campaign_season_id').val();
                if (!campaignSeasonId) {
                    showAlert('danger', 'Please select a campaign.');
                    return;
                }

                var $btn = $('#exportBtn');
                var originalText = $btn.html();
                $btn.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Exporting... Please wait'
                );
                hideAlert();

                $.ajax({
                    url: '{{ route("admin.performance.export") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        campaign_season_id: campaignSeasonId,
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val()
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob, status, xhr) {
                        var filename = 'performance_export.xlsx';
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            var matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                            if (matches && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();

                        showAlert('success', 'Export downloaded successfully!');
                        $btn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        var message = 'Export failed. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                        } else if (xhr.responseType === 'blob' && xhr.response) {
                            var reader = new FileReader();
                            reader.onload = function () {
                                try {
                                    var json = JSON.parse(reader.result);
                                    if (json.error) message = json.error;
                                } catch (e) {}
                                showAlert('danger', message);
                            };
                            reader.readAsText(xhr.response);
                            $btn.prop('disabled', false).html(originalText);
                            return;
                        }
                        showAlert('danger', message);
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function showAlert(type, message) {
                $('#exportAlert')
                    .removeClass('d-none alert-success alert-danger')
                    .addClass('alert-' + type)
                    .find('#exportAlertMessage').text(message);
            }

            function hideAlert() {
                $('#exportAlert').addClass('d-none');
            }
        });
    </script>
@endpush
