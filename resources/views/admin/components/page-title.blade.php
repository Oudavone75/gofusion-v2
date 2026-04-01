<div class="col-md-6 col-sm-12 {{ $fitContent ?? '' }}">
    <div class="title">
        <h4>{{ $page_title }}</h4>
    </div>
    @if(isset($paths))
        @include('admin.components.breadcrumb',['paths' => $paths])
    @endif
</div>
