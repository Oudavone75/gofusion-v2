@extends('company_admin.layout.main')

@section('title', 'Add Campaign')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Add Campaign',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.campaigns.store') }}"
                    onsubmit="submitForm(event,this,'/company-admin/campaigns/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label for="department-select">Department*</label>
                            <select name="departments[]" id="department-select" class="selectpicker form-control"
                                data-size="5" data-actions-box="true"
                                data-selected-text-format="count" multiple>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                                placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Start Date*</label>
                            <input type="text" name="start_date" value="{{ old('start_date') }}"
                                class="form-control date-picker" placeholder="Start Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">End Date*</label>
                            <input type="text" name="end_date" value="{{ old('end_date') }}"
                                class="form-control date-picker" placeholder="Enter End Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control" placeholder="Enter Description" rows="4">{{ old('description') }}</textarea>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-control-label">Rewards / Incentives</label>
                            <textarea name="custom_reward" class="form-control" placeholder="Enter rewards / incentives">{{ old('custom_reward') }}</textarea>
                        </div>
                    </div>
                    <div class="row" id="department-wrapper">
                        <div class="col-md-12 col-sm-12 mt-2 d-flex align-items-center reward-field">
                            <div class="col-md-3">
                                <label class="form-control-label">From Ranking*</label>
                                <input type="number" name="from_ranking[]" value="{{ old('from_ranking[]') }}"
                                    class="form-control" placeholder="Enter From Ranking" min="1">
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-control-label">To Ranking*</label>
                                <input type="number" name="to_ranking[]" value="{{ old('to_ranking[]') }}"
                                    class="form-control" placeholder="Enter To Ranking" min="2">
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-control-label">Reward*<small>(In €)</small> </label>
                                <input type="number" name="reward[]" value="{{ old('reward[]') }}" class="form-control"
                                    min="1" placeholder="Enter Reward">
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <div class="col-md-1" style="margin-top:27px;">
                                <button type="button" class="btn btn-success" id="add-reward-field">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
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

        /* Validation error styling */
        .has-error label,
        .has-error > label {
            color: #dc3545 !important;
        }

        .form-control-feedback {
            color: #dc3545;
            font-size: 15px;
        }

        .form-control-danger,
        .bootstrap-select.has-danger .dropdown-toggle {
            border-color: #dc3545 !important;
        }
    </style>
@endsection
@push('scripts')
    {{-- <script>
        $(document).ready(function() {
            $("#department-select").selectpicker({
                noneSelectedText: "Select Department",
                liveSearch: true,
                maxOptions: 1
            });
        });

        $('#department-select').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            var selected = $(this).val();

            // Always keep only the last selected value
            if (selected && selected.length > 1) {
                $(this).selectpicker('val', [selected[selected.length - 1]]);
            }
        });
    </script> --}}
@endpush
