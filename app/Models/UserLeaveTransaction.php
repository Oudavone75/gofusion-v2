<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLeaveTransaction extends Model
{
    const LEAVE_TYPE_CREDIT = 'credit';
    const LEAVE_TYPE_DEBIT = 'debit';
    protected $guarded = ['id', '_token'];
}
