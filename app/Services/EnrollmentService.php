<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentService
{
    /** تفعيل اشتراك طالب في كورس (شراء / كود / إداري) */
    public function enroll(User $user, Course $course, string $source = 'purchase'): Enrollment
    {
        $expiresAt = $course->access_days ? now()->addDays($course->access_days) : null;

        return Enrollment::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['source' => $source, 'status' => 'active', 'expires_at' => $expiresAt],
        );
    }

    public function revoke(User $user, Course $course): void
    {
        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update(['status' => 'revoked']);
    }
}
