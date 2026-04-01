<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    protected $guarded = ['id', '_token'];

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_users')
            ->withTimestamps();
    }
}
