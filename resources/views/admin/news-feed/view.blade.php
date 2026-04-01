@extends('admin.layout.main')

@section('title', 'View News Feed')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View News Feed', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Company</label>
                        <p class="form-control-plaintext">{{ $news_feed->company->name ?? 'Admin' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Category</label>
                        <p class="form-control-plaintext">{{ $news_feed->category->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Title</label>
                        <p class="form-control-plaintext">{{ $news_feed->title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 ml-0">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext text-capitalize">
                            <span
                                class="badge badge-{{ $news_feed->status == 'active' ? 'success' : ($news_feed->status == 'inactive' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                                {{ $news_feed->status == 'active' ? 'Published' : 'Un-Published' ?? '—' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Description</label>
                        <p class="form-control-plaintext">{{ $news_feed->description ?? '—' }}</p>
                    </div>
                    @if ($news_feed->status == 'active')
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label bold">Published At</label>
                            <p class="form-control-plaintext">
                                {{ \Carbon\Carbon::parse($news_feed->published_at)->format('d F Y') }}</p>
                        </div>
                    @else
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label bold">UnPublished At</label>
                            <p class="form-control-plaintext">
                                {{ \Carbon\Carbon::parse($news_feed->published_at)->format('d F Y') }}</p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Image</label>
                        @if (!empty($news_feed->image_path))
                            <div>
                                <img src="{{ $news_feed->image_path }}" alt="News Feed Image" class="img-thumbnail"
                                    style="max-height: 150px;">
                            </div>
                            <a href="{{ asset($news_feed->image_path) }}" target="_blank"> View Uploaded Image</a>
                        @else
                            <p class="form-control-plaintext">No image uploaded.</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
