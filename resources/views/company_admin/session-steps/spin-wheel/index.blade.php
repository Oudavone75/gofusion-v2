@extends('company_admin.layout.main')

@section('title', 'SpinWheel Step')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'SpinWheel Step',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('web')->user()->hasDirectPermission('create spinwheel'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('company_admin.steps.spin-wheel.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Campaign Name</th>
                                    <th>Session Name</th>
                                    <th>Points</th>
                                    <th>Total Attempted Users</th>
                                    @if (auth('web')->user()->hasDirectPermission('edit spinwheel') ||
                                            auth('web')->user()->hasDirectPermission('delete spinwheel') ||
                                            auth('web')->user()->hasDirectPermission('view spinwheel'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($spin_wheels as $step)
                                    <tr>
                                        <td>{{ $step->goSessionStep?->goSession?->campaignSeason?->title ?? 'Not Available' }}
                                        </td>
                                        <td>{{ $step->goSessionStep?->goSession?->title ?? 'Not Available' }}</td>
                                        <td>{{ $step->points }}</td>
                                        <td>
                                            @if (auth('web')->user()->hasDirectPermission('view spinwheel attempted users'))
                                                <a class="badge badge-primary"
                                                    href="{{ route('company_admin.steps.spin-wheel.attempted-users', $step->id) }}">
                                                    {{ $step->attempts_count }}
                                                </a>
                                            @else
                                                {{ $step->attempts_count }}
                                            @endif
                                        </td>
                                        @if (auth('web')->user()->hasDirectPermission('edit spinwheel') ||
                                                auth('web')->user()->hasDirectPermission('delete spinwheel') ||
                                                auth('web')->user()->hasDirectPermission('view spinwheel'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('web')->user()->hasDirectPermission('edit spinwheel'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('company_admin.steps.spin-wheel.edit', $step) }}">
                                                                <i class="dw dw-edit2"></i> Edit
                                                            </a>
                                                        @endif
                                                        @if (auth('web')->user()->hasDirectPermission('delete spinwheel'))
                                                            <a class="dropdown-item delete-event-step" href="#"
                                                                onClick="deleteRecord(this)" data-id="{{ $step->id }}"
                                                                data-name="{{ $step->title }}"
                                                                data-url="{{ route('company_admin.steps.spin-wheel.destroy', $step->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete
                                                            </a>
                                                        @endif
                                                        <a class="dropdown-item"
                                                            href="{{ route('company_admin.steps.spin-wheel.view', $step) }}"><i
                                                                class="dw dw-eye"></i> View</a>
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
                        {{ $spin_wheels->links('pagination::bootstrap-4') }}
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
