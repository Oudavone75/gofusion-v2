@extends('admin.layout.main')

@section('title', 'Edit Custom Reward')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
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
                        'page_title' => 'Edit Custom Reward',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.rewards.custom.store') }}"
                    onsubmit="submitForm(event,this,'/admin/rewards/custom-rewards/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="campaign_season_id" value="{{ $campaign_season->id }}">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" class="custom-select2 form-control" disabled>
                                <option value="campaign" {{ $campaign_season->company_id ? 'selected' : '' }}>Campaign
                                </option>
                                <option value="season" {{ is_null($campaign_season->company_id) ? 'selected' : '' }}>Season
                                </option>
                            </select>
                        </div>
                        @if ($campaign_season->company_id)
                            <div class="col-md-6 col-sm-12">
                                <label class="form-control-label">Company*</label>
                                <select name="company" class="custom-select2 form-control" disabled>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ $campaign_season->company_id == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">
                                {{ $campaign_season->company_id ? 'Campaign*' : 'Season*' }}
                            </label>
                            <select name="campaign" class="custom-select2 form-control" disabled>
                                <option value="{{ $campaign_season->id }}" selected>
                                    {{ $campaign_season->title }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Rewards / Incentives*</label>
                            <textarea name="custom_reward" class="form-control" placeholder="Enter rewards / incentives">{{ old('custom_reward', $campaign_season->custom_reward) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Custom Reward
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('vendors/scripts/quiz.js') }}"></script>
@endpush
