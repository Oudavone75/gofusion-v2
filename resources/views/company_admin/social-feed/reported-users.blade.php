@extends('company_admin.layout.main')
@section('title', 'Post Reports')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            {{-- Page Header --}}
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Post Reports',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pb-20">
                    <div class="table-responsive">
                        <table class="table table-hover nowrap">
                            <thead>
                                <tr>
                                    <th>Reported By</th>
                                    <th>Reason</th>
                                    <th>Reported At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($reports as $report)
                                    <tr>
                                        <td>{{ $report->reporter->full_name ?? 'Unknown User' }}</td>
                                        <td>{{ $report->reason ?? 'No reason provided' }}</td>
                                        <td>{{ $report->created_at?->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                    href="#" role="button" data-toggle="dropdown">
                                                    <i class="dw dw-more"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                    <a class="dropdown-item"
                                                        href="{{ route('company_admin.social-feed.reported-users-detail', $report->id) }}"><i
                                                            class="dw dw-eye"></i> View</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No reports found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $reports->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function updateReportStatus(url) {
            Swal.fire({
                title: "Are you sure?",
                text: "You want to update this report status?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, proceed",
            }).then((result) => {
                if (result.isConfirmed) {

                    fetch(url, {
                            method: "PATCH",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                        })
                        .then(async (res) => {
                            const data = await res.json();

                            if (res.ok) {
                                Swal.fire("Updated!", data.message, "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Error", data.message || "Something went wrong", "error");
                            }
                        })
                        .catch(() => Swal.fire("Error", "Something went wrong", "error"));
                }
            });
        }
    </script>
@endpush
