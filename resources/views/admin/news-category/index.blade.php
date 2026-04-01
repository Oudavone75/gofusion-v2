@extends('admin.layout.main')

@section('title', 'News Category')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'News Category',
                        'paths' => breadcrumbs(),
                    ])
                    @if (auth('admin')->user()->hasDirectPermission('create news categories'))
                        <div class="col-md-6 col-sm-12 text-right">
                            <a href="{{ route('admin.news-category.create') }}" class="btn btn-primary">Create</a>
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
                                    <th>Category Name</th>
                                    <th>Created At</th>
                                    @if (auth('admin')->user()->hasDirectPermission('edit news categories') ||
                                            auth('admin')->user()->hasDirectPermission('delete news categories') ||
                                            auth('admin')->user()->hasDirectPermission('view news categories'))
                                        <th>Action</th>
                                    @else
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($news_categories as $news_category)
                                    <tr>
                                        <td>{{ $news_category->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($news_category->created_at)->format('d F Y') }}</td>
                                        @if (auth('admin')->user()->hasDirectPermission('edit news categories') ||
                                                auth('admin')->user()->hasDirectPermission('delete news categories') ||
                                                auth('admin')->user()->hasDirectPermission('view news categories'))
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        @if (auth('admin')->user()->hasDirectPermission('edit news categories'))
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.news-category.edit', $news_category) }}"><i
                                                                    class="dw dw-edit2"></i> Edit</a>
                                                        @endif
                                                        @if (auth('admin')->user()->hasDirectPermission('delete news categories'))
                                                            <a class="dropdown-item delete-news-category" href="#"
                                                                onClick="deleteRecord(this)"
                                                                data-id="{{ $news_category->id }}"
                                                                data-name="{{ $news_category->title }}"
                                                                data-url="{{ route('admin.news-category.destroy', $news_category->id) }}">
                                                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                                Delete</a>
                                                        @endif
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.news-category.show', $news_category->id) }}"><i
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
                        {{ $news_categories->links('pagination::bootstrap-4') }}
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
