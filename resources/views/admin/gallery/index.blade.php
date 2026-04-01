@extends('admin.layout.main')

@section('title', 'Gallery')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            @include('admin.components.page-title', [
                                'page_title' => 'Gallery',
                                'paths' => breadcrumbs(),
                            ])
                        </div>
                    </div>
                    @if(auth('admin')->user()->hasDirectPermission('create gallery'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a class="btn btn-primary" href="{{ route('admin.gallery.create') }}" role="button">
                                Upload Gallery
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="gallery-wrap">
                <ul class="row">
                    @forelse ($gallery_images as $gallery_image)
                        <li class="col-lg-3 col-md-6 col-sm-12">
                            <div class="da-card box-shadow">
                                <div class="da-card-photo">
                                    <img src="{{ asset($gallery_image->image_path) }}" alt="">
                                    <div class="da-overlay">
                                        <div class="da-social">
                                            <h5 class="mb-10 color-white pd-20">{{ $gallery_image->filename }}</h5>
                                            <ul class="clearfix">
                                                <li><a href="{{ asset($gallery_image->image_path) }}"
                                                        data-fancybox="images"><i class="fa fa-picture-o"></i></a></li>
                                                <li>
                                                    <a href="javascript:void(0);"
                                                        data-url="{{ asset($gallery_image->image_path) }}"
                                                        onClick="copyToClipboard(this)">
                                                        <i class="fa fa-link"></i>
                                                    </a>
                                                </li>
                                                @if(auth('admin')->user()->hasDirectPermission('delete gallery'))
                                                    <li><a href="#" data-id="{{ $gallery_image->id }}"
                                                            data-url="{{ route('admin.gallery.delete', $gallery_image->id) }}"
                                                            onClick="deleteRecord(this)"><i class="icon-copy fa fa-trash"
                                                                aria-hidden="true"></i></a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="col-12 text-center">
                            <div class="alert alert-info">
                                No images found in the gallery.
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
