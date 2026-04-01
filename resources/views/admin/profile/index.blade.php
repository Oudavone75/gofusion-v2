@extends('admin.layout.main')

@section('title', 'Profile')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Profile',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.profile.update', auth('admin')->user()->id ) }}" onsubmit="submitForm(event,this,'profile')" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', auth('admin')->user()->name) }}"
                            class="form-control" placeholder="Enter Name">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth('admin')->user()->email) }}"
                            class="form-control"
                            placeholder="Enter Email" disabled>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label for="image" class="form-control-label">Logo</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input"
                                onchange="updateFileName(this)">
                            <label class="custom-file-label" for="image">Choose file</label>
                            <div class="form-control-feedback mt-2 d-none"></div>
                            @if(auth('admin')->user()->image_path)
                            <a href="{{ asset(auth('admin')->user()->image_path) }}" target="_blank"> View Uploaded Image</a>
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
