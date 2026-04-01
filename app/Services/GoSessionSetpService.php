<?php

namespace App\Services;

use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Traits\AppCommonFunction;

class GoSessionSetpService
{
    use AppCommonFunction;

    public function __construct(private GoSession $go_session, private GoSessionStep $go_session_step)
    {
        $this->go_session = $go_session;
        $this->go_session_step = $go_session_step;
    }

    public function getAllSessionsStep($company_id=null)
    {
        if($company_id){
            $query = $this->go_session_step->with('goSession')
            ->whereHas('goSession', function ($q) use ($company_id) {
                $q->whereHas('campaignSeason', function ($q) use ($company_id) {
                    $q->where('company_id', $company_id);
                    $q->where('end_date', '>=', date('Y-m-d'));
                });
            });
        }else{
            $query = $this->go_session_step->with('goSession')
            ->whereHas('goSession', function ($q) {
                $q->whereHas('campaignSeason', function ($q) {
                    $q->where('end_date', '>=', date('Y-m-d'));
                });
            });

        }
        return $this->getPaginatedData($query);
    }

    public function create($go_session_id,$status)
    {
        foreach(range(1, 6) as $position) {
            $this->go_session_step::create([
                'go_session_id' => $go_session_id,
                'status' => $status,
                'position' => $position
            ]);
        }
    }

    public function delete($go_session_id)
    {
        $go_session_steps = $this->go_session_step->where('go_session_id', $go_session_id)->get();
        foreach($go_session_steps as $go_session_step) {
            $this->go_session_step->delete($go_session_step);
        }
    }
}
