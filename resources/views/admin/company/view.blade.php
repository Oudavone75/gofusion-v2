@extends('admin.layout.main')

@section('title', 'View News Category')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'View News Category',
                'paths' => breadcrumbs(),
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Name</label>
                    <p class="form-control-plaintext">{{ $company->name ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Email</label>
                    <p class="form-control-plaintext">{{ $company->email ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Code</label>
                    <p class="form-control-plaintext">{{ $company->code ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Type</label>
                    <p class="form-control-plaintext">{{ $company->mode->name ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Registration Date</label>
                    <p class="form-control-plaintext">
                        {{ \Carbon\Carbon::parse($company->registration_date)->format('d F Y') ?? '—' }}
                    </p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Status</label>
                    <p class="form-control-plaintext">
                        <span
                            class="badge badge-{{ $company->status == 'active' ? 'success' : ($company->status == 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Total Users</label>
                    <p class="form-control-plaintext">{{ $company->users_count ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Created By</label>
                    <p class="form-control-plaintext">{{ $company->admin->name ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <label class="form-control-label bold">Created At</label>
                    <p class="form-control-plaintext">{{ $company->created_at->format('d F Y') ?? '—' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mt-2 ml-0">
                    <label class="form-control-label bold">Logo</label>
                    @if (!empty($company->image))
                    <div>
                        <img src="{{ $company->image }}" alt="Event Step Image" class="img-thumbnail"
                            style="max-height: 150px;">
                    </div>
                    <a href="{{ asset($company->image) }}" target="_blank"> View Uploaded Image</a>
                    @else
                    <p class="form-control-plaintext">No Logo uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection