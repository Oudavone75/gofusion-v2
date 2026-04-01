@extends('admin.layout.main')

@section('title', 'Add Challenges to Complete Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Add Challenges to Complete Step',
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
                <form action="{{ route('admin.images.store') }}" id="imgae-step-store-form"
                    onsubmit="submitForm(event,this,'/admin/images'+TYPE_QUERY_PARAM,true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control"
                                onchange="handleTypeChange(this)">
                                <option value="campaign" selected>Campaign</option>
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
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label campaign-select-label">Campaign*</label>
                            <select name="campaign" id="campaign-select"
                                onchange="getSelectData(this,'session-select','Sessions','campaign_id')"
                                data-url="{{ route('admin.get-campaign-sessions', ['campaign_id']) }}"
                                class="custom-select2 form-control campaign-select">
                                <option value="" disabled selected>Select Campaign</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Sessions*</label>
                            <select name="session" id="session-select" class="custom-select2 form-control">
                                <option value="" disabled selected>Select Session</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                                placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" name="points" value="{{ old('points') }}" class="form-control"
                                placeholder="Enter Points (1-300)" step="1">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Validation Mode*</label>
                            <select name="mode" id="mode-select" class="custom-select2 form-control"
                                onchange="handleModeChange(this)">
                                <option value="photo" selected>Photo</option>
                                <option value="video">Video</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12" id="ai-field">
                            <label class="form-control-label">AI Description*</label>
                            <input type="text" name="guideline_text" class="form-control"
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
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 d-none" id="video-field">
                            <label class="form-control-label">Video URL*</label>
                            <input type="text" name="video_url" class="form-control"
                                placeholder="Enter guideline video url">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 d-none" id="keywords-field">
                            <label class="form-control-label">Keywords*</label>
                            <div id="keywords-container">
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
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1"
                                onclick="addKeywordInput('#keywords-container')">
                                <i class="fa fa-plus"></i> Add Keyword
                            </button>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control" placeholder="Enter Description" rows="4">{{ old('description') }}</textarea>
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
