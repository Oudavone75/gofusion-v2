@extends('company_admin.layout.main')

@section('title', 'Import File')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row d-flex justify-content-between">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Import File',
                    ])

                    <a href="{{ asset('SampleImportFile.xlsx') }}" download class="btn btn-primary mr-10">
                        <i class="icon-copy dw dw-download"></i> Download Sample File
                    </a>
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <strong>Import Your Content</strong>
                <p>You have 3 ways to add content in Go Fusion:</p>
                <ul class="p-3">
                    <li>1. Use AI to automatically generate quizzes, challenges, or events.</li>
                    <li>2. Download and fill in an Excel file (template above) with your own data.</li>
                    <li>3. Select from the library of already available content.</li>
                </ul>
                <p>👉 With the Excel option, simply complete the provided file without changing its structure (Quiz,
                    ChallengeToComplete, SpinWheel, Survey / Feedback).</p>
            </div>
            <div class="pd-20 card-box mb-30">
                <div id="alert-box" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 mr-3">
                            <i class="icon-copy dw dw-warning" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <h4 class="alert-heading mb-2" id="alert-box-heading"></h4>
                            <div id="alert-box-message" class="mb-0"></div>
                            <div id="alert-box-details" class="mt-3 p-3 bg-white rounded small d-none">
                                <!-- Session numbers or additional details will be displayed here -->
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        style="position: absolute; top: 15px; right: 15px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('company_admin.import-file.import') }}" onsubmit="submitFileImportForm(event, this);"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="case" value="admin">
                    <div class="row">
                        <input type="hidden" name="company" value="{{ $company->id }}">
                        <input type="hidden" name="type" value="campaign">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label campaign-select-label">Campaign*</label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectDataImportFile(this,'session-select','Sessions','campaign_id','session')"
                                data-url="{{ route('company_admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control campaign-select">
                                <option value="" disabled selected>Select Campaign</option>
                                @if (count($company->campaignSeasons) > 0)
                                    @foreach ($company->campaignSeasons as $campaign)
                                        <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label for="session-select">Sessions*</label>
                            <select name="session[]" id="session-select" class="selectpicker form-control" data-size="5"
                                data-actions-box="true" data-selected-text-format="count" multiple required>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label for="image" class="form-control-label">Upload File*</label>
                            <div class="custom-file">
                                <input type="file" name="file" id="file" class="custom-file-input"
                                    onchange="updateFileName(this)" accept=".xls,.xlsx,.csv">
                                <label class="custom-file-label" for="image">Choose file</label>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                        </div>
                        <div id="generic-error" class="text-danger mt-2 d-none"></div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">
                                <i class="fa fa-save"></i> Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .btn-light {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #444;
        }

        .bootstrap-select>.dropdown-toggle.bs-placeholder,
        .bootstrap-select>.dropdown-toggle.bs-placeholder:active,
        .bootstrap-select>.dropdown-toggle.bs-placeholder:focus,
        .bootstrap-select>.dropdown-toggle.bs-placeholder:hover {
            color: #444;
        }

        .btn {
            font-family: 'Inter', sans-serif;
            letter-spacing: 0;
            font-weight: 400;
            padding: .438rem 1rem;
            font-size: 0.98rem;
        }

        .dropdown-menu {
            border: 1px solid #aaa;
        }

        #alert-box {
            position: relative;
            border-left: 4px solid #dc3545;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
            animation: slideDown 0.3s ease-out;
            padding-right: 50px;
            /* Space for close button */
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #alert-box .alert-heading {
            font-size: 1.1rem;
            font-weight: 600;
            color: #721c24;
            margin-bottom: 0.5rem;
        }

        #alert-box-message {
            font-size: 0.95rem;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        #alert-box-details {
            font-family: 'Courier New', monospace;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
            white-space: normal;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        #alert-box-details::-webkit-scrollbar {
            width: 8px;
        }

        #alert-box-details::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #alert-box-details::-webkit-scrollbar-thumb {
            background: #dc3545;
            border-radius: 4px;
        }

        #alert-box-details::-webkit-scrollbar-thumb:hover {
            background: #c82333;
        }

        #alert-box .close {
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        #alert-box .close:hover {
            opacity: 1;
        }

        /* Icon styling */
        #alert-box .dw-warning {
            color: #dc3545;
        }

        /* Ensure flex items don't overflow */
        #alert-box .flex-grow-1 {
            overflow: hidden;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('vendors/scripts/import-file.js') }}"></script>
    <script>
        $('#session-select').on('shown.bs.select', function() {
            const $this = $(this);
            const dropdown = $this.parent().find('.dropdown-menu');

            // Handle "Select All"
            dropdown.find('.bs-select-all').off('click').on('click', function() {
                setTimeout(() => {
                    $this.selectpicker('toggle'); // Close dropdown AFTER select all
                }, 200);
            });

            // Handle "Deselect All"
            dropdown.find('.bs-deselect-all').off('click').on('click', function() {
                setTimeout(() => {
                    $this.selectpicker('toggle'); // Close dropdown AFTER deselect all
                }, 200);
            });
        });
    </script>
@endpush
