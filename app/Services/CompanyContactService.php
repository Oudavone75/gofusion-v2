<?php

namespace App\Services;

use App\Models\CompanyContact;
use App\Traits\AppCommonFunction;

class CompanyContactService
{
    use AppCommonFunction;

    public function saveCompanyContact(array $data)
    {
        $company_contact = CompanyContact::create($data);
        return [
            'success' => true,
            'message' => trans('general.company_info_received'),
            'data' => $company_contact
        ];
    }

    public function getAllCompanyContacts()
    {
        $query = CompanyContact::with('user')
            ->orderBy('mark_as_read', 'asc')
            ->orderBy('created_at', 'desc');
        return $this->getPaginatedData($query);
    }

    public function markAllAsRead()
    {
        CompanyContact::where('mark_as_read', false)->update(['mark_as_read' => true]);
    }
}
