@extends('admin.layout.main')

@section('title', 'Edit Event Step')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Event Step', 'paths' => breadcrumbs()
                ])
            </div>
        </div>
        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.events.update', $event_step->id) }}" onsubmit="submitForm(event,this,'/admin/events?type={{!is_null($event_step->goSessionStep->goSession->campaignSeason->company_id) ? 'campaign' : 'season'}}',true)"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    @if(!is_null($event_step->goSessionStep->goSession->campaignSeason->company_id))
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Company*</label>
                        <select name="company" id="company-select" onchange="getSelectData(this,'campaign-select','Campaigns','company_id')" data-url="{{ route('admin.get-company-campaigns',['company_id']) }}"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Company</option>
                            @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company', $event_step->goSessionStep->goSession->campaignSeason->company_id) == $company->id ? 'selected' : '' }}>
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
                        <label class="form-control-label"> @if(!is_null($event_step->goSessionStep->goSession->campaignSeason->company_id)) Campaign* @else Season* @endif</label>
                        <select name="campaign" id="campaign-select" onchange="getSelectData(this,'session-select','Sessions','campaign_id')" data-url="{{ route('admin.get-campaign-sessions',['campaign_id']) }}"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Campaign</option>
                            @foreach ($company_campaigns as $campaign)
                            <option value="{{ $campaign->id }}"
                                {{ old('campaign', $event_step->goSessionStep->goSession->campaignSeason->id) == $campaign->id ? 'selected' : '' }}>
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
                                {{ old('session', $event_step->goSessionStep->goSession->id) == $session->id ? 'selected' : '' }}>
                                {{ $session->title }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>

                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Event Name*</label>
                            <input name="event_name" value="{{$event_step->event->title ?? ""}}" id="event-name" class="form-control" placeholder="Enter event name" >
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label" >Event Type*</label>
                            <select name="event_type" id="event-select" class="custom-select2 form-control"  onchange="handleEventTypeChange(this)">
                                <option value="onsite" @if(!is_null($event_step->event) && $event_step->event->event_type == "onsite")selected @else selected @endif>Onsite</option>
                                <option value="online" @if(!is_null($event_step->event) &&$event_step->event->event_type == "online") selected @endif>Online</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label" id="event-location-label">Event Location*</label>
                            <input name="event_location" id="event-location" value="{{$event_step->event->location ?? ""}}" class="form-control" placeholder="Enter event location" >
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Event Start Date*</label>
                            <input type="text" name="event_start_date" @if($event_step->event) value="{{\Carbon\Carbon::parse($event_step->event->start_date)->format('d F Y')}}" @endif
                                   class="form-control date-picker"
                                   placeholder="Enter Event Start Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Event End Date*</label>
                            <input type="text" name="event_end_date" @if($event_step->event) value="{{\Carbon\Carbon::parse($event_step->event->end_date)->format('d F Y')}}" @endif
                                   class="form-control date-picker"
                                   placeholder="Enter Event End Date"  autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Points*</label>
                        <input type="number" name="points" value="{{ old('points', $event_step->points) }}"
                            class="form-control"
                            placeholder="Enter Points (1-300)" step="1">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">AI Description*</label>
                        <input type="text" name="guideline_text" value="{{ old('guideline_text', $event_step->guideline_text) }}"
                            class="form-control"
                            placeholder="Enter one line AI description">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label for="image" class="form-control-label">Sample Event Image</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input"
                                onchange="updateFileName(this)">
                            <label class="custom-file-label" for="image">Choose file</label>
                            <div class="form-control-feedback mt-2 d-none"></div>
                            @if($event_step->image_path)
                            <a href="{{ asset($event_step->image_path) }}" target="_blank"> View Uploaded Image</a>
                            @endif
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Description*</label>
                        <textarea name="description" class="form-control"
                            placeholder="Enter Description" rows="4">{{ old('description', $event_step->description) }}</textarea>
                        <div class="form-control-feedback mt-2 d-none"></div>
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
