@extends('company_admin.layout.main')
@section('title', 'View Post')
@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="row">
                @include('company_admin.components.page-title', [
                    'paths' => breadcrumbs(),
                    'page_title' => 'Post Details',
                ])
            </div>
        </div>
        <div class="card-box p-4 shadow-sm mb-30 rounded">
            {{-- Post Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="post-header">
                        {{ $post['data']->author->name ?? ($post['data']->author->full_name ?? 'Unknown User') }}</h5>
                    <p class="text-muted mb-0">
                        Posted on {{ $post['data']->published_at ?? 'N/A' }}
                    </p>
                </div>
                <span
                    class="badge {{ $post['data']->status->value === 'approved' ? 'badge-success' : 'badge-danger' }} px-3 py-2">
                    {{ $post['data']->status->value === 'approved' ? 'Active' : 'Inactive' }}
                </span>
            </div>
            {{-- Post Content --}}
            <div class="mb-4">
                <h5 class="post-header mb-2">Post Content</h5>
                <p class="text-dark" style="line-height: 1.6; font-size: 16px;">
                    {!! nl2br(e($post['data']->content ?? 'No content available')) !!}
                </p>
            </div>
            {{-- Attachments --}}
            @if ($post['data']->media->count())
                <hr>
                <h5 class="post-header mb-3">
                    <i class="fa fa-paperclip mr-2"></i>Attachments
                    <span class="badge badge-secondary ml-2">{{ $post['data']->media->count() }}</span>
                </h5>
                <div class="row">
                    @foreach ($post['data']->media as $media)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card shadow-sm border-0 attachment-card h-100" data-type="{{ $media->media_type }}"
                                data-path="{{ $media->file_url }}" data-name="{{ $media->name }}"
                                style="cursor: pointer; transition: all 0.3s ease;">
                                {{-- THUMBNAIL DISPLAY --}}
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
                                    @elseif ($media->media_type == 'pdf')
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
                                <div class="card-footer bg-white border-0 text-center p-3">
                                    <button class="btn btn-primary btn-block preview-btn"
                                        style="border-radius: 25px; font-weight: 600;">
                                        <i class="fa fa-eye mr-2"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="fa fa-info-circle mr-2"></i>No attachments available for this post.
                </div>
            @endif
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
    <script src="{{ asset('vendors/scripts/post-view.js') }}"></script>
@endpush
