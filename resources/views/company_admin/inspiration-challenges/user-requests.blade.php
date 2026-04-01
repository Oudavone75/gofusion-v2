@extends('company_admin.layout.main')
@section('title', 'Challenges Created By Users')

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
                        'page_title' => 'Challenges Created By Users',
                        'paths' => breadcrumbs(),
                    ])
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                            <i class="icon-copy fa fa-download"></i> Export
                        </button>
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
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($challenges as $challenge)
                                    <tr>
                                        <td>{{ $challenge->title }}</td>
                                        <td>{{ $challenge->description }}</td>
                                        <td>
                                            @if (auth('web')->user()->hasDirectPermission('manage inspiration challenges user requests'))
                                                <!-- Accept Button -->
                                                <form
                                                    action="{{ route('company_admin.inspiration-challenges.pending.status', [$challenge->id, 'accept']) }}"
                                                    onsubmit="addPoints(event,this,true)" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="points" id="points-status" value="">
                                                    <input type="hidden" name="description" class="description"
                                                        value="{{ $challenge?->description }}">
                                                    <input type="hidden" name="guideline_text" id="guideline_text"
                                                        value="">
                                                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                </form>
                                                <!-- Reject Button -->
                                                <form
                                                    action="{{ route('company_admin.inspiration-challenges.pending.status', [$challenge->id, 'reject']) }}"
                                                    onsubmit="addPoints(event,this)" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                </form>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                    href="#" role="button" data-toggle="dropdown">
                                                    <i class="dw dw-more"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                    <a class="dropdown-item"
                                                        href="{{ route('company_admin.inspiration-challenges.pending.details', [$challenge->id]) }}"><i
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
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $challenges->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('company_admin.inspiration-challenges.export-modal', ['company_id' => $company_id])
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
