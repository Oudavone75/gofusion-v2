@extends('company_admin.layout.main')

@section('title', 'Survey / Feedback')

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
                        'page_title' => 'Survey / Feedback',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('create survey feedback'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('company_admin.steps.survey-feedback.create') }}"
                                class="btn btn-primary">Create</a>
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
                                    <th>Session Name</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Total Attempted Users</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit survey feedback') ||
                                            auth('web')->user()->hasDirectPermission('delete survey feedback') ||
                                            auth('web')->user()->hasDirectPermission('view survey feedback'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($survey_feedbacks as $survey_feedback)
                                    <tr>
                                        <td>{{ $survey_feedback->goSessionSteps->goSession->title ?? 'Not Available' }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $survey_feedback->status == 'active' ? 'success' : ($survey_feedback->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($survey_feedback->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $survey_feedback->created_at->format('d F Y') }}</td>
                                        <td>
                                            @if (auth('web')->user()->hasDirectPermission('create survey feedback'))
                                                <a class="badge badge-primary"
                                                    href="{{ route('company_admin.steps.survey-feedback.attempted-users', $survey_feedback->id) }}">
                                                    {{ $survey_feedback->attempts_count }}
                                                </a>
                                            @else
                                                {{ $survey_feedback->attempts_count }}
                                            @endif
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('edit survey feedback') ||
                                                auth('web')->user()->hasDirectPermission('delete survey feedback') ||
                                                auth('web')->user()->hasDirectPermission('view survey feedback'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.steps.survey-feedback.view', $survey_feedback->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if (auth('web')->user()->hasDirectPermission('edit survey feedback'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.steps.survey-feedback.edit', $survey_feedback->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit
                                                            </a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete survey feedback'))
                                                            <a class="dropdown-item delete-image-survey_feedback"
                                                                href="#" onClick="deleteRecord(this)"
                                                                data-id="{{ $survey_feedback->id }}"
                                                                data-name="{{ $survey_feedback->title }}"
                                                                data-url="{{ route('company_admin.steps.survey-feedback.delete', $survey_feedback->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
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
                        {{ $survey_feedbacks->links('pagination::bootstrap-4') }}
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
