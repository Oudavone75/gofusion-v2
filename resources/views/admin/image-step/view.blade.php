@extends('admin.layout.main')

@section('title', 'View Challenges to Complete Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View Challenges to Complete Step', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    @if (!is_null($image_step->goSessionStep->goSession->campaignSeason->company_id))
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Company</label>
                            <p class="form-control-plaintext">
                                {{ $image_step->goSessionStep->goSession->campaignSeason->company->name ?? 'Not Available' }}
                            </p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">
                            @if (!is_null($image_step->goSessionStep->goSession->campaignSeason->company_id))
                                Campaign
                            @else
                                Season
                            @endif
                        </label>
                        <p class="form-control-plaintext">
                            {{ $image_step->goSessionStep->goSession->campaignSeason->title ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Session</label>
                        <p class="form-control-plaintext">{{ $image_step->goSessionStep->goSession->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Title</label>
                        <p class="form-control-plaintext">{{ $image_step->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Points</label>
                        <p class="form-control-plaintext">{{ $image_step->points }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Validation Mode</label>
                        <p class="form-control-plaintext">{{ ucfirst($image_step->mode) }}</p>
                    </div>
                    @if ($image_step->mode == 'photo')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">AI Description</label>
                        <p class="form-control-plaintext">{{ $image_step->guideline_text }}</p>
                    </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $image_step->description ?? '—' }}</p>
                    </div>
                    @if ($image_step->mode == 'photo')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($image_step->image_path))
                            <div>
                                <img src="{{ $image_step->image_path }}" alt="Challenges to Complete Step Image"
                                    class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($image_step->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                    @endif
                    @if ($image_step->mode == 'video')
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-control-label font-weight-bold">Video URL</label>
                        <p class="form-control-plaintext mb-0">
                            @if (!empty($image_step->video_url))
                                <a href="{{ $image_step->video_url }}" target="_blank" rel="noopener noreferrer"
                                    class="text-primary" style="text-decoration: underline;">
                                    {{ $image_step->video_url }}
                                </a>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    @endif
                    @if ($image_step->mode == 'video')
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-control-label font-weight-bold">Keywords</label>
                        <p class="form-control-plaintext mb-0">
                            @if (!empty($image_step->keywords) && count($image_step->keywords) > 0)
                                @foreach($image_step->keywords as $keyword)
                                    <span class="badge badge-primary mr-1 mb-1" style="font-size: 14px;">{{ $keyword }}</span>
                                @endforeach
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
