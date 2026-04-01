@extends('admin.layout.main')

@section('title', 'View Role')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View Role',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Name</label>
                        <p class="form-control-plaintext">{{ $role->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Permissions</label>
                        <div class="form-control-plaintext">
                            @if ($role->permissions && $role->permissions->count() > 0)
                                @foreach ($role->permissions as $permission)
                                    <span class="badge badge-primary mr-1 mb-1">{{ ucfirst($permission->name) }}</span>
                                @endforeach
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
