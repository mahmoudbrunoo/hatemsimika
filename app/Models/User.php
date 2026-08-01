<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    public const STATUS_PENDING = 'PENDING_REVIEW';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_BANNED = 'BANNED';

    public const STATUSES = [
        self::STATUS_PENDING => 'قيد المراجعة',
        self::STATUS_APPROVED => 'مفعل',
        self::STATUS_REJECTED => 'مرفوض',
        self::STATUS_BANNED => 'محظور',
    ];

    public const YEARS = [
        1 => 'الصف الأول الثانوي',
        2 => 'الصف الثاني الثانوي',
        3 => 'الصف الثالث الثانوي',
    ];

    public const GOVERNORATES = [
        'القاهرة', 'الجيزة', 'الإسكندرية', 'الدقهلية', 'البحر الأحمر', 'البحيرة', 'الفيوم',
        'الغربية', 'الإسماعيلية', 'المنوفية', 'المنيا', 'القليوبية', 'الوادي الجديد',
        'السويس', 'أسوان', 'أسيوط', 'بني سويف', 'بورسعيد', 'دمياط', 'الشرقية', 'جنوب سيناء',
        'كفر الشيخ', 'مطروح', 'الأقصر', 'قنا', 'شمال سيناء', 'سوهاج',
    ];

    protected $guarded = [];

    // الحقول الحساسة لا تظهر أبداً في أي تحويل JSON/مصفوفة (ردود API أو @js في الواجهة)
    protected $hidden = [
        'password',
        'remember_token',
        'id_photo_path',
        'national_id',
        'current_session_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    // ------------------------------------------------------------ العلاقات

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function mistakes(): HasMany
    {
        return $this->hasMany(Mistake::class);
    }

    public function videoViews(): HasMany
    {
        return $this->hasMany(VideoView::class);
    }

    public function lectureProgresses(): HasMany
    {
        return $this->hasMany(LectureProgress::class);
    }

    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(LoginActivity::class)->latest('logged_in_at');
    }

    public function qaThreads(): HasMany
    {
        return $this->hasMany(QaThread::class);
    }

    // ------------------------------------------------------------ مساعدات

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['super_admin', 'assistant']);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function yearLabel(): string
    {
        return self::YEARS[$this->academic_year] ?? '—';
    }

    /** أول اسمين من الاسم الرباعي */
    public function shortName(): string
    {
        return implode(' ', array_slice(preg_split('/\s+/u', trim($this->name)), 0, 2));
    }

    /** هل الطالب مشترك اشتراك ساري في الكورس؟ */
    public function isEnrolledIn(Course $course): bool
    {
        $enrollment = $this->enrollments()->where('course_id', $course->id)->first();

        return $enrollment !== null && $enrollment->isActive();
    }
}
