<div class="row">
    <div class="col-md-6 col-sm-12 @if(isset($quiz)) d-none @endif ">
        <label class="form-control-label">Select Type (Campaign / Season)</label>
        <select name="type" id="type-select" class="custom-select2 form-control" onchange="handleTypeChange(this)">
            <option value="campaign" selected  >Campaign</option>
            <option value="season"> Season</option>
        </select>
    </div>
    <div class="col-md-6 col-sm-12 company-select">
        <label class="form-control-label">Company*</label>
        <select name="company" id="survey-feedback-company-select"
            onchange="getSelectData(this,'survey-feedback-campaign-select','Campaigns','company_id')"
            data-url="{{ route('admin.get-company-campaigns',['company_id']) }}"
            class="custom-select2 form-control">
            <option value="" disabled selected>Select Company</option>
            @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ old('company')==$company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
            @endforeach
        </select>
        <div class="form-control-feedback d-none"></div>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label campaign-select-label">Campaign*</label>
        <select name="campaign" id="survey-feedback-campaign-select"
            onchange="getSelectData(this,'survey-feedback-session-select','Sessions','campaign_id')"
            data-url="{{ route('admin.get-campaign-sessions',['campaign_id']) }}"
            class="custom-select2 form-control campaign-select">
            <option value="" disabled selected>Select Campaign</option>
        </select>
        <div class="form-control-feedback d-none"></div>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Session*</label>
        <select name="session" id="survey-feedback-session-select"
            class="custom-select2 form-control">
            <option value="" disabled selected>Select session</option>
        </select>
        <div class="form-control-feedback d-none"></div>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Total Points (Out of 300)*</label>
        <input type="number" name="points" value="{{ old('points', 300) }}"
            class="form-control" placeholder="Enter total points (1-300)">
        <div class="form-control-feedback d-none"></div>
    </div>
</div>
