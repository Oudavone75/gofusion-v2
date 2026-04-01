@extends('admin.layout.main')

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
                    @include('admin.components.page-title', [
                        'page_title' => 'Survey / Feedback',
                        'paths' => breadcrumbs(),
                    ])

                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        @if (activeCampaignSeasonFilter() == 'campaign')
                            @include('admin.filters.company-filter', [
                                'route_name' => 'admin.survey-feedback.index',
                            ])
                        @endif
                        <div class="dropdown mx-2">
                            <a class="btn btn-info dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                aria-expanded="false">
                                Filter: {{ ucfirst(activeCampaignSeasonFilter()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a class="dropdown-item"
                                    href="{{ route('admin.survey-feedback.index') }}?type=campaign">Campaign</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.survey-feedback.index') }}?type=season">Season</a>
                            </div>
                        </div>

                        @if (auth('admin')->user()->hasDirectPermission('create survey feedback'))
                            <a href="{{ route('admin.survey-feedback.create') }}" class="btn btn-primary">Create</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    @if (activeCampaignSeasonFilter() == 'campaign')
                                        <th>Company Name</th>
                                    @endif
                                    <th>
                                        @if (activeCampaignSeasonFilter() == 'campaign')
                                            Campaign Name
                                        @else
                                            Season Name
                                        @endif
                                    </th>
                                    <th>Session Name</th>
                                    <th>Points</th>
                                    <th>Total Attempted Users</th>
                                    @if (auth('admin')->user()->hasDirectPermission('edit survey feedback') ||
                                            auth('admin')->user()->hasDirectPermission('delete survey feedback') ||
                                            auth('admin')->user()->hasDirectPermission('view survey feedback'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($survey_feedbacks as $survey_feedback)
                                    <tr>
                                        @if (activeCampaignSeasonFilter() == 'campaign')
                                            <td>{{ $survey_feedback->company->name }}</td>
                                        @endif
                                        <td>{{ $survey_feedback->goSessionSteps?->goSession?->campaignSeason?->title }}</td>
                                        <td>{{ $survey_feedback->goSessionSteps->goSession->title ?? 'Not Available' }}</td>
                                        <td>{{ $survey_feedback->points }}</td>
                                        <td>
                                            @if (auth('admin')->user()->hasDirectPermission('view survey feedback attempted users'))
                                                <a class="badge badge-primary"
                                                    href="{{ route('admin.survey-feedback.attempted-users', [$survey_feedback->id, activeCampaignSeasonFilter()]) }}">
                                                    {{ $survey_feedback->attempts_count }}
                                                </a>
                                            @else
                                                {{ $survey_feedback->attempts_count }}
                                            @endif
                                        </td>
                                        @if (auth('admin')->user()->hasDirectPermission('edit survey feedback') ||
                                                auth('admin')->user()->hasDirectPermission('delete survey feedback') ||
                                                auth('admin')->user()->hasDirectPermission('view survey feedback'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.survey-feedback.view', $survey_feedback->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if (auth('admin')->user()->hasDirectPermission('edit survey feedback'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.survey-feedback.edit', $survey_feedback->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('admin')->user()->hasDirectPermission('delete survey feedback'))
                                                            <a class="dropdown-item delete-image-survey_feedback"
                                                                href="#" onClick="deleteRecord(this)"
                                                                data-id="{{ $survey_feedback->id }}"
                                                                data-name="{{ $survey_feedback->title }}"
                                                                data-url="{{ route('admin.survey-feedback.delete', $survey_feedback->id) }}">
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
