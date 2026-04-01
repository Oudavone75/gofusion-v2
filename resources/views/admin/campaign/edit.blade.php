@extends('admin.layout.main')

@section('title', !is_null($campaign->company_id) ? 'Edit Campaign' : 'Edit Season')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => !is_null($campaign->company_id) ? 'Edit Campaign' : 'Edit Season',
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
                <form action="{{ route('admin.campaign.update', $campaign->id) }}"
                    onsubmit="submitForm(event,this,'/admin/campaign/list?type={{ !is_null($campaign->company_id) ? 'campaign' : 'season' }}',true)"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        @if (!is_null($campaign->company_id))
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Company*</label>
                                <select name="company_id" id="company-select" class="custom-select2 form-control">
                                    <option value="" disabled selected>Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id', $campaign->company_id) == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <div class="col-md-6 col-sm-12 ml-0 department-select">
                                <label for="department-select">Department*</label>
                                <select name="departments[]" id="department-select" class="selectpicker form-control"
                                    data-size="5" data-actions-box="true" data-selected-text-format="count" multiple>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ $campaign->departments->contains($department->id) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <input type="hidden" name="type" value="campaign">
                        @else
                            <input type="hidden" name="type" value="season">
                        @endif
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title', $campaign->title) }}"
                                class="form-control" placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Start Date*</label>
                            <input type="text" name="start_date" autocomplete="off"
                                value="{{ old('start_date', \Carbon\Carbon::parse($campaign->start_date)->format('d F Y')) }}"
                                class="form-control date-picker" placeholder="Enter Start Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-control-label">End Date*</label>
                                    <input type="text" name="end_date" autocomplete="off"
                                        value="{{ old('end_date', \Carbon\Carbon::parse($campaign->end_date)->format('d F Y')) }}"
                                        class="form-control date-picker" placeholder="Enter End Date" autocomplete="off">
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-control-label">Rewards / Incentives</label>
                                    <textarea name="custom_reward" class="form-control" placeholder="Enter rewards / incentives">{{ old('custom_reward', $campaign->custom_reward) }}</textarea>
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control" placeholder="Enter Description" rows="6">{{ old('description', $campaign->description) }}</textarea>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="row" id="department-wrapper">
                        @foreach ($campaign->campaignsSeasonsRewardRanges as $key => $range)
                            <div class="col-md-12 col-sm-12 mt-2 d-flex align-items-center reward-field">
                                <div class="col-md-3">
                                    <label class="form-control-label">From Ranking*</label>
                                    <input type="number" name="from_ranking[]"
                                        value="{{ old('from_ranking[]') ?? $range->rank_start }}" class="form-control"
                                        required placeholder="Enter From Ranking" min="1">
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-control-label">To Ranking*</label>
                                    <input type="number" name="to_ranking[]"
                                        value="{{ old('to_ranking[]') ?? $range->rank_end }}" class="form-control" required
                                        placeholder="Enter To Ranking" min="2">
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-control-label">Reward*<small>(In €)</small> </label>
                                    <input type="number" name="reward[]" value="{{ old('reward[]') ?? $range->reward }}"
                                        class="form-control" required min="1" placeholder="Enter Reward">
                                    <div class="form-control-feedback mt-2 d-none"></div>
                                </div>

                                @if ($loop->last)
                                    <div class="col-md-1" style="margin-top:27px;">
                                        <button type="button" class="btn btn-success" id="add-reward-field">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                                @if ($loop->first)
                                    <div class="col-md-1" style="margin-top:27px;"></div>
                                @endif
                                @if (!$loop->first)
                                    <div class="col-md-2" style="margin-top:27px;">
                                        <button type="button" class="btn btn-danger remove-reward-field">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
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
