@extends('company_admin.layout.main')

@section('title', 'Quizzes')

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
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Quizzes',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('create quiz'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('company_admin.steps.quiz.create') }}" class="btn btn-primary">Create</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Session Name</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Total Attempted Users</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit quiz') ||
                                            auth('web')->user()->hasDirectPermission('delete quiz') ||
                                            auth('web')->user()->hasDirectPermission('view quiz'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $quiz)
                                    <tr>
                                        <td>{{ $quiz->title }}</td>
                                        <td>{{ $quiz->session->title }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $quiz->status == 'active' ? 'success' : ($quiz->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($quiz->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $quiz->created_at->format('d F Y') }}</td>
                                        <td><a class="badge badge-primary"
                                                href="{{ route('company_admin.steps.quiz.attempted-users', $quiz->id) }}">
                                                {{ $quiz->attempts_count }} </a></td>
                                        @if (auth('web')->user()->hasDirectPermission('edit quiz') ||
                                                auth('web')->user()->hasDirectPermission('delete quiz') ||
                                                auth('web')->user()->hasDirectPermission('view quiz'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.steps.quiz.view', $quiz->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if (auth('web')->user()->hasDirectPermission('edit quiz'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.steps.quiz.edit', $quiz->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete quiz'))
                                                            <a class="dropdown-item" href="#"
                                                                data-name="{{ $quiz->title }}"
                                                                data-id="{{ $quiz->id }}" onClick="deleteRecord(this)"
                                                                data-url="{{ route('company_admin.steps.quiz.delete', $quiz->id) }}"><i
                                                                    class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
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
                        {{ $quizzes->links('pagination::bootstrap-4') }}
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
