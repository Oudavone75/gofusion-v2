@extends('admin.layout.main')

@section('title', 'Create Post')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="page-header">
            <div class="row">
                @include('admin.components.page-title', [
                    'page_title' => 'Create Post',
                    'paths' => breadcrumbs(),
                ])
            </div>
        </div>

        <div class="card-box p-4 mb-30 shadow-sm rounded">
            {{-- SINGLE FORM FOR EVERYTHING --}}
            <form action="{{ route('admin.social-feed.store') }}"
                onsubmit="submitForm(event,this,'/admin/social-feed/list',true)" method="POST" enctype="multipart/form-data"
                id="post-form">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-6 col-sm-12">
                        <h4 class="post-header mb-2">Select Type (Company / Citizen)</h4>
                        <select name="type" id="type-select" class="custom-select2 form-control"
                            onchange="handleTypeChange(this,true)">
                            <option value="campaign" selected>Company</option>
                            <option value="season">Citizen</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-12 company-select">
                        <h4 class="post-header mb-2">Company</h4>
                        <select name="company_id" id="company-select" class="custom-select2 form-control">
                            <option value="" disabled selected>Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                </div>
                <h4 class="post-header mb-2 mt-4">Post Content</h4>
                {{-- Post Content --}}
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Write something inspiring..." rows="5"></textarea>
                    <div class="form-control-feedback d-none"></div>
                    <small class="form-text text-muted">
                        <i class="fa fa-info-circle"></i> Share your thoughts, updates, or announcements
                    </small>
                </div>

                <hr class="my-4">

                {{-- File Upload Area --}}
                <h5 class="post-header mb-3">
                    <i class="fa fa-cloud-upload mr-2"></i>Attachments
                </h5>
                <p class="text-muted small mb-3">Upload images, videos, PDFs, or documents to enhance your post</p>

                {{-- Hidden file input --}}
                <input type="file" name="medias[]" multiple class="d-none" id="file-input"
                    accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">

                {{-- Custom dropzone area --}}
                <div class="dropzone-area bg-light p-5 rounded border text-center" id="dropzone-area"
                    style="cursor: pointer; transition: all 0.3s ease; border: 2px dashed #dee2e6;">
                    <div class="dz-message">
                        <i class="fa fa-cloud-upload fa-4x text-primary mb-3"></i>
                        <h5 class="post-header mb-2">Drag & Drop Files Here</h5>
                        <p class="text-muted mb-1">or click to browse</p>
                        <p class="small text-danger mb-0">
                            <i class="fa fa-info-circle"></i> Max file size: 50MB per file
                        </p>
                    </div>
                </div>

                {{-- File preview container --}}
                <div id="file-preview" class="mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('admin.social-feed.list') }}" class="btn btn-secondary px-4">
                        <i class="fa fa-arrow-left mr-2"></i>Cancel
                    </a>
                    <button type="submit" id="submit-button" class="btn btn-primary px-4">
                        <i class="fa fa-paper-plane mr-2"></i>Publish Post
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('vendors/scripts/post.js') }}"></script>
@endpush
