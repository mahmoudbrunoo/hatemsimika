<?php

namespace App\Support;

/**
 * كتالوج نظام الأدوار والصلاحيات الحبيبية (RBAC):
 *
 * الدور "قالب افتراضي" فقط — عند تعيينه لمستخدم تُنسخ صلاحياته الافتراضية
 * كصلاحيات مباشرة (model_has_permissions) قابلة للتخصيص فرداً فرداً من ملف
 * المستخدم في لوحة التحكم: منح صلاحية إضافية أو سحب صلاحية افتراضية.
 * لذلك لا تُربط أي صلاحيات بجداول الأدوار — المرجع النهائي هو صلاحيات المستخدم المباشرة.
 *
 * السوبر أدمن (المالك) خارج الكتالوج تماماً: يتجاوز كل الفحوص عبر Gate::before
 * في AppServiceProvider ولا يمكن لأحد تعديل دوره أو تقييد صلاحياته.
 */
class Rbac
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const TEACHER = 'teacher';

    public const STUDENT = 'student';

    public const ROLE_LABELS = [
        self::SUPER_ADMIN => 'سوبر أدمن (المالك)',
        self::ADMIN => 'أدمن',
        self::TEACHER => 'مدرس',
        self::STUDENT => 'طالب',
    ];

    /** الأدوار القابلة للتعيين من لوحة التحكم — السوبر أدمن لا يُمنح من الواجهة أبداً */
    public const ASSIGNABLE_ROLES = [
        self::STUDENT => 'طالب',
        self::TEACHER => 'مدرس',
        self::ADMIN => 'أدمن',
    ];

    /** الصلاحيات مجمعة بالموديول: المفتاح اسم الصلاحية والقيمة وصفها في الواجهة */
    public const GROUPS = [
        'الوصول العام' => [
            'admin.access' => 'دخول لوحة التحكم',
            'dashboard.view' => 'عرض لوحة الإحصائيات',
        ],
        'إدارة المستخدمين' => [
            'users.view' => 'عرض المستخدمين وبياناتهم',
            'users.update' => 'تعديل بيانات المستخدمين',
            'users.password' => 'تغيير كلمات مرور المستخدمين',
            'users.approve' => 'الموافقة على الحسابات ورفضها',
            'users.ban' => 'حظر الحسابات',
            'users.impersonate' => 'الدخول كحساب طالب (الدعم الفني)',
            'roles.manage' => 'إدارة الأدوار والصلاحيات',
        ],
        'إدارة المحتوى' => [
            'courses.manage' => 'إدارة الكورسات والمحاضرات والفيديوهات',
            'books.manage' => 'إدارة الكتب',
        ],
        'الامتحانات والتقييم' => [
            'exams.manage' => 'إدارة الامتحانات',
            'questions.manage' => 'إدارة بنك الأسئلة',
            'exams.import' => 'استيراد أسئلة الامتحانات',
            'grading.manage' => 'تصحيح الواجبات والأسئلة المقالية',
        ],
        'أسئلة الطلاب (الدعم التعليمي)' => [
            'qa.view' => 'عرض أسئلة الطلاب',
            'qa.moderate' => 'الموافقة على الأسئلة أو رفضها',
            'qa.answer' => 'الرد على أسئلة الطلاب',
        ],
        'المالية والمبيعات' => [
            'orders.manage' => 'إدارة الطلبات والإيصالات',
            'coupons.manage' => 'إدارة الكوبونات',
            'codes.manage' => 'إدارة أكواد السنتر',
            'wallet.adjust' => 'تعديل أرصدة المحافظ',
            'enrollments.manage' => 'فتح الكورسات للطلاب يدوياً',
        ],
        'النظام' => [
            'settings.manage' => 'إدارة محتوى الموقع',
            'faqs.manage' => 'إدارة الأسئلة الشائعة',
            'chatbot.manage' => 'إدارة الشات بوت',
            'audit.view' => 'عرض سجل التدقيق',
        ],
    ];

    /** @return string[] كل أسماء الصلاحيات كمصفوفة مسطحة */
    public static function all(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::GROUPS)));
    }

    /** @return array<string, string[]> القالب الافتراضي لكل دور قابل للتعيين */
    public static function defaults(): array
    {
        return [
            self::STUDENT => [],

            // المدرس: الدعم التعليمي فقط — مراجعة أسئلة الطلاب والرد عليها
            self::TEACHER => ['admin.access', 'qa.view', 'qa.moderate', 'qa.answer'],

            // الأدمن: كل الصلاحيات عدا الانتحال (يبقى حصرياً للسوبر أدمن ما لم يُمنح صراحة)
            self::ADMIN => array_values(array_diff(self::all(), ['users.impersonate'])),
        ];
    }

    /** @return string[] */
    public static function defaultsFor(string $role): array
    {
        return self::defaults()[$role] ?? [];
    }

    public static function roleLabel(?string $role): string
    {
        return self::ROLE_LABELS[$role] ?? self::ROLE_LABELS[self::STUDENT];
    }
}
