@extends('company_admin.layout.main')

@section('title', 'Edit Challenge Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Edit Challenge Step',
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
                <form action="{{ route('company_admin.steps.challenges-step.update', $challenge_step->id) }}"
                    onsubmit="submitForm(event,this)" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Campaign*</label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectData(this,'session-select','Sessions','campaign_id')"
                                data-url="{{ route('company_admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($company_campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign', $challenge_step->goSessionStep->goSession->campaignSeason->id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Sessions*</label>
                            <select name="session" id="session-select"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Session</option>
                                @foreach ($campaign_sessions as $session)
                                    <option value="{{ $session->id }}"
                                        {{ old('session', $challenge_step->goSessionStep->goSession->id) == $session->id ? 'selected' : '' }}>
                                        {{ $session->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" name="points" value="{{ old('points', $challenge_step->points) }}"
                                class="form-control"
                                placeholder="Enter Points (1-300)">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
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
