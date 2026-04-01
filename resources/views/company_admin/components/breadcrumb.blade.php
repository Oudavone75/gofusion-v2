@if(count($paths) > 0)
<nav aria-label="breadcrumbs" role="navigation">
    <ol class="breadcrumb">
        @foreach($paths as $path)
            <li class="breadcrumb-item">
                @if($path['is_active'])
                    <span class="bread-active" aria-current="page">{{ $path['name'] }}</span>
                @elseif($path['url'])
                    <a href="{{ $path['url'] }}">{{ $path['name'] }}</a>
                @else
                    <span>{{ $path['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
