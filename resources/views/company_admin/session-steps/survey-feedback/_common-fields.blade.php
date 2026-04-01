<div class="row">
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Campaign*</label>
        <select name="campaign" id="survey-feedback-campaign-select"
            onchange="getSelectData(this,'survey-feedback-session-select','Sessions','campaign_id')"
            data-url="{{ route('company_admin.get-campaign-sessions', ['campaign_id']) }}"
            class="custom-select2 form-control">
            <option value="" disabled selected>Select Campaign</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}" {{ old('campaign') == $campaign->id ? 'selected' : '' }}>
                    {{ $campaign->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Session*</label>
        <select name="session" id="survey-feedback-session-select" class="custom-select2 form-control">
            <option value="" disabled selected>Select session</option>
        </select>
    </div>
    <div class="col-md-6 col-sm-12">
        <label class="form-control-label">Total Points (Out of 300)*</label>
        <input type="number" name="points" value="{{ old('points', 300) }}"
            class="form-control" placeholder="Enter total points (1-300)">
    </div>
</div>
