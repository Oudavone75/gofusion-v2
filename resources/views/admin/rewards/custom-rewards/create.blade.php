@extends('admin.layout.main')

@section('title', 'Add Custom Reward')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
                        'page_title' => 'Add Custom Reward',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('admin.rewards.custom.store') }}"
                    onsubmit="submitForm(event,this,'/admin/rewards/custom-rewards/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Select Type (Campaign / Season)</label>
                            <select name="type" id="type-select" class="custom-select2 form-control"
                                onchange="handleTypeChange(this)">
                                <option value="campaign" selected>Campaign</option>
                                <option value="season">Season</option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12 company-select">
                            <label class="form-control-label">Company*</label>
                            <select name="company" id="quiz-company-select"
                                onchange="getSelectData(this,'reward-campaign-select','Campaigns','company_id')"
                                data-url="{{ route('admin.rewards.custom.get-unrewarded-campaigns', ['company_id']) }}"
                                class="custom-select2 form-control">
                                <option value="" disabled selected>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label campaign-select-label">Campaign*</label>
                            <select name="campaign_season_id" id="reward-campaign-select"
                                class="custom-select2 form-control campaign-select">
                                <option value="" disabled selected>Select Campaign</option>
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
@push('scripts')
    <script>
        function handleTypeChange(select) {
            const type = select.value;
            const companySelect = $('.company-select');
            const campaignLabel = $('.campaign-select-label');
            const campaignSelect = $('#reward-campaign-select');

            if (type === 'season') {
                companySelect.addClass('d-none');
                campaignLabel.text('Season*');
                campaignSelect.html('<option value="" disabled selected>Loading...</option>').trigger('change');

                $.ajax({
                    url: "{{ route('admin.rewards.custom.get-unrewarded-seasons') }}",
                    type: 'GET',
                    success: function (data) {
                        campaignSelect.empty();
                        campaignSelect.append('<option value="" disabled selected>Select Season</option>');
                        $.each(data, function (key, obj) {
                            campaignSelect.append('<option value="' + obj.id + '">' + obj.title + '</option>');
                        });
                        campaignSelect.trigger('change');
                    },
                    error: function () {
                        campaignSelect.html('<option value="">Error loading Seasons</option>').trigger('change');
                    }
                });
            } else {
                companySelect.removeClass('d-none');
                campaignLabel.text('Campaign*');
                campaignSelect.attr('data-url', "{{ route('admin.rewards.custom.get-unrewarded-campaigns', ['company_id']) }}");
                campaignSelect.html('<option value="" disabled selected>Select Campaign</option>').trigger('change');
            }
        }
    </script>
@endpush
