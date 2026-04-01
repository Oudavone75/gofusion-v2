@extends('admin.layout.main')

@section('title', 'Add Inspiration Challenge')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Add Inspiration Challenge', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.inspiration-challenges.store') }}"
                    onsubmit="submitForm(event,this,'/admin/inspiration-challenges',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Theme*</label>
                            <select name="theme_id" class="custom-select2 form-control">
                                <option value="" disabled selected>Select Theme</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme->id }}" {{ old('theme') == $theme->id ? 'selected' : '' }}>
                                        {{ $theme->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Company</label>
                            <select name="company_id" id="company-select" class="custom-select2 form-control">
                                <option value="" disabled selected>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0 department-select">
                            <label for="department-select">Department*</label>
                            <select name="departments[]" id="department-select" class="selectpicker form-control"
                                data-size="5" data-actions-box="true" data-selected-text-format="count" multiple >
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Category*</label>
                            <select name="challenge_category_id" id="category-select" class="custom-select2 form-control" onchange="updateCategoryName(this)">
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-name="{{ $category->name }}" {{ old('challenge_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="category" id="category-name" value="">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0" id="validation-mode">
                            <label class="form-control-label">Validation Mode*</label>
                            <select name="mode" id="mode-select" class="custom-select2 form-control"
                                onchange="handleModeChange(this)">
                                <option value="photo" selected>Photo</option>
                                <option value="video">Video</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0 event-column d-none ">
                            <label class="form-control-label">Event Name*</label>
                            <input name="event_name" id="event-name" class="form-control" placeholder="Enter event name" >
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>

                        <div class="col-md-6 col-sm-12 ml-0 event-column d-none ">
                            <label class="form-control-label"> Event Type* </label>
                            <select name="event_type" id="event-select" class="custom-select2 form-control"  onchange="handleEventTypeChange(this)">
                                <option value="onsite" selected >Onsite</option>
                                <option value="online">Online</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>

                        <div class="col-md-6 col-sm-12 ml-0 event-column d-none">
                            <label class="form-control-label" id="event-location-label">Event Location*</label>
                            <input name="event_location" id="event-location" class="form-control" placeholder="Enter event location" >
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 event-column d-none">
                            <label class="form-control-label">Event Start Date*</label>
                            <input type="text" name="event_start_date"
                                   class="form-control date-picker"
                                   placeholder="Enter Event Start Date" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 event-column d-none">
                            <label class="form-control-label">Event End Date*</label>
                            <input type="text" name="event_end_date"
                                   class="form-control date-picker"
                                   placeholder="Enter Event End Date"  autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12" id="title">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter Title">
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Points*</label>
                            <input type="number" name="attempted_points" class="form-control"
                                placeholder="Enter Points (1-300)" step="1">
                            <div class="form-control-feedback d-none"></div>
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
                        <div class="col-md-6 col-sm-12" id="ai-field">
                            <label class="form-control-label">AI Description*</label>
                            <input name="guideline_text" class="form-control" placeholder="Enter AI Description"></input>
                            <div class="form-control-feedback d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12"  id="video-field">
                            <label class="form-control-label">Video URL</label>
                            <input type="text" name="video_url"
                                   class="form-control"
                                   placeholder="Enter guideline video url">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="description" class="form-control" placeholder="Enter Description" rows="4"></textarea>
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
@push('scripts')
    <script>
        $(document).ready(function() {
            handleModeChange($('#mode-select')[0]);
        });
    </script>
@endpush
