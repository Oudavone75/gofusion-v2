<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoSessionSetpService;

class GoSessionStepController extends Controller
{
    public function __construct(public GoSessionSetpService $go_session_step_service)
    {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $go_session_steps = $this->go_session_step_service->getAllSessionsStep();
        return view('admin.session-steps.index', compact('go_session_steps'));
    }

   
}
