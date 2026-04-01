@extends('company_admin.layout.main')

@section('title', 'Challenge Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Challenge Step',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 text-right">
                        <a href="{{ route('company_admin.steps.challenges-step.create') }}" class="btn btn-primary">Create</a>
                    </div>
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <table class="table table-hover nowrap">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Session Name</th>
                                <th>Points</th>
                                <th>Total Attempted Users</th>
                                <th>Challenges Created By Users</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($challenge_steps as $step)
                                <tr>
                                    <td>{{ $step?->goSessionStep?->goSession?->campaignSeason?->title ?? 'Not Available' }}
                                    </td>
                                    <td>{{ $step?->goSessionStep?->goSession?->title ?? 'Not Available' }}</td>
                                    <td>{{ $step->points }}</td>
                                    <td>{{ $step->attempts_count }}</td>
                                    <td><a class="btn btn-primary"
                                            href="{{ route('company_admin.steps.challenges-step.attempted-users', $step->id) }}">
                                            View Challenges </a></td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                href="#" role="button" data-toggle="dropdown">
                                                <i class="dw dw-more"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                <a class="dropdown-item"
                                                    href="{{ route('company_admin.steps.challenges-step.edit', $step) }}"><i
                                                        class="dw dw-edit2"></i> Edit</a>
                                                <a class="dropdown-item delete-challenge-step" href="#"
                                                    onClick="deleteRecord(this)" data-id="{{ $step->id }}"
                                                    data-name="{{ $step->title }}"
                                                    data-url="{{ route('company_admin.steps.challenges-step.destroy', $step->id) }}">
                                                    <i class="icon-copy fa fa-trash" aria-hidden="true"></i> Delete</a>

                                                <a class="dropdown-item"
                                                    href="{{ route('company_admin.steps.challenges-step.show', $step) }}"><i
                                                        class="dw dw-eye"></i> View</a>
                                            </div>
                                        </div>
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
                        {{ $challenge_steps->links('pagination::bootstrap-4') }}
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
