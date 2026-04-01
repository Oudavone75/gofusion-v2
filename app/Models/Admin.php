<?php

namespace App\Models;

use App\Mail\AdminResetPasswordMail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\AppCommonFunction;
use Spatie\Permission\Traits\HasPermissions;

class Admin extends Authenticatable
{
    use Notifiable, HasRoles, AppCommonFunction, HasPermissions;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'image_path',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function createdCompanies()
    {
        return $this->hasMany(Company::class, 'created_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('admin.password.reset', ['token' => $token, 'email' => $this->email]);
        $this->sendEmail(user: $this, email: new AdminResetPasswordMail($this, $url));
    }

    public function getRoleAttribute()
    {
        return $this->roles->first();
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'author');
    }
}
