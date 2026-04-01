@extends('admin.layout.main')

@section('title', 'Contact Request Details')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Contact Request Details',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <h3>Company Details</h3>
                <br>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold"> Name</label>
                        <p class="form-control-plaintext">{{ $company_contact->first_name ?? '—' }}
                            {{ $company_contact->last_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold"> Email</label>
                        <p class="form-control-plaintext">{{ $company_contact->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Comment</label>
                        <p class="form-control-plaintext">{{ $company_contact->comment ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Created At</label>
                        <p class="form-control-plaintext">{{ $company_contact->created_at->format('d F Y') ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <h3>Contact Person Details</h3>
                <br>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold"> Name</label>
                        <p class="form-control-plaintext">{{ $company_contact->user->full_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold"> Email</label>
                        <p class="form-control-plaintext">{{ $company_contact->user->email ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
