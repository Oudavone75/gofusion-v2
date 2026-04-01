@extends('company_admin.layout.main')

@section('title', 'Add SpinWheel Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Add SpinWheel Step',
                        'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.steps.spin-wheel.store') }}" method="POST"
                    onsubmit="submitForm(event,this)"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Campaign*</label>
                            <select name="campaign" id="spin-wheel-campaign-select"
                                onchange="getSelectData(this,'spin-wheel-session-select','Sessions','campaign_id')"
                                data-url="{{ route('company_admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control"
                            >
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign') == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Sessions*</label>
                            <select name="session" id="spin-wheel-session-select"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Session</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Video URL*</label>
                            <input type="text" name="video_url" value="{{ old('video_url') }}"
                                class="form-control"
                                placeholder="Video URL (https://www.youtube.com/watch?v=dQw4w9WgXcQ)" min="0"
                                step="1">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Bonus Leaves*</label>
                            <input type="number" name="bonus_leaves" value="{{ old('bonus_leaves') }}"
                                class="form-control"
                                placeholder="Enter Bonus Leaves" min="0" step="1">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Surprising Gift*</label>
                            <input type="text" name="promo_codes" value="{{ old('promo_codes') }}"
                                class="form-control"
                                placeholder="Enter Surprising Gift" min="0" step="1">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" name="points" value="{{ old('points') }}"
                                class="form-control"
                                placeholder="Enter Points (1-300)">
                            <div class="form-control-feedback mt-2 d-none"></div>
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
