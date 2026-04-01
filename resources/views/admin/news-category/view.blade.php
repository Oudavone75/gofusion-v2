@extends('admin.layout.main')

@section('title', 'View News Category')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'View News Category', 'paths' => breadcrumbs()
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label bold">Name</label>
                        <p class="form-control-plaintext">{{ $news_category->name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function disableSubmitButton(form) {
            const button = form.querySelector('#submit-button');
            if (button) {
                button.disabled = true;
                button.innerText = 'Submitting...';
            }
        }
    </script>
@endpush
