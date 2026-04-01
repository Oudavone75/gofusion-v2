@extends('admin.layout.main')

@section('title', 'Posts')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Posts',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('admin')->user()->hasDirectPermission('create posts'))
                        <div class="col-md-6 col-sm-12 d-flex justify-content-end text-right">
                            <a href="{{ route('admin.social-feed.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Author</th>
                                    <th>Content</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    @if (auth('admin')->user()->hasDirectPermission('edit posts') ||
                                            auth('admin')->user()->hasDirectPermission('delete posts') ||
                                            auth('admin')->user()->hasDirectPermission('view posts'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>{{ $post->author->name ?? ($post->author->full_name ?? 'Not Available') }}</td>
                                        <td>{{ $post->content ?? 'Not Available' }}</td>
                                        <td>{{ $post->published_at ?? 'Not Available' }}</td>
                                        <td>
                                            @if (auth('admin')->user()->hasDirectPermission('manage posts status'))
                                                <input type="checkbox" class="switch-btn"
                                                    data-url="{{ route('admin.social-feed.toggle-status', $post->id) }}"
                                                    {{ $post->status->value === 'approved' ? 'checked' : '' }}>
                                            @else
                                                <span
                                                    class="badge badge-{{ $post->status->value === 'approved' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($post->status->value === 'approved' ? 'Active' : 'Inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        @if (auth('admin')->user()->hasDirectPermission('edit posts') ||
                                                auth('admin')->user()->hasDirectPermission('delete posts') ||
                                                auth('admin')->user()->hasDirectPermission('view posts'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.social-feed.view', $post->id) }}"><i
                                                                class="dw dw-eye"></i> View</a>
                                                        @if (auth('admin')->user()->hasDirectPermission('edit posts'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.social-feed.edit', $post->id) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('admin')->user()->hasDirectPermission('delete posts'))
                                                            <a class="dropdown-item delete-image-post" href="#"
                                                                onClick="deleteRecord(this)" data-id="{{ $post->id }}"
                                                                data-name="{{ $post->title }}"
                                                                data-url="{{ route('admin.social-feed.delete', $post->id) }}">
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
                        {{ $posts->links('pagination::bootstrap-4') }}
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
    <script>
        toggleStatus();
    </script>
@endpush
