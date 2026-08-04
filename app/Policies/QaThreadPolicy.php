<?php

namespace App\Policies;

use App\Models\QaThread;
use App\Models\User;

/**
 * صلاحيات التعليق على أسئلة الدروس:
 * قفل الموضوع يمنع تعليقات الطلاب — ويبقى الرد متاحاً حصراً للأدمن والمدرسين
 * (أصحاب صلاحيات الدعم التعليمي) وصاحب السؤال نفسه ليتابع على استفساره.
 * السوبر أدمن يتجاوز الفحص كله عبر Gate::before.
 */
class QaThreadPolicy
{
    public function reply(User $user, QaThread $thread): bool
    {
        // الأدمن والمدرس: الرد متاح دائماً حتى مع القفل
        if ($user->can('qa.answer') || $user->can('qa.moderate')) {
            return true;
        }

        // السؤال المرفوض لا يستقبل أي ردود
        if ($thread->status === QaThread::STATUS_REJECTED) {
            return false;
        }

        // صاحب السؤال مستثنى من القفل — يتابع على سؤاله حتى قبل نشره
        if ($user->id === $thread->user_id) {
            return true;
        }

        // باقي الطلاب: السؤال منشور + الموضوع مفتوح + مشترك في الكورس
        return $thread->status === QaThread::STATUS_APPROVED
            && ! $thread->is_locked
            && $user->isEnrolledIn($thread->lecture->course);
    }
}
