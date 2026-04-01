<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $guarded = ['id', '_token'];

    protected $fillable = [
        'platform',
        'latest_version',
        'min_supported_version',
        'force_update',
        'update_url',
    ];

    protected $casts = [
        'force_update' => 'boolean'
    ];

    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', strtolower($platform));
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d M Y, h:i A');
    }
}
