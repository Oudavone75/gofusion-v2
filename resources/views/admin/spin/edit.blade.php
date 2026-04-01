@extends('admin.layout.main')

@section('title', 'Edit SpinWheel Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Edit SpinWheel Step', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.spin.update', $spin_wheel->id) }}" onsubmit="submitForm(event,this,'/admin/spin-wheel/list?type={{!is_null($spin_wheel->goSessionStep->goSession->campaignSeason->company_id) ? 'campaign' : 'season'}}',true)" method="POST"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="row">
                        @if(!is_null($spin_wheel->goSessionStep->goSession->campaignSeason->company_id))
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Company*</label>
                            <select name="company" id="company-select"
                                onchange="getSelectData(this,'campaign-select','Campaigns','company_id')"
                                data-url="{{ route('admin.get-company-campaigns', ['company_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Company</option>
                                @foreach ($companies as $company)
                                    <option
                                        {{ $spin_wheel->goSessionStep->goSession->campaignSeason->company_id === $company->id ? 'selected' : '' }}
                                        value="{{ $company->id }}" {{ old('company') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                            <input type="hidden" name="type" value="campaign">
                        @else
                            <input type="hidden" name="type" value="season">
                        @endif
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label"> @if(!is_null($spin_wheel->goSessionStep->goSession->campaignSeason->company_id)) Campaign* @else Season* @endif </label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectData(this,'session-select','Sessions','campaign_id')"
                                data-url="{{ route('admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($company_campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign', $spin_wheel->goSessionStep->goSession->campaignSeason->id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach

                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Sessions*</label>
                            <select name="session" id="session-select"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Session</option>
                                @foreach ($campaign_sessions as $session)
                                    <option value="{{ $session->id }}"
                                        {{ old('session', $spin_wheel->goSessionStep->goSession->id) == $session->id ? 'selected' : '' }}>
                                        {{ $session->title }}
                                    </option>
                                @endforeach

                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Video URL*</label>
                            <input type="text" name="video_url" value="{{ $spin_wheel->video_url ?? old('video_url') }}"
                                class="form-control"
                                placeholder="Video URL (https://www.youtube.com/watch?v=dQw4w9WgXcQ)" min="0"
                                step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Bonus Leaves*</label>
                            <input type="number" min="1" name="bonus_leaves"
                                value="{{ $spin_wheel->bonus_leaves ?? old('bonus_leaves') }}"
                                class="form-control"
                                placeholder="Enter Bonus Leaves" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Surprising Gift*</label>
                            <input type="text" name="promo_codes"
                                value="{{ $spin_wheel->promo_codes ?? old('promo_codes') }}"
                                class="form-control"
                                placeholder="Enter Surprising Gift" min="0" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" name="points" value="{{ $spin_wheel->points ?? old('points') }}"
                                class="form-control"
                                placeholder="Enter Points (1-300)" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
