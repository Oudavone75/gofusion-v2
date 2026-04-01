@extends('admin.layout.main')

@section('title', 'Add Department')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Add Department',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.department.store') }}" onsubmit="submitForm(event,this,'department/list')" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Name*</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control" placeholder="Enter Name">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company*</label>
                        <select name="company_id" class="custom-select2 form-control">
                            <option value="" disabled selected>Select Type</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id')==$company->id ? 'selected' : ''
                                }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
