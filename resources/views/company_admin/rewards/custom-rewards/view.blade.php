@extends('company_admin.layout.main')

@section('title', 'View Custom Reward')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row justify-content-between">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'View Custom Reward',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <div class="row">
                    @if ($campaign_season->company_id)
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-control-label bold">Company</label>
                            <p class="form-control-plaintext">
                                {{ $campaign_season->company?->name ?? 'N/A' }}
                            </p>
                        </div>
                    @endif
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label class="form-control-label bold">
                            {{ $campaign_season->company_id ? 'Campaign Name' : 'Season Name' }}
                        </label>
                        <p class="form-control-plaintext">
                            {{ $campaign_season->title }}
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label class="form-control-label bold">Rewards / Incentives</label>
                        <div class="form-control-plaintext">
                            {!! nl2br(e($campaign_season->custom_reward)) !!}
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label class="form-control-label bold">Status</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-{{ $campaign_season->custom_reward_status ? 'success' : 'danger' }}">
                                {{ $campaign_season->custom_reward_status ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
