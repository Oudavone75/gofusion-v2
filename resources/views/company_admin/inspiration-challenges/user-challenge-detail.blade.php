@extends('company_admin.layout.main')

@section('title', 'View User Details')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="page-header">
            <div class="row">
                @include('company_admin.components.page-title', [
                    'page_title' => 'View User Details',
                    'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <div class="row">
                @if (!is_null($user->company_id))
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">{{ $user->company->name ?? 'Not Available' }}</p>
                    </div>
                @endif
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Department</label>
                    <p class="form-control-plaintext">{{ $user->department->name ?? 'Not Available' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Name</label>
                    <p class="form-control-plaintext">{{ $user->full_name ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Email</label>
                    <p class="form-control-plaintext">{{ $user->email ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Challenge Details',
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Title</label>
                        <p class="form-control-plaintext">{{ $inspiration_challenge->title ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $inspiration_challenge->description ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">AI Description</label>
                        <p class="form-control-plaintext">{{ $inspiration_challenge->guideline_text ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Points</label>
                        <p class="form-control-plaintext">{{ $inspiration_challenge->attempted_points ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext">
                            @if (isset($inspiration_challenge) && $inspiration_challenge->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif(isset($inspiration_challenge) && $inspiration_challenge->status == 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @elseif(isset($inspiration_challenge) && $inspiration_challenge->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-secondary">N/A</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Category</label>
                        <p class="form-control-plaintext">{{ $inspiration_challenge->category->name ?? '—' }}</p>
                    </div>
                    @if ($inspiration_challenge->event && $inspiration_challenge->category->name === 'Event')
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Name</label>
                            <p class="form-control-plaintext">{{ $inspiration_challenge->event->title ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Type</label>
                            <p class="form-control-plaintext">{{ $inspiration_challenge->event->event_type ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Location</label>
                            <p class="form-control-plaintext">{{ $inspiration_challenge->event->location ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Start Date</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($inspiration_challenge->event->start_date)->format('d F Y') ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event End Date</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($inspiration_challenge->event->end_date)->format('d F Y') ?? '—' }}</p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($inspiration_challenge->image_path))
                            <div>
                                <img src="{{ $inspiration_challenge->image_path }}" alt="Inspiration Challenge Image" class="img-thumbnail"
                                    style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($inspiration_challenge->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                    @if ($inspiration_challenge->category->name === 'Image')
                        <div class="col-md-6 col-sm-12 mt-2">
                            <label class="form-control-label font-weight-bold">Video URL</label>
                            <p class="form-control-plaintext mb-0">
                                @if (!empty($inspiration_challenge->video_url))
                                    <a href="{{ $inspiration_challenge->video_url }}" target="_blank" rel="noopener noreferrer"
                                        class="text-primary" style="text-decoration: underline;">
                                        {{ $inspiration_challenge->video_url }}
                                    </a>
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
