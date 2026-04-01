@extends('company_admin.layout.main')

@section('title', 'Import Sessions')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row d-flex justify-content-between">
                    @include('company_admin.components.page-title', [
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
                <form action="{{ route('company_admin.sessions.import') }}"
                      onsubmit="submitFileImportForm(event, this,'',false,'sessions')" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 ml-0">
                            <label class="form-control-label">Campaign*</label>
                            <select name="campaign"
                            class="custom-select2 form-control">
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
