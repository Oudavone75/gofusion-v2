@extends('admin.layout.main')

@section('title', 'Import Sessions')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row d-flex justify-content-between">
                    @include('admin.components.page-title', [
                        'page_title' => 'Import Sessions', 'paths' => breadcrumbs()
                    ])
                    <a href="{{ asset('SessionImportFile.xlsx') }}" download class="btn btn-primary mr-10">
                        <i class="icon-copy dw dw-download"></i> Download Sample File
                    </a>
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <strong>Import Your Content</strong>
                <p>Download and fill in an Excel file (template above) with your own data:</p>
                <p>👉 With the Excel option, simply complete the provided file without changing its structure
                </p>
            </div>
            <div class="pd-20 card-box mb-30">
                <div id="alert-box" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                    <h4 class="alert-heading" id="alert-box-heading" ></h4>
                    <div id="alert-box-message"></div>

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.sessions.import') }}"
                      onsubmit="submitFileImportForm(event, this,'',false,'sessions')" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control"
                                onchange="handleTypeChange(this)">
                                <option value="campaign" selected>Campaign</option>
                                <option value="season"> Season</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 ml-0  company-select">
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
                        <div class="col-md-6 col-sm-12">
                            <label for="image" class="form-control-label">Upload File*</label>
                            <div class="custom-file">
                                <input type="file" name="file" id="file" class="custom-file-input" onchange="updateFileName(this)" accept=".xls,.xlsx,.csv">
                                <label class="custom-file-label" for="image">Choose file</label>
                                <div class="form-control-feedback mt-2 d-none"></div>
                            </div>
                        </div>
                        <div id="generic-error" class="text-danger d-none"></div>
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">
                                <i class="fa fa-save"></i> Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('vendors/scripts/import-file.js') }}"></script>
    <script>
        //
    </script>
@endpush
