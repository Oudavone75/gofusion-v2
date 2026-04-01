<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'status',
        'comment',
        'mark_as_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
