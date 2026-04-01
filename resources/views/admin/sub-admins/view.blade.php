@extends('admin.layout.main')

@section('title', 'View Sub-Admin')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View Sub-Admin',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Name</label>
                        <p class="form-control-plaintext">{{ $sub_admin->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Email</label>
                        <p class="form-control-plaintext">{{ $sub_admin->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Permissions</label>
                        <div class="form-control-plaintext"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #e0e0e0; padding: 10px; border-radius: 4px;">
                            @if ($sub_admin->permissions && $sub_admin->permissions->count() > 0)
                                @foreach ($sub_admin->permissions as $permission)
                                    <span class="badge badge-primary mr-1 mb-1">{{ $permission->name }}</span>
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
