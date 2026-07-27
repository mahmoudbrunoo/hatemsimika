<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings.all'));
        static::deleted(fn () => Cache::forget('site_settings.all'));
    }
}
