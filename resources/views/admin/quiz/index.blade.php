@extends('admin.layout.main')

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
                    @include('admin.components.page-title', [
                        'page_title' => 'Quizzes',
                        'paths' => breadcrumbs(),
                    ])

                    <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                        @if (activeCampaignSeasonFilter() == 'campaign')
                            @include('admin.filters.company-filter', ['route_name' => 'admin.quiz.index'])
                        @endif
                        <div class="dropdown mx-2">
                            <a class="btn btn-info dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                aria-expanded="false">
                                Filter: {{ ucfirst(activeCampaignSeasonFilter()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a class="dropdown-item" href="{{ route('admin.quiz.index') }}?type=campaign">Campaign</a>
                                <a class="dropdown-item" href="{{ route('admin.quiz.index') }}?type=season">Season</a>
                            </div>
                        </div>
                        @if (auth('admin')->user()->hasDirectPermission('create quiz'))
                            <a href="{{ route('admin.quiz.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Title</th>
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
                                    @if (auth('admin')->user()->hasDirectPermission('edit quiz') ||
                                            auth('admin')->user()->hasDirectPermission('delete quiz') ||
                                            auth('admin')->user()->hasDirectPermission('view quiz'))
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
                                        @if (activeCampaignSeasonFilter() == 'campaign')
                                            <td>{{ $quiz?->campaignSeason?->company?->name ?? 'Not available' }}</td>
                                        @endif
                                        <td>{{ $quiz?->campaignSeason?->title }}</td>
                                        <td>{{ $quiz->session->title }}</td>
                                        <td>{{ $quiz->points }}</td>
                                        <td>
                                            @if (auth('admin')->user()->hasDirectPermission('view quiz attempted users'))
                                                <a class="badge badge-primary"
                                                    href="{{ route('admin.quiz.attempted-users', [$quiz->id, activeCampaignSeasonFilter()]) }}">
                                                    {{ $quiz->attempts_count }}
                                                </a>
                                            @else
                                                <span class="badge badge-primary"> {{ $quiz->attempts_count }} </span>
                                        </td>
                                @endif
                                </td>
                                @if (auth('admin')->user()->hasDirectPermission('edit quiz') ||
                                        auth('admin')->user()->hasDirectPermission('delete quiz') ||
                                        auth('admin')->user()->hasDirectPermission('view quiz'))
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                href="#" role="button" data-toggle="dropdown" data-boundary="viewport">
                                                <i class="dw dw-more"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.quiz.view', $quiz->id) }}"><i
                                                        class="dw dw-eye"></i> View</a>
                                                @if (auth('admin')->user()->hasDirectPermission('edit quiz'))
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.quiz.edit', $quiz->id) }}"><i
                                                            class="dw dw-edit2"></i> Edit
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasDirectPermission('delete quiz'))
                                                    <a class="dropdown-item" href="#" data-name="{{ $quiz->title }}"
                                                        data-id="{{ $quiz->id }}"
                                                        data-url="{{ route('admin.quiz.delete', $quiz->id) }}"
                                                        onClick="deleteRecord(this)"><i class="icon-copy fa fa-trash"
                                                            aria-hidden="true"></i>
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
