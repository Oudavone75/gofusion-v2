<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = ['id', '_token'];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_users')
                    ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(CompanyDepartment::class, 'department_id');
    }

    public function departments()
    {
        return $this->belongsToMany(CompanyDepartment::class, 'notification_departments', 'notification_id', 'company_department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
