@extends('admin.layout.main')

@section('title', 'Edit Department')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                'page_title' => 'Edit Department',
                'paths' => breadcrumbs()
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('admin.department.update', $company_department->id) }}" onsubmit="submitForm(event,this,'department/list')" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Name*</label>
                        <input type="text" name="name" value="{{ old('name', $company_department->name) }}"
                            class="form-control" placeholder="Enter Name">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Company</label>
                        <input type="text" name="company_id" value="{{ old('company_id', $company_department->company->name) }}"
                            class="form-control" readonly>
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
