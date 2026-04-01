@extends('admin.layout.main')

@section('title', 'Edit Company')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Company',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.company.update', $company->id) }}" onsubmit="submitForm(event,this,'company/list')" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Name*</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}"
                            class="form-control" placeholder="Enter Name">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Email*</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                            class="form-control"
                            placeholder="Enter Email" disabled>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Address*</label>
                        <input type="text" name="address" value="{{ old('address', $company->address)}}"
                            class="form-control"
                            placeholder="Enter Address">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Registration Date*</label>
                        <input type="text" name="registration_date" value="{{ old('registration_date', \Carbon\Carbon::parse($company->registration_date)->format('d F Y')) }}"
                            class="form-control date-picker"
                            placeholder="Enter Registration Date" autocomplete="off">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Company Type*</label>
                        <select name="type" class="form-control">
                            <option value="" disabled selected>Select Type</option>
                            @foreach($company_modes as $mode)
                            <option value="{{ $mode->id }}" {{ old('type', $company->mode_id) == $mode->id ? 'selected' : '' }}>
                                {{ $mode->name == 'Event' ? 'Media Impact' : $mode->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label for="image" class="form-control-label">Company Logo</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input"
                                onchange="updateFileName(this)">
                            <label class="custom-file-label" for="image">Choose file</label>
                            <div class="form-control-feedback mt-2 d-none"></div>
                            @if($company->image)
                            <a href="{{ asset($company->image) }}" target="_blank"> View Uploaded Image</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
