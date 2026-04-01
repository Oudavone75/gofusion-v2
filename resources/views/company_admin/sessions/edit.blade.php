@extends('company_admin.layout.main')

@section('title', 'Edit Session')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Edit Session',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.sessions.update', $session->id) }}"
                    onsubmit="submitForm(event,this,'/company-admin/sessions',true)" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                       <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Campaigns*</label>
                            <select name="campaign"
                                class="form-control custom-select2">
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign', $session->campaign_season_id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title', $session->title) }}"
                                class="form-control"
                                placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
