<?php

namespace App\Services;

use App\Http\Resources\CampaingSeasonResource;
use App\Models\CampaignsSeason;
use App\Models\CampaignsSeasonsRewardRange;
use App\Traits\AppCommonFunction;
use App\Models\GoSession;
use App\Models\CompanyDepartment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CampaignSeasonService
{
    use AppCommonFunction;

    public function __construct(private CampaignsSeason $campaign_season) {}

    public function getActiveCampaingSeason($mode, $user)
    {
        $company_id = $user->company_id;
        $department_id = $user->company_department_id;
        $query = CampaignsSeason::with(['goSessions'])
            ->where('status', config('constants.STATUS.ACTIVE'))
            ->where('end_date', '>=', Carbon::now()->format('Y-m-d'));

        if ($mode === 'employee') {
            $query->where('company_id', $company_id)
                ->where(function ($q) use ($department_id) {

                    // Company-wide campaigns (no assigned departments)
                    $q->whereDoesntHave('departments')

                        // OR campaigns assigned to the user's department
                        ->orWhereHas('departments', function($sub) use ($department_id) {
                            $sub->where('company_department_id', $department_id);
                        });
                })

                // Sort campaigns with matching department first
                ->orderByDesc(
                    CampaignsSeason::selectRaw('COUNT(*)')
                        ->from('campaign_departments')
                        ->whereColumn('campaign_id', 'campaigns_seasons.id')
                        ->where('company_department_id', $department_id)
                );

        } else {
            $query->whereNull('company_id');
        }

        $campaign_season = $query->first();
        if (!$campaign_season) {
            $error_msg = $user->isEmployee() ? trans('general.active_campaign') : trans('general.active_season');
            return ['status' => false, "message" => $error_msg];
        }
        return new CampaingSeasonResource($campaign_season);
    }

    public function getCampaigns($company_id = null)
    {
        $query = CampaignsSeason::query()->with('company', 'department');

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        if (activeCampaignSeasonFilter() == "campaign") {
            $query->whereNotNull('company_id');
        } else {
            $query->whereNull('company_id');
        }
        $query->where('end_date', '>=', date('Y-m-d'));
        return $this->getPaginatedData($query);
    }
    public function getCampaignsCount($company_id = null)
    {
        return $this->campaign_season->whereNotNull('company_id')
            ->when($company_id, function ($query, $company_id) {
                return $query->where('company_id', $company_id);
            })
            ->where('end_date', '>=', now())
            ->count();
    }
    public function checkExistingCampaign($company_id, $start_date, $end_date, $campaign_id = null, $type = "campaign")
    {
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        $query = $type == "campaign"
            ? $this->campaign_season::where('company_id', $company_id)
            : $this->campaign_season::whereNull('company_id');
        if ($campaign_id) {
            $query->where('id', '!=', $campaign_id);
        }
        return $query->where(function ($query) use ($start_date, $end_date) {
            $query->whereRaw('DATE(start_date) between ? and ?', [$start_date, $end_date])
                ->orWhereRaw('DATE(end_date) between ? and ?', [$start_date, $end_date])
                ->orWhere(function ($query) use ($start_date, $end_date) {
                    $query->whereRaw('DATE(start_date) <= ?', [$start_date])
                        ->whereRaw('DATE(end_date) >= ?', [$end_date]);
                });
        })->exists();
    }

    public function create($request, $company_id = null, $is_admin = false)
    {
        try {
            DB::beginTransaction();
            $department_id = $request['department'] ?? null;
            if ($is_admin) {
                $company_id    = $request['type'] == "season"  ? null : $request['company_id'];
                $department_id = $request['type'] == "season"  ? null : $department_id;
            }
            if (isset($request['to_ranking'])) {
                $arrLength = count($request['to_ranking']) - 1;
                $max_user_ranking_size = $request['to_ranking'][$arrLength];
            }
            $campaign = $this->campaign_season::create([
                'company_id' => $company_id,
                'title' => $request['title'],
                'description' => $request['description'] ?? null,
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
                'reward' =>  0,
                'custom_reward' => $request['custom_reward'] ?? null,
                'custom_reward_status' => $request['custom_reward'] ? true : false,
                'max_user_ranking_size' => $max_user_ranking_size ?? 0,
                'status' => $request['status'] ?? config('constants.STATUS.PENDING'),
            ]);
            if (isset($request['departments'])) {
                $campaign->departments()->sync($request['departments']);
            }
            if (isset($request['from_ranking']) && isset($request['to_ranking']) && isset($request['reward'])) {
                $reward_ranges = [];
                foreach ($request['from_ranking'] as $key => $from_ranking) {
                    $reward_ranges[] = [
                        'campaign_season_id' => $campaign->id,
                        'rank_start' => $from_ranking,
                        'rank_end' => $request['to_ranking'][$key],
                        'reward' => $request['reward'][$key],
                    ];
                }
                CampaignsSeasonsRewardRange::insert($reward_ranges);
            }
            DB::commit();
            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($campaign, array $data, $is_admin = false)
    {
        try {
            DB::beginTransaction();
            $updateData = [
                // 'company_department_id' => $data['department'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reward' =>  0,
                'custom_reward' => $data['custom_reward'] ?? null,
            ];

            if ($is_admin && isset($data['company_id'])) {
                $updateData['company_id'] = $data['company_id'];
            }

            $campaign->update($updateData);
            if (isset($data['departments'])) {
                $campaign->departments()->sync($data['departments']);
            }

            if (isset($data['from_ranking']) && isset($data['to_ranking']) && isset($data['reward'])) {
                CampaignsSeasonsRewardRange::where('campaign_season_id', $campaign->id)->delete();
                $reward_ranges = [];
                foreach ($data['from_ranking'] as $key => $from_ranking) {
                    $reward_ranges[] = [
                        'campaign_season_id' => $campaign->id,
                        'rank_start' => $from_ranking,
                        'rank_end' => $data['to_ranking'][$key],
                        'reward' => $data['reward'][$key],
                    ];
                }
                CampaignsSeasonsRewardRange::insert($reward_ranges);
            }
            DB::commit();
            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function getCompanyDepartments($company_id)
    {
        return CompanyDepartment::query()->select('id', 'name')->where('company_id', $company_id)->get();
    }

    public function checkIfCampaignHasSession($campaign_id)
    {
        return GoSession::where('campaign_season_id', $campaign_id)->exists();
    }

    public function delete($campaign)
    {
        return $campaign->delete();
    }

    public function checkActiveCampaignExists($company_id)
    {
        $is_exist = $this->makeQuery($company_id)->exists();
        return $is_exist;
    }

    public function checkActiveCampaignPassed($company_id)
    {
        $end_date = date('Y-m-d', strtotime(Carbon::now()));
        $is_exist = $this->makeQuery($company_id, $end_date)->exists();
        return $is_exist;
    }

    public function makeItComplate($company_id)
    {
        $end_date = date('Y-m-d', strtotime(Carbon::now()));
        $campaign = $this->makeQuery($company_id)->first();
        $campaign->update([
            'status' => config('constants.STATUS.COMPLETED')
        ]);
        return $campaign;
    }

    public function makeQuery($company_id, $date = null)
    {
        $query = CampaignsSeason::where('company_id', $company_id)
            ->where('status', config('constants.STATUS.ACTIVE'));
        if ($date) {
            $query->where('end_date', '>=', $date);
        }
        return $query;
    }

    public function getCompanyCampaigns($company_id)
    {
        // Get current date once
        $currentDateTime = date('Y-m-d');
        $currentDate = date('Y-m-d');

        // Build the query
        return CampaignsSeason::query()
            ->select('id', 'title')
            ->where('company_id', $company_id)
            ->where(function ($query) use ($currentDateTime, $currentDate) {
                $query->where('end_date', '>=', $currentDateTime)
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->get();
    }

    public function getEmployeeCampaignByCompany($company_id, $company_department_id, $type = 'personal')
    {
        $campaign = CampaignsSeason::query()
            ->select('id', 'title')
            ->where('company_id', $company_id);

        if ($type === 'personal') {
            // Company-wide campaigns (no departments assigned)
            $campaign->whereDoesntHave('departments');
        } else {
            // Department-specific campaigns (match this user’s department)
            $campaign->whereHas('departments', function ($q) use ($company_department_id) {
                $q->where('company_departments.id', $company_department_id);
            });
        }
        $campaign->where('status', config('constants.STATUS.ACTIVE'));
        return $campaign->first();
    }

    public function getSeason()
    {
        return CampaignsSeason::query()
            ->select('id', 'title')
            ->whereNull('company_id')
            ->where('status', config('constants.STATUS.ACTIVE'))
            ->first();
    }
    public function getSeasons()
    {
        return CampaignsSeason::query()->select('id', 'title')->whereNull('company_id')->where('end_date', '>=', date('Y-m-d'))->get();
    }
    public function getCampignOrSeasonsById($id)
    {
        return CampaignsSeason::query()->select('id', 'title')->where('end_date', '>=', date('Y-m-d'))->find($id);
    }

    public function getAllActiveAndCompletedSeasons()
    {
        return CampaignsSeason::query()
            ->select('id', 'title')
            ->whereNull('company_id')
            ->whereIn('status', [
                // config('constants.STATUS.COMPLETED'),
                'completed',
                'active'
                // config('constants.STATUS.ACTIVE')
            ])
            ->pluck('id');
    }
}
