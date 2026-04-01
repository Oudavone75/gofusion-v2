@extends('admin.layout.main')

@section('title', 'Edit Session')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Session',
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
            <form action="{{ route('admin.sessions.update', $session->id) }}"
                onsubmit="submitForm(event,this,'/admin/sessions?type={{!is_null($session->campaignSeason->company_id) ? "campaign" : "season"}}',true)" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    @if(!is_null($session->campaignSeason->company_id))
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Company*</label>
                        <select name="company" id="company-select" onchange="getSelectData(this,'campaign-select','Campaigns','company_id')" data-url="{{ route('admin.get-company-campaigns',['company_id']) }}"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Company</option>
                            @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company', $session->campaignSeason->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                        <input type="hidden" name="type" value="campaign">
                    @else
                        <input type="hidden" name="type" value="season">
                    @endif
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">@if(!is_null($session->campaignSeason->company_id)) Campaign* @else Season* @endif</label>
                        <select name="campaign" id="campaign-select" onchange="getSelectData(this,'session-select','Sessions','campaign_id')" data-url="{{ route('admin.get-campaign-sessions',['campaign_id']) }}"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Campaign</option>
                            @foreach ($company_campaigns as $campaign)
                            <option value="{{ $campaign->id }}"
                                {{ old('campaign', $session->campaignSeason->id) == $campaign->id ? 'selected' : '' }}>
                                {{ $campaign->title }}
                            </option>
                            @endforeach

                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Title*</label>
                        <input type="text" name="title" value="{{ old('title', $session->title) }}"
                            class="form-control"
                            placeholder="Enter Title">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
