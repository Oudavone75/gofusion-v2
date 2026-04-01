@extends('admin.layout.main')

@section('title', 'Add Company')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Add Company',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.company.store') }}" onsubmit="submitForm(event,this,'company/list')" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Name*</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control" placeholder="Enter Name">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Email*</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control"
                            placeholder="Enter Email">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Address*</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="form-control"
                            placeholder="Enter Address">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Registration Date*</label>
                        <input type="text" name="registration_date" value="{{ old('registration_date') }}"
                            class="form-control date-picker"
                            placeholder="Enter Registration Date" autocomplete="off">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company Type*</label>
                        <select name="type" class="form-control">
                            <option value="" disabled selected>Select Type</option>
                            @foreach($company_modes as $modes)
                            <option value="{{ $modes->id }}" {{ old('type')==$modes->id ? 'selected' : ''
                                }}>
                                {{ $modes->name == 'Event' ? 'Media Impact' : $modes->name }}
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
                        </div>
                    </div>
                </div>
                <div class="row" id="department-wrapper">
                    <div class="col-md-6 col-sm-12 mt-2 d-flex align-items-center department-field">
                        <div class="col-md-10">
                            <label class="form-control-label">Department Name*</label>
                            <input type="text" name="department[]" value="{{ old('department[]') }}"
                                class="form-control"
                                placeholder="Enter Department Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-2" style="margin-top:27px;">
                            <button type="button" class="btn btn-success" id="add-department-field">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
