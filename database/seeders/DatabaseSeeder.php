<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * بذر النظام: الأدوار + حساب السوبر أدمن.
     * آمن لإعادة التشغيل — لا يكرر بيانات موجودة.
     */
    public function run(): void
    {
        // ------------------------------------------------------------ الأدوار
        // super_admin: تحكم كامل | assistant: مساعد المدرس (تصحيح ومراجعة) | student: طالب
        foreach (['super_admin', 'assistant', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ------------------------------------------------------------ السوبر أدمن
        $admin = User::firstOrCreate(
            ['email' => 'admin@simika.com'],
            [
                'name' => 'حاتم سميكة إدارة المنصة',
                'phone' => '01003878666',
                'password' => Hash::make('Admin@12345'),
                'status' => User::STATUS_APPROVED,
                'approved_at' => now(),
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles(['super_admin']);

        // ------------------------------------------------------------ محتوى الموقع القابل للتعديل
        $this->call(SiteSettingsSeeder::class);

        // ------------------------------------------------------------ شجرة الشات بوت التفاعلي الأولية
        $this->call(ChatbotOptionsSeeder::class);

        $this->command?->info('السوبر أدمن: admin@simika.com / Admin@12345 — غيّر كلمة المرور في الإنتاج.');
    }
}
