@extends('company_admin.layout.main')

@section('title', 'Add Custom Reward')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Add Custom Reward',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.rewards.custom.store') }}"
                    onsubmit="submitForm(event,this,'/company-admin/rewards/custom-rewards/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label campaign-select-label">Campaign*</label>
                            <select name="campaign_season_id"
                                class="custom-select2 form-control campaign-select">
                                <option value="" disabled selected>Select Campaign</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                        {{ old('campaign_season_id') == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Rewards / Incentives*</label>
                            <textarea name="custom_reward" class="form-control" placeholder="Enter rewards / incentives">{{ old('custom_reward') }}</textarea>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">
                            <i class="fa fa-save"></i> Create Custom Reward
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
