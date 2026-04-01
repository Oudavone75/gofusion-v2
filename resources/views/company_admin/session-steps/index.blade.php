@extends('company_admin.layout.main')

@section('title', 'Go Session Steps')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', ['page_title' => 'Go Session Steps'])
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <table class="table table-hover nowrap">
                        <thead>
                            <tr>
                                <th>Campaign Title</th>
                                <th>Session Title</th>
                                <th>Step Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($go_session_steps as $step)
                                <tr>
                                    <td>{{ $step->gosession->campaignSeason->title }}</td>
                                    <td>{{ $step->gosession->title }}</td>
                                    <td>{{ $step->position }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $step->gosession->status == 'active' ? 'success' : ($step->gosession->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($step->gosession->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $go_session_steps->links('pagination::bootstrap-4') }}
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
@endpush
