<div class="row">
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Campaign*</label>
        <select name="campaign" id="quiz-campaign-select"
            onchange="getSelectData(this,'quiz-session-select','Sessions','campaign_id')"
            data-url="{{ route('company_admin.get-campaign-sessions',['campaign_id']) }}"
            class="custom-select2 form-control">
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
