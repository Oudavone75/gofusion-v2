<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanyContactService;
use App\Models\CompanyContact;

class CompanyContactController extends Controller
{
    public function __construct(private CompanyContactService $company_contact_service) {}

    public function index()
    {
        $company_contacts = $this->company_contact_service->getAllCompanyContacts();
        return view('admin.company-contact.index', compact('company_contacts'));
    }

    public function show(CompanyContact $company_contact)
    {
        $company_contact->update(['mark_as_read' => true]);
        return view('admin.company-contact.view', compact('company_contact'));
    }

    public function markAllAsRead()
    {
        $this->company_contact_service->markAllAsRead();
        return redirect()->back()->with('success', trans('general.updated_successfully'));
    }
}
