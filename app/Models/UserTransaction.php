<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    protected $guarded = ['id', '_token'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->transaction_id = $model->transaction_id ?? uniqid('txn_', true);
        });
    }
}
