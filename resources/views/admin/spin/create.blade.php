@extends('admin.layout.main')

@section('title', 'Add SpinWheel Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Add SpinWheel Step', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.spin.store') }}" onsubmit="submitForm(event, this, '/admin/spin-wheel/list'+TYPE_QUERY_PARAM,true);" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control" onchange="handleTypeChange(this)">
                                <option value="campaign" selected  >Campaign</option>
                                <option value="season"> Season</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0 company-select">
                            <label class="form-control-label">Company*</label>
                            <select name="company" id="company-select"
                                onchange="getSelectData(this,'campaign-select','Campaigns','company_id')"
                                data-url="{{ route('admin.get-company-campaigns', ['company_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label campaign-select-label">Campaign*</label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectData(this,'session-select','Sessions','campaign_id')"
                                data-url="{{ route('admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control campaign-select">
                                <option value="" disabled selected>Select Campaign</option>
                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Sessions*</label>
                            <select name="session" id="session-select"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Session</option>
                            </select>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Video URL*</label>
                            <input type="text" name="video_url" value="{{ old('video_url') }}"
                                class="form-control"
                                placeholder="Video URL (https://www.youtube.com/watch?v=dQw4w9WgXcQ)" min="0"
                                step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Bonus Leaves*</label>
                            <input type="text" name="bonus_leaves" value="{{ old('bonus_leaves') }}"
                                class="form-control"
                                placeholder="Enter Bonus Leaves" min="0" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Surprising Gift*</label>
                            <input type="text" name="promo_codes" value="{{ old('promo_codes') }}"
                                class="form-control"
                                placeholder="Enter Surprising Gift" min="0" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" min="1" name="points" value="{{ old('points') }}"
                                class="form-control"
                                placeholder="Enter Points (1-300)" step="1">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
