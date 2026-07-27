<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function submissionFor(User $user): ?AssignmentSubmission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }
}
