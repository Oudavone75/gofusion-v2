@extends('admin.layout.main')

@section('title', 'Reported User Details')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            {{-- Page Header --}}
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Reported User Details',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>

            <div class="pd-20 card-box mb-30 shadow-sm">
                <div class="row">

                    <div class="col-md-6 col-sm-12">
                        <label class="font-weight-bold">Reported By</label>
                        <p class="form-control-plaintext">
                            {{ $report->reporter->name ?? $report->reporter->full_name ?? 'Not Available' }}
                        </p>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <label class="font-weight-bold">Reason</label>
                        <p class="form-control-plaintext">
                            {{ $report->reason ?? 'Not Available' }}
                        </p>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <label class="font-weight-bold">Comment</label>
                        <p class="form-control-plaintext">
                            {{ $report->description ?? 'Not Available' }}
                        </p>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <label class="font-weight-bold">Reported At</label>
                        <p class="form-control-plaintext">
                            {{ $report->created_at?->format('d M Y h:i A') ?? 'Not Available' }}
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
