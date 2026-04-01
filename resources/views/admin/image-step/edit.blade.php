@extends('admin.layout.main')

@section('title', 'Edit Challenges to Complete Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Edit Challenges to Complete Step', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.images.update', $image_step->id) }}"
                    onsubmit="submitForm(event,this,'/admin/images?type={{ !is_null($image_step->goSessionStep->goSession->campaignSeason->company_id) ? 'campaign' : 'season' }}',true)"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        @if (!is_null($image_step->goSessionStep->goSession->campaignSeason->company_id))
                            <div class="col-md-6 col-sm-12 ml-0">
                                <label class="form-control-label">Company*</label>
                                <select name="company" id="company-select"
                                    onchange="getSelectData(this,'campaign-select','Campaigns','company_id')"
                                    data-url="{{ route('admin.get-company-campaigns', ['company_id']) }}"
                                    class="custom-select2 form-control">
                                    <option value="" disabled selected>Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company', $image_step->goSessionStep->goSession->campaignSeason->company_id) == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                            <input type="hidden" name="type" value="campaign">
                        @else
                            <input type="hidden" name="type" value="season">
                        @endif
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">
                                @if (!is_null($image_step->goSessionStep->goSession->campaignSeason->company_id))
                                    Campaign*
                                @else
                                    Season*
                                @endif
                            </label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectData(this,'session-select','Sessions','campaign_id')"
                                data-url="{{ route('admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($company_campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign', $image_step->goSessionStep->goSession->campaignSeason->id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach

                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Sessions*</label>
                        <select name="session" id="session-select"
                            class="custom-select2 form-control">
                            <option value="" disabled selected>Select Session</option>
                            @foreach ($campaign_sessions as $session)
                            <option value="{{ $session->id }}"
                                {{ old('session', $image_step->goSessionStep->goSession->id) == $session->id ? 'selected' : '' }}>
                                {{ $session->title }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Title*</label>
                        <input type="text" name="title" value="{{ old('title', $image_step->title) }}"
                            class="form-control"
                            placeholder="Enter Title">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Points*</label>
                        <input type="number" name="points" value="{{ old('points', $image_step->points) }}"
                            class="form-control"
                            placeholder="Enter Points (1-300)" step="1">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12 ml-0">
                        <label class="form-control-label">Validation Mode*</label>
                        <select name="mode" id="mode-select" class="custom-select2 form-control" onchange="handleModeChange(this)">
                            <option value="photo" {{ $image_step->mode === 'photo' ? 'selected' : '' }}>Photo</option>
                            <option value="video" {{ $image_step->mode === 'video' ? 'selected' : '' }}>Video</option>
                            <option value="checkbox" {{ $image_step->mode === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                        </select>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12" id="ai-field">
                        <label class="form-control-label">AI Description*</label>
                        <input type="text" name="guideline_text" value="{{$image_step->guideline_text}}"
                               class="form-control"
                               placeholder="Enter one line AI description">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12" id="photo-field">
                        <label for="image" class="form-control-label">Sample Image</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input"
                                onchange="updateFileName(this)">
                            <label class="custom-file-label" for="image">Choose file</label>
                            <div class="form-control-feedback mt-2 d-none"></div>
                            @if($image_step->image_path)
                            <a href="{{ asset($image_step->image_path) }}" target="_blank"> View Uploaded Image</a>
                            @endif
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12" id="video-field">
                        <label class="form-control-label">Video URL</label>
                        <input type="text" name="video_url"
                               class="form-control"
                               placeholder="Enter guideline video url" value="{{$image_step->video_url}}">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-12 col-sm-12 d-none" id="keywords-field">
                        <label class="form-control-label">Keywords*</label>
                        <div id="keywords-container">
                            @if($image_step->keywords && count($image_step->keywords) > 0)
                                @foreach($image_step->keywords as $keyword)
                                    <div class="keyword-entry input-group mb-2">
                                        <input type="text" name="keywords[]" class="form-control"
                                            placeholder="Enter keyword" value="{{ $keyword }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="removeKeywordInput(this)">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="keyword-entry input-group mb-2">
                                    <input type="text" name="keywords[]" class="form-control"
                                        placeholder="Enter keyword">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="removeKeywordInput(this)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-1"
                            onclick="addKeywordInput('#keywords-container')">
                            <i class="fa fa-plus"></i> Add Keyword
                        </button>
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Description*</label>
                        <textarea name="description" class="form-control"
                            placeholder="Enter Description" rows="4">{{ old('description', $image_step->description) }}</textarea>
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
@push('scripts')
    <script>
        $(document).ready(function() {
            handleModeChange($('#mode-select')[0]);
        });
    </script>
@endpush
