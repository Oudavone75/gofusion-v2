@extends('company_admin.layout.main')

@section('title', 'Add News Feed')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Add News Feed',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.news-feed.store') }}"
                    onsubmit="submitForm(event, this);" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Category*</label>
                            <select name="category"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($categories['data'] as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="form-control"
                                placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Status*</label>
                            <select name="status" class="form-control">
                                <option value="" disabled {{ old('status') == '' ? 'selected' : '' }}>
                                    Select Status
                                </option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Publish</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Un-Publish
                                </option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label for="image" class="form-control-label">Image*</label>
                            <div class="custom-file">
                                <input type="file" name="image" id="image" class="custom-file-input"
                                    onchange="updateFileName(this)">
                                <label class="custom-file-label" for="image">Choose file</label>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control"
                                placeholder="Enter Description" rows="4">{{ old('description') }}</textarea>
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
