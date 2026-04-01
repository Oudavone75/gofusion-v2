@extends('company_admin.layout.main')

@section('title', 'View Event Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Event Step',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">
                            {{ $event_step->goSessionStep->goSession->campaignSeason->company->name ?? 'Not Available' }}
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Campaign</label>
                        <p class="form-control-plaintext">
                            {{ $event_step->goSessionStep->goSession->campaignSeason->department->name ?? 'Not Available' }}
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Session</label>
                        <p class="form-control-plaintext">{{ $event_step->goSessionStep->goSession->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Event Name</label>
                        <p class="form-control-plaintext">{{ $event_step->event->title ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Event Type</label>
                        <p class="form-control-plaintext">{{ $event_step->event->event_type ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Event Location</label>
                        <p class="form-control-plaintext">{{ $event_step->event->location ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Event Start Date</label>
                        <p class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($event_step->event->start_date)->format('d F Y') ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Event End Date</label>
                        <p class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($event_step->event->end_date)->format('d F Y') ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Points</label>
                        <p class="form-control-plaintext">{{ $event_step->points }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $event_step->description }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">AI Description</label>
                        <p class="form-control-plaintext">{{ $event_step->guideline_text }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($event_step->image_path))
                            <div>
                                <img src="{{ $event_step->image_path }}" alt="Event Step Image" class="img-thumbnail"
                                    style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($event_step->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
