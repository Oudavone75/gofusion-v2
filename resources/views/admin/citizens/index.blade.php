@extends('admin.layout.main')

@section('title', 'Citizens')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Citizens',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="mb-3">
                        <div class="search-wrapper">
                            <i class="fa fa-search search-icon"></i>
                            <input type="text" id="citizenSearchInput" class="form-control search-input"
                                placeholder="Search citizens...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>City</th>
                                    <th>Registeration Date</th>
                                    <th>Status</th>
                                    @if (auth('admin')->user()->hasDirectPermission('delete citizens'))
                                    <th>Action</th>
                                    @else
                                    <th></th>
                                    @endif
                                </tr>
                            </thead>
                            @include('admin.citizens.partials.table')

                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3" id="paginationLinks">
                        {{ $citizens->links('pagination::bootstrap-4') }}
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
        toggleStatus();
    </script>
    <script>
        initAjaxSearch({
            input: "#citizenSearchInput",
            tableBody: "#citizenTableBody",
            pagination: "#paginationLinks",
            url: "{{ route('admin.citizens.index') }}",
            switchery: true,
            delay: 400
        });
    </script>
@endpush
