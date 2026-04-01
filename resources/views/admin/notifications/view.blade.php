@extends('admin.layout.main')

@section('title', 'View Notification')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View Notification',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>

            <div class="pd-20 card-box mb-30">
                <div class="row">
                    @if (!is_null($notification->company_id))
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Company</label>
                            <p class="form-control-plaintext">
                                {{ $notification->company->name ?? 'Not Available' }}
                            </p>
                        </div>
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Departments</label>
                            <p class="form-control-plaintext">
                                {{ $notification->departments->isNotEmpty()
                                    ? $notification->departments->pluck('name')->join(', ')
                                    : 'Not Available' }}
                            </p>
                        </div>
                    @endif
                    @if (!is_null($notification->campaign_id))
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Campaign</label>
                            <p class="form-control-plaintext">
                                {{ $notification->campaign->title ?? 'Not Available' }}
                            </p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Notification Type</label>
                        <p class="form-control-plaintext text-capitalize">
                            <span class="badge badge-info d-inline px-2 py-1">
                                General
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Title</label>
                        <p class="form-control-plaintext">{{ $notification->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $notification->content ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext">
                            <span
                                class="badge badge-{{ $notification->status == 'sent' ? 'success' : ($notification->status == 'scheduled' ? 'warning' : 'secondary') }} d-inline px-2 py-1">
                                {{ ucfirst($notification->status ?? '—') }}
                            </span>
                        </p>
                    </div>
                    @if ($notification->notification_type === 'scheduled')
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Scheduled At</label>
                            <p class="form-control-plaintext">
                                {{ $notification->scheduled_at ? $notification->scheduled_at->format('d M Y, h:i A') : '—' }}
                            </p>
                        </div>
                    @endif
                    @if (!is_null($notification->sent_at))
                        <div class="col-md-6 col-sm-12 mt-2 ml-0">
                            <label class="form-control-label bold">Sent At</label>
                            <p class="form-control-plaintext">
                                {{ $notification->sent_at ? $notification->sent_at->format('d M Y, h:i A') : '—' }}
                            </p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Recipients</label>
                        <p class="form-control-plaintext">
                            {{ $notification->users_count ? $notification->users_count : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
