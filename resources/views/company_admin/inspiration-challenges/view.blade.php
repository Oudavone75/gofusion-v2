@extends('company_admin.layout.main')

@section('title', 'View Inspiration Challenge')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Inspiration Challenge',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Theme</label>
                        <p class="form-control-plaintext">{{ $challenge->theme->french_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">{{ $challenge->company->name ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Departments</label>
                        <p class="form-control-plaintext">
                            {{ $challenge->departments->isNotEmpty()
                                    ? $challenge->departments->pluck('name')->join(', ')
                                    : 'Not Available' }}
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Category</label>
                        <p class="form-control-plaintext">{{ $challenge->category->name ?? '—' }}</p>
                    </div>
                    @if ($challenge->category->name === 'Image')
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Validation Mode</label>
                            <p class="form-control-plaintext">{{ ucfirst($challenge->mode) }}</p>
                        </div>
                    @endif
                    @if ($challenge->event && $challenge->category->name === 'Event')
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Name</label>
                            <p class="form-control-plaintext">{{ $challenge->event->title ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Type</label>
                            <p class="form-control-plaintext">{{ $challenge->event->event_type ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Location</label>
                            <p class="form-control-plaintext">{{ $challenge->event->location ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event Start Date</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($challenge->event->start_date)->format('d F Y') ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Event End Date</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($challenge->event->end_date)->format('d F Y') ?? '—' }}</p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext text-capitalize">
                            <span
                                class="badge badge-{{ $challenge->status == 'approved' ? 'success' : ($challenge->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                                {{ $challenge->status ?? '—' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Points</label>
                        <p class="form-control-plaintext">{{ $challenge->attempted_points ?? 0 }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Title</label>
                        <p class="form-control-plaintext">{{ $challenge->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $challenge->description ?? '—' }}</p>
                    </div>
                    @if ($challenge->mode == 'photo')
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label bold">AI Description</label>
                            <p class="form-control-plaintext">{{ $challenge->guideline_text ?? '—' }}</p>
                        </div>
                    @endif
                    @if (!empty($challenge->image_path) && $challenge->mode == 'photo')
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($challenge->image_path))
                            <div>
                                <img src="{{ $challenge->image_path }}" alt="Inspiration Challenge Image"
                                    class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($challenge->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>
                    @endif
                    @if ($challenge->category->name === 'Image' && $challenge->mode == 'video')
                        <div class="col-md-6 col-sm-12 mt-2">
                            <label class="form-control-label font-weight-bold">Video URL</label>
                            <p class="form-control-plaintext mb-0">
                                @if (!empty($challenge->video_url))
                                    <a href="{{ $challenge->video_url }}" target="_blank" rel="noopener noreferrer"
                                        class="text-primary" style="text-decoration: underline;">
                                        {{ $challenge->video_url }}
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

@push('scripts')
    <script>
        function disableSubmitButton(form) {
            const button = form.querySelector('#submit-button');
            if (button) {
                button.disabled = true;
                button.innerText = 'Submitting...';
            }
        }
    </script>
@endpush
