<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $guarded = [];

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(VideoView::class);
    }

    public function durationHuman(): string
    {
        $h = intdiv((int) $this->duration_seconds, 3600);
        $m = intdiv(((int) $this->duration_seconds) % 3600, 60);
        $s = ((int) $this->duration_seconds) % 60;

        return $h > 0 ? sprintf('%02d:%02d:%02d', $h, $m, $s) : sprintf('%02d:%02d', $m, $s);
    }

    /** معرف يوتيوب من الرابط أو القيمة المباشرة */
    public function youtubeId(): ?string
    {
        if ($this->source !== 'youtube' || ! $this->url) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([\w-]{11})~', $this->url, $m)) {
            return $m[1];
        }

        return preg_match('/^[\w-]{11}$/', $this->url) ? $this->url : null;
    }
}
