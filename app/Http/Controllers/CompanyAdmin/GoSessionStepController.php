<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Services\GoSessionSetpService;
use Illuminate\Support\Facades\Auth;
use App\Models\GoSession;

class GoSessionStepController extends Controller
{
    private $user;
    public function __construct(public GoSessionSetpService $go_session_step_service)
    {
        $this->user  = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $go_session_steps = $this->go_session_step_service->getAllSessionsStep($this->user->company_id);
        return view('company_admin.session-steps.index', compact('go_session_steps'));
    }

   
}
