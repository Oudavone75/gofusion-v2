@extends('admin.layout.main')

@section('title', 'View Session')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View Session',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    @if(!is_null($session->campaignSeason->company_id))
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">{{ $session->campaignSeason->company->name ?? 'Not Available' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Department</label>
                        <p class="form-control-plaintext">{{ $session->campaignSeason->department->name ?? 'Not Available' }}</p>
                    </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold"> @if(!is_null($session->campaignSeason->company_id)) Campaign @else Season @endif Title</label>
                        <p class="form-control-plaintext">{{ $session->campaignSeason->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Session Title</label>
                        <p class="form-control-plaintext">{{ $session->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Status</label>
                    <p class="form-control-plaintext text-capitalize">
                        <span class="badge badge-{{ $session->status == 'active' ? 'success' : ($session->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                            {{ $session->status ?? '—' }}
                        </span>
                    </p>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection
