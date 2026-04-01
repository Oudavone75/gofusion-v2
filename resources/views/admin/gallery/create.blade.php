@extends('admin.layout.main')

@section('title', 'Gallery Upload')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            @include('admin.components.page-title', [
                                'page_title' => 'Add Gallery',
                                'paths' => breadcrumbs(),
                            ])
                        </div>
                    </div>
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="clearfix mb-20">
                    <div class="pull-left">
                        <h4 class="text-blue h4">Upload Files</h4>
                    </div>
                </div>
                <form class="dropzone" id="gallery-dropzone" action="{{ route('admin.gallery.store') }}">
                    @csrf
                    <div class="fallback">
                        <input type="file" name="gallery_images[]" multiple />
                    </div>
                </form>
                <div class="col-md-12 col-sm-12 text-right" style="margin-left: 15px">
                    <button type="button" id="upload-btn" class="btn btn-primary mt-4">Upload All</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const galleryStoreUrl = "{{ route('admin.gallery.store') }}";
        const galleryIndexUrl = "{{ route('admin.gallery.index') }}";
        const csrfToken = "{{ csrf_token() }}";

        document.addEventListener("DOMContentLoaded", function () {
            initGalleryDropzone();
        });
    </script>
@endpush
