<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WithdrawalRequest extends Model
{
    protected $guarded = ['id', '_token'];

    protected function apiResponse(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, JSON_THROW_ON_ERROR),
            set: fn(array $value) => json_encode($value, JSON_THROW_ON_ERROR),
        );
    }
}
