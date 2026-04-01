@extends('company_admin.layout.main')

@section('title', 'Edit Post')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="row">
                @include('company_admin.components.page-title', [
                    'page_title' => 'Edit Post',
                    'paths' => breadcrumbs(),
                ])
            </div>
        </div>

        <div class="card-box p-4 shadow-sm mb-30 rounded">
            {{-- Edit Form --}}
            <form action="{{ route('company_admin.social-feed.update', $post['data']->id) }}" method="POST"
                onsubmit="submitForm(event,this)" enctype="multipart/form-data" id="post-form">
                @csrf
                @method('PUT')

                {{-- Post Content --}}
                <h4 class="post-header mb-2">Post Content</h4>
                <div class="form-group">
                    <textarea name="content" class="form-control" rows="5" >{{ $post['data']->content }}</textarea>
                </div>

                <hr>

                {{-- Existing Attachments --}}
                <h5 class="post-header mb-3">
                    <i class="fa fa-paperclip mr-2"></i>Existing Attachments
                    @if ($post['data']->media->count())
                        <span class="badge badge-secondary ml-2">{{ $post['data']->media->count() }}</span>
                    @endif
                </h5>

                @if ($post['data']->media->count())
                    <div class="row" id="existing-attachments">
                        @foreach ($post['data']->media as $media)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card shadow-sm border-0 attachment-card h-100"
                                     data-type="{{ $media->media_type }}"
                                     data-path="{{ $media->file_url }}"
                                     data-name="{{ $media->name }}"
                                     style="transition: all 0.3s ease;">

                                    <div class="card-body text-center p-4 d-flex flex-column align-items-center justify-content-center"
                                         style="min-height: 180px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                        @if ($media->media_type === 'image')
                                            <div class="icon-wrapper mb-3">
                                                <i class="fa fa-file-image-o text-warning" style="font-size: 4rem;"></i>
                                            </div>
                                            <span class="badge badge-warning mb-2">IMAGE</span>
                                        @elseif ($media->media_type === 'video')
                                            <div class="icon-wrapper mb-3">
                                                <i class="fa fa-file-video-o text-info" style="font-size: 4rem;"></i>
                                            </div>
                                            <span class="badge badge-info mb-2">VIDEO</span>
                                        @elseif ($media->media_type === 'pdf')
                                            <div class="icon-wrapper mb-3">
                                                <i class="fa fa-file-pdf-o text-danger" style="font-size: 4rem;"></i>
                                            </div>
                                            <span class="badge badge-danger mb-2">PDF</span>
                                        @else
                                            <div class="icon-wrapper mb-3">
                                                <i class="fa fa-file-text-o text-primary" style="font-size: 4rem;"></i>
                                            </div>
                                            <span class="badge badge-primary mb-2">DOCUMENT</span>
                                        @endif
                                        <h6 class="text-dark font-weight-bold mt-2 mb-0 text-truncate w-100"
                                            style="font-size: 0.9rem;" title="{{ $media->name }}">
                                            {{ $media->name }}
                                        </h6>
                                    </div>

                                    <div class="card-footer bg-white border-0 text-center p-2">
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <button type="button" class="btn btn-outline-primary preview-btn" style="flex: 1;">
                                                <i class="fa fa-eye"></i> Preview
                                            </button>
                                            <button type="button" onclick="deleteRecord(this)"
                                                data-url="{{ route('company_admin.social-feed.delete-media', $media->id) }}"
                                                class="btn btn-outline-danger delete-media-btn" style="flex: 1;">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fa fa-info-circle mr-2"></i>No existing attachments for this post.
                    </div>
                @endif

                <hr>

                {{-- Dropzone for New Files --}}
                <h5 class="post-header mb-3">
                    <i class="fa fa-cloud-upload mr-2"></i>Upload New Attachments
                </h5>

                {{-- Hidden file input --}}
                <input type="file" name="medias[]" multiple class="d-none" id="file-input"
                       accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">

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

                <div class="text-right mt-4">
                    <a href="{{ route('company_admin.social-feed.list') }}" class="btn btn-secondary px-4 mr-2">
                        <i class="fa fa-arrow-left mr-2"></i>Cancel
                    </a>
                    <button type="submit" id="submit-button" class="btn btn-primary px-4">
                        <i class="fa fa-save mr-2"></i>Update Post
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="previewTitle">
                        <span id="previewFileName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="previewContent" style="min-height: 500px; background-color: #f8f9fa;">
                    <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" id="downloadLink" class="btn btn-success" download target="_blank">
                        <i class="fa fa-download mr-2"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-2"></i>Close
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('vendors/scripts/post-edit.js') }}"></script>
@endpush
