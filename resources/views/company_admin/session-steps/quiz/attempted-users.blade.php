@extends('company_admin.layout.main')
@section('title', 'Quiz Attempted Users')

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
                <div class="row justify_between">
                    @include('company_admin.components.page-title', [
                        'page_title' => $quiz->title,
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('export quiz attempted users'))
                        <!-- Export Button Trigger -->
                        <div class="col-md-6 col-sm-12 d-flex justify-content-end align-items-center">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                                <i class="icon-copy fa fa-download"></i> Export
                            </button>
                        </div>
                        @include('company_admin.session-steps.quiz.export-modal', [
                            'quiz_id' => $quiz->id,
                        ])
                    @endif
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Departments</th>
                                    <th>Points</th>
                                    <th>Registration Date</th>
                                    <th>Attempted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->company?->name ?? 'Not Available' }}</td>
                                        <td>{{ $user->department?->name ?? 'Not Available' }}</td>
                                        <td>{{ $user->quiz_attempts[0]?->points }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($user->registeration_date)->format('d M Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($user->quiz_attempts[0]?->created_At)->format('d M Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
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
@endpush
