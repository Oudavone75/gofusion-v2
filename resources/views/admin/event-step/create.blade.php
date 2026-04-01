@extends('admin.layout.main')

@section('title', 'Add Event Step')
<style>
    /* Keep readonly input looking normal */
    input[readonly].form-control {
        background-color: #fff !important;
        color: inherit !important;
        cursor: pointer; /* still looks clickable */
    }
</style>
@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Add Event Step', 'paths' => breadcrumbs()
                ])
            </div>
        </div>
        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.events.store') }}" onsubmit="submitForm(event,this,'/admin/events'+TYPE_QUERY_PARAM,true)"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Select Type (Campaign / Season)</label>
                        <select name="type" id="type-select" class="custom-select2 form-control" onchange="handleTypeChange(this)">
                            <option value="campaign" selected  >Campaign</option>
                            <option value="season"> Season</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-12 ml-0 company-select">
                        <label class="form-control-label">Company*</label>
                        <select name="company" id="company-select" onchange="getSelectData(this,'campaign-select','Campaigns','company_id')" data-url="{{ route('admin.get-company-campaigns',['company_id']) }}"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Company</option>
                            @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label campaign-select-label">Campaign*</label>
                        <select name="campaign" id="campaign-select" onchange="getSelectData(this,'session-select','Sessions','campaign_id')" data-url="{{ route('admin.get-campaign-sessions',['campaign_id']) }}"
                            class="custom-select2 form-control campaign-select">
                            <option value="" disabled selected>Select Campaign</option>
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Sessions*</label>
                        <select name="session" id="session-select"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Session</option>
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Event Name*</label>
                        <input name="event_name" id="event-name" class="form-control" placeholder="Enter event name" >
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label" >Event Type*</label>
                        <select name="event_type" id="event-select" class="custom-select2 form-control"  onchange="handleEventTypeChange(this)">
                            <option value="onsite" selected>Onsite</option>
                            <option value="online">Online</option>
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label" id="event-location-label">Event Location*</label>
                        <input name="event_location" id="event-location" class="form-control" placeholder="Enter event location" >
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Event Start Date*</label>
                        <input type="text" name="event_start_date"
                               class="form-control date-picker"
                               placeholder="Enter Event Start Date" autocomplete="off">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Event End Date*</label>
                        <input type="text" name="event_end_date"
                               class="form-control date-picker"
                               placeholder="Enter Event End Date"  autocomplete="off">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Points*</label>
                        <input type="number" name="points" value="{{ old('points') }}"
                            class="form-control"
                            placeholder="Enter Points (1-300)" step="1">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                     <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">AI Description*</label>
                        <input type="text" name="guideline_text"
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
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Description*</label>
                        <textarea name="description" class="form-control"
                            placeholder="Enter Description" rows="4">{{ old('description') }}</textarea>
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
