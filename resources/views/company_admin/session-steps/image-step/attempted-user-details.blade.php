@extends('company_admin.layout.main')

@section('title', 'View Attempted User Details')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="page-header">
            <div class="row">
                @include('company_admin.components.page-title', [
                    'page_title' => 'View Attempted User Details',
                    'paths' => breadcrumbs(),
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
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Points</label>
                    <p class="form-control-plaintext">{{ $image_challenge->points ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Status</label>
                    <p class="form-control-plaintext">
                        @if (isset($image_challenge) && $image_challenge->status == 'completed')
                            <span class="badge badge-success">Completed</span>
                        @elseif(isset($image_challenge) && $image_challenge->status == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @elseif(isset($image_challenge) && $image_challenge->status == 'appealing')
                            <span class="badge badge-warning">Appealing</span>
                        @else
                            <span class="badge badge-secondary">N/A</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Validation Mode</label>
                    <p class="form-control-plaintext">{{ ucfirst($image_challenge->goSessionStep->imageSubmissionGuideline->mode) }}</p>
                </div>
                @if ($image_challenge->goSessionStep->imageSubmissionGuideline->mode == 'video')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Comment</label>
                        <p class="form-control-plaintext">{{ $image_challenge->comment ?? 'Not Available' }}</p>
                    </div>
                @endif
                @if ($image_challenge->goSessionStep->imageSubmissionGuideline->mode == 'photo')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">AI Description</label>
                        <p class="form-control-plaintext">{{ $image_challenge->goSessionStep->imageSubmissionGuideline->guideline_text }}</p>
                    </div>
                @endif
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Description</label>
                    <p class="form-control-plaintext">{{ $image_challenge->goSessionStep->imageSubmissionGuideline->description ?? '—' }}</p>
                </div>
                @if ($image_challenge->goSessionStep->imageSubmissionGuideline->mode == 'photo')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($image_challenge->goSessionStep->imageSubmissionGuideline->image_path))
                            <div>
                                <img src="{{ $image_challenge->goSessionStep->imageSubmissionGuideline->image_path }}" alt="Challenges to Complete Step Image"
                                    class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($image_challenge->goSessionStep->imageSubmissionGuideline->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                @endif
                @if ($image_challenge->goSessionStep->imageSubmissionGuideline->mode == 'photo')
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Image Upload By User</label>
                        @if (!empty($image_challenge->goSessionStep->imageSubmissionGuideline->image_path))
                            <div>
                                <img src="{{ $image_challenge->file_name }}" alt="Challenges to Complete Step Image"
                                    class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($image_challenge->goSessionStep->imageSubmissionGuideline->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                @endif
                @if ($image_challenge->goSessionStep->imageSubmissionGuideline->mode == 'video')
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-control-label font-weight-bold">Video URL</label>
                        <p class="form-control-plaintext mb-0">
                            @if (!empty($image_challenge->goSessionStep->imageSubmissionGuideline->video_url))
                                <a href="{{ $image_challenge->goSessionStep->imageSubmissionGuideline->video_url }}" target="_blank" rel="noopener noreferrer"
                                    class="text-primary" style="text-decoration: underline;">
                                    {{ $image_challenge->goSessionStep->imageSubmissionGuideline->video_url }}
                                </a>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                @endif
                @if ($image_challenge->rejection_reason)
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Rejection Reason</label>
                        <p class="form-control-plaintext">{{ $image_challenge->rejection_reason ?? 'Not Available' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
