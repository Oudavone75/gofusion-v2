@extends('admin.layout.main')

@section('title', 'Create Notification')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Create Notification',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.notifications.store') }}"
                    onsubmit="submitForm(event,this,'/admin/notifications',true)" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control"
                                onchange="handleTypeChange(this)">
                                <option value="campaign" selected>Campaign</option>
                                <option value="season">Season</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12 company-select">
                            <label class="form-control-label">Company*</label>
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

                        <!-- ✅ Notification Type Dropdown -->
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Notification Type*</label>
                            <select name="notification_type" id="notification-type" class="custom-select2 form-control"
                                onchange="handleNotificationTypeChange(this)">
                                <option value="direct" selected>Send Now</option>
                                <option value="scheduled">Schedule</option>
                            </select>
                        </div>

                        <!-- ✅ Scheduled Date-Time Picker (Hidden by default) -->
                        <div class="col-md-6 col-sm-12 d-none" id="schedule-datetime-container">
                            <label class="form-control-label">Select Date & Time*</label>
                            <input class="form-control datetimepicker" name="scheduled_at" id="scheduled_at"
                                placeholder="Choose Date and time" type="text" autocomplete="off">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Title*</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                                placeholder="Enter Title">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Description*</label>
                            <textarea name="content" class="form-control" placeholder="Enter Description" rows="4">{{ old('content') }}</textarea>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Send Notification</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tz = '{{ config('app.timezone') }}' || 'CET';
            const now = new Date().toLocaleString("en-US", {
                timeZone: tz
            });
            const localDate = new Date(now);

            // Initialize Air Datepicker
            $('.datetimepicker').datepicker({
                timepicker: true,
                language: 'en',
                autoClose: true,
                dateFormat: 'dd MM yyyy',
                startDate: localDate, // 👈 sets default date/time to France time
            });
        });
    </script>
@endpush
