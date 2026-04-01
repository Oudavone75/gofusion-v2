@extends('company_admin.layout.main')

@section('title', 'View Spin Wheel')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Spin Wheel',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Campaign</label>
                        <p class="form-control-plaintext">
                            {{ $spin_wheel->goSessionStep->goSession->campaignSeason->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Session</label>
                        <p class="form-control-plaintext">{{ $spin_wheel->goSessionStep->goSession->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Total Points</label>
                        <p class="form-control-plaintext">{{ $spin_wheel->points ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-control-label font-weight-bold">Video URL</label>
                        <p class="form-control-plaintext mb-0">
                            @if (!empty($spin_wheel->video_url))
                                <a href="{{ $spin_wheel->video_url }}" target="_blank" rel="noopener noreferrer"
                                    class="text-primary" style="text-decoration: underline;">
                                    {{ $spin_wheel->video_url }}
                                </a>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Bonus leaves</label>
                        <p class="form-control-plaintext">{{ $spin_wheel->bonus_leaves ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Surprising Gift</label>
                        <p class="form-control-plaintext">{{ $spin_wheel->promo_codes ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Total Attempted Users</label>
                        <p class="form-control-plaintext text-capitalize">{{ $spin_wheel->attempts->count() ?? '—' }}
                        </p>
                    </div>
                    {{-- <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Created By</label>
                        <p class="form-control-plaintext">
                            {{ $spin_wheel->createdBy->name ?? ($spin_wheel->createdByAdmin->name ?? '—') }}
                        </p>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
