<div class="row">
    <div class="col-md-6 col-sm-12 @if(isset($quiz)) d-none @endif ">
        <label class="form-control-label">Select Type (Campaign / Season)</label>
        <select name="type" id="type-select" class="custom-select2 form-control" onchange="handleTypeChange(this)">
            <option value="campaign" @if(isset($quiz)) @if(is_null($quiz->company_id)) selected @endif  @else selected @endif  >Campaign</option>
            <option value="season" @if(isset($quiz) && is_null($quiz->company_id)) selected @endif> Season</option>
        </select>
    </div>
    <div class="col-md-6 col-sm-12 company-select @if(isset($quiz) && is_null($quiz->company_id)) d-none @endif">
        <label class="form-control-label">Company*</label>
        <select name="company" id="quiz-company-select"
            onchange="getSelectData(this,'quiz-campaign-select','Campaigns','company_id')"
            data-url="{{ route('admin.get-company-campaigns',['company_id']) }}"
            class="custom-select2 form-control">
            <option value="" disabled selected>Select Company</option>
            @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ (isset($quiz) && $quiz->company_id == $company->id) || old('company')
                == $company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label campaign-select-label"> @if(isset($quiz) && is_null($quiz->company_id)) Season* @else Campaign* @endif </label>
        <select name="campaign" id="quiz-campaign-select"
            onchange="getSelectData(this,'quiz-session-select','Sessions','campaign_id')"
            data-url="{{ route('admin.get-campaign-sessions',['campaign_id']) }}"
            class="custom-select2 form-control campaign-select">
            <option value="" disabled selected>Select Campaign</option>
            @if(isset($campaigns))
            @foreach($campaigns as $campaign)
            <option value="{{ $campaign->id }}" {{ (isset($quiz) && $quiz->campaign_season_id == $campaign->id) ||
                old('campaign') == $campaign->id ? 'selected' : '' }}>
                {{ $campaign->title }}
            </option>
            @endforeach
            @endif
        </select>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Session*</label>
        <select name="session" id="quiz-session-select" class="custom-select2 form-control">
            <option value="" disabled selected>Select session</option>
            @if(isset($sessions))
            @foreach($sessions as $session)
            <option value="{{ $session->id }}" {{ (isset($quiz) && $quiz->go_session_id == $session->id) ||
                old('session') == $session->id ? 'selected' : '' }}>
                {{ $session->title }}
            </option>
            @endforeach
            @endif
        </select>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Title*</label>
        <input type="text" name="title" value="{{ old('title', $quiz->title ?? '') }}" class="form-control"
            placeholder="Enter title">
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Total Points (Out of 300)*</label>
        <input type="number" name="points" value="{{ old('points', $quiz->points ?? 300) }}"
            class="form-control" placeholder="Enter total points (1-300)">
    </div>
</div>
