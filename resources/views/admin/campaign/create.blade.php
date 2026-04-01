@extends('admin.layout.main')

@section('title', 'Add Campaign / Season')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Add Campaign / Season',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.campaign.store') }}"
                    onsubmit="submitForm(event,this,'/admin/campaign/list'+TYPE_QUERY_PARAM,true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control"
                                onchange="handleTypeChange(this,true)">
                                <option value="campaign" selected>Campaign</option>
                                <option value="season"> Season</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 company-select">
                            <label class="form-control-label">Company*</label>
                            <select name="company_id" id="company-select" class="custom-select2 form-control">
                                <option value="" disabled selected>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0 department-select">
                            <label for="department-select">Department*</label>
                            <select name="departments[]" id="department-select" class="selectpicker form-control"
                                data-size="5" data-actions-box="true" data-selected-text-format="count" multiple >
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
                            <input type="text" name="start_date" autocomplete="off" value="{{ old('start_date') }}"
                                class="form-control date-picker" placeholder="Enter Start Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-control-label">End Date*</label>
                                    <input type="text" autocomplete="off" name="end_date" value="{{ old('end_date') }}"
                                        class="form-control date-picker" placeholder="Enter End Date" autocomplete="off">
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control" placeholder="Enter Description" rows="6">{{ old('description') }}</textarea>
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
@endsection
@push('scripts')
    <script>
        $('#department-select').on('shown.bs.select', function () {
            const $this = $(this);
            const dropdown = $this.parent().find('.dropdown-menu');

            // Handle "Select All"
            dropdown.find('.bs-select-all').off('click').on('click', function () {
                setTimeout(() => {
                $this.selectpicker('toggle'); // Close dropdown AFTER select all
                }, 200);
            });

            // Handle "Deselect All"
            dropdown.find('.bs-deselect-all').off('click').on('click', function () {
                setTimeout(() => {
                $this.selectpicker('toggle'); // Close dropdown AFTER deselect all
                }, 200);
            });
        });
    </script>
@endpush
