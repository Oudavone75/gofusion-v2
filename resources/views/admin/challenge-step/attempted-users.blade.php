@extends('admin.layout.main')
@section('title', 'Challenges Created Users')

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
                        'page_title' => 'Challenges Created By Users',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="card-box mb-30">
                <div class="pb-20">
                    <table class="table table-hover nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Company</th>
                                <th>Departments</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->fullname }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->company?->name ?? 'Not Available' }}</td>
                                    <td>{{ $user->department?->name ?? 'Not Available' }}</td>
                                    <td>
                                        @if ($user->challenge_attempts[0]->status == 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($user->challenge_attempts[0]->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <!-- Accept Button -->
                                            <form action="{{ route('admin.challenges-step.status', 'accept') }}"
                                                onsubmit="addPoints(event,this,true)" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="go_session_step_id"
                                                    value="{{ $challenge->go_session_step_id }}">
                                                <input type="hidden" name="points" id="points-status" value="">
                                                <input type="hidden" name="description" class="description"
                                                    value="{{ $user->challenge_attempts[0]?->description }}">
                                                <input type="hidden" name="guideline_text" id="guideline_text"
                                                    value="">
                                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                            </form>
                                            <!-- Reject Button -->
                                            <form action="{{ route('admin.challenges-step.status', 'reject') }}"
                                                onsubmit="addPoints(event,this)" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="go_session_step_id"
                                                    value="{{ $challenge->go_session_step_id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
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
                                                    href="{{ route('admin.challenges-step.attempted-user.details', [$user->id, $user->challenge_attempts[0]->go_session_step_id]) }}"><i
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
