@extends('admin.layout.main')
@section('title', 'Survey Feedback Attempted Users')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            @if (session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Survey Feedback Attempted Users',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('admin')->user()->hasDirectPermission('export survey feedback attempted users'))
                        <!-- Export Button Trigger -->
                        <div class="col-md-6 col-sm-12 d-flex justify-content-end align-items-center">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                                <i class="icon-copy fa fa-download"></i> Export
                            </button>
                        </div>
                    @endif
                    @include('admin.survey-feedback.export-modal', [
                        'go_session_step_id' => $go_session_step_id,
                    ])
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="mb-3">
                        <div class="search-wrapper">
                            <i class="fa fa-search search-icon"></i>
                            <input type="text" id="userSearchInput" class="form-control search-input"
                                placeholder="Search users...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>
                                        @if ($type == 'campaign')
                                            Campaign
                                        @else
                                            Season
                                        @endif
                                    </th>
                                    @if ($type == 'campaign')
                                        <th>Company</th>
                                    @endif
                                    @if ($type == 'campaign')
                                        <th>Departments</th>
                                    @endif
                                    <th>Points</th>
                                    <th>Attempted At</th>
                                    <th>Registeration Date</th>
                                </tr>
                            </thead>
                            @include('admin.survey-feedback.partials.table')
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3" id="paginationLinks">
                        {{ $users->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- buttons for Export datatable -->
    <script src="{{ asset('src/plugins/datatables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/vfs_fonts.js') }}"></script>
    <!-- Datatable Setting js -->
    <script src="{{ asset('vendors/scripts/datatable-setting.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        initAjaxSearch({
            input: "#userSearchInput",
            tableBody: "#userTableBody",
            pagination: "#paginationLinks",
            url: "{{ route('admin.survey-feedback.attempted-users', [$survey_feedback->id, $type]) }}",
            switchery: false,
            delay: 400
        });
    </script>
@endpush
