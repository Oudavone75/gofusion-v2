@extends('admin.layout.main')
@section('title', 'Post Reports')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            {{-- Page Header --}}
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
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
                                    <th>Author</th>
                                    <th>Post</th>
                                    <th>Reported Users</th>
                                    @if (auth('admin')->user()->hasDirectPermission('manage posts reports'))
                                        <th>Action</th>
                                    @else
                                        <th>Status</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($reported_posts as $post)
                                    <tr>
                                        <td>{{ $post->author->name ?? $post->author->full_name ?? 'Not Available' }}</td>
                                        <td>
                                            <a href="{{ route('admin.social-feed.view', $post->id) }}"
                                                class="text-primary font-weight-bold" target="_blank">
                                                View Post
                                            </a>
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasDirectPermission('view reported users'))
                                                <a class="badge badge-primary"
                                                    href="{{ $post->reports_count > 0 ? route('admin.social-feed.reported-users-list', $post->id) : '#' }}">
                                                    {{ format_number_short($post->reports_count) }} </a>
                                            @else
                                                <span class="badge badge-primary">
                                                    {{ format_number_short($post->reports_count) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasDirectPermission('manage posts reports'))
                                            <div class="dropdown">
                                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                    href="#" role="button" data-toggle="dropdown">
                                                    <i class="dw dw-more"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                    <button class="dropdown-item text-success"
                                                        onClick="updateReportStatus('{{ route('admin.social-feed.reports.status', [$post->id, 'resolved']) }}')">
                                                        <i class="dw dw-check"></i> Approve
                                                    </button>
                                                    <button class="dropdown-item text-danger"
                                                        onClick="updateReportStatus('{{ route('admin.social-feed.reports.status', [$post->id, 'dismissed']) }}')">
                                                        <i class="icon-copy ti-close"></i> Reject
                                                    </button>
                                                </div>
                                            </div>
                                            @else
                                                <span
                                                    class="badge badge-warning">
                                                    pending
                                                </span>
                                            @endif
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
                        {{ $reported_posts->links('pagination::bootstrap-4') }}
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
