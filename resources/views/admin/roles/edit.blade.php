@extends('admin.layout.main')

@section('title', 'Edit Role')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Edit Role',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.roles.update', $role->id) }}" onsubmit="submitForm(event,this,'roles')" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Name*</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control"
                                placeholder="Enter Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label for="permissions-select">Permissions*</label>
                            <select name="permissions[]" id="permissions-select" class="selectpicker form-control"
                                data-size="5" data-actions-box="true" data-selected-text-format="count" multiple required>
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}"
                                        {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ ucfirst($permission->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            var $permissionSelect = $("#permissions-select");

            $permissionSelect.selectpicker({
                noneSelectedText: "Select Permissions",
                liveSearch: true
            });
        });
    </script>
@endpush
