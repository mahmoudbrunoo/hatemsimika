<?php

namespace Database\Seeders;

use App\Models\ChatbotOption;
use Illuminate\Database\Seeder;

/**
 * شجرة أولية للشات بوت التفاعلي — أمثلة يعدلها الأدمن من لوحة التحكم.
 * آمن لإعادة التشغيل: لا يضيف شيئاً لو الشجرة فيها بيانات بالفعل.
 */
class ChatbotOptionsSeeder extends Seeder
{
    public function run(): void
    {
        if (ChatbotOption::exists()) {
            return;
        }

        $whatsapp = preg_replace('/\D/', '', (string) setting('support.whatsapp', '01003878666'));
        $whatsappLink = 'https://wa.me/2' . $whatsapp;

        $subscribe = ChatbotOption::create([
            'label' => 'إزاي أشترك في كورس؟',
            'response' => "بسيطة جداً 👌\n1) اعمل حساب واستنى تفعيله.\n2) اشحن محفظتك أو استخدم كود سنتر.\n3) ادخل على الكورس ودوس اشترك.",
            'position' => 10,
        ]);

        ChatbotOption::create([
            'parent_id' => $subscribe->id,
            'label' => 'طرق الدفع المتاحة',
            'response' => 'بنقبل فودافون كاش وانستاباي — هتلاقي الأرقام في صفحة الدفع، وبعد التحويل ارفع صورة الإيصال وهنفعّل طلبك.',
            'position' => 10,
        ]);

        ChatbotOption::create([
            'parent_id' => $subscribe->id,
            'label' => 'تفعيل الكورس بعد الدفع',
            'response' => 'بعد ما ترفع الإيصال بيراجعه فريقنا وبيتفعّل الكورس في أقرب وقت — لو اتأخر عليك كلمنا واتساب.',
            'link_url' => $whatsappLink,
            'link_label' => 'كلمنا على واتساب',
            'position' => 20,
        ]);

        ChatbotOption::create([
            'label' => 'مشكلة في تسجيل الدخول',
            'response' => 'اتأكد إنك بتكتب البريد أو رقم الموبايل صح — ولو الحساب لسه مش مفعّل استنى مراجعة الإدارة. لو المشكلة مستمرة كلمنا.',
            'link_url' => $whatsappLink,
            'link_label' => 'الدعم الفني على واتساب',
            'position' => 20,
        ]);

        ChatbotOption::create([
            'label' => 'عايز أكلم الدعم',
            'response' => 'فريق الدعم متاح يومياً وهيرد عليك في أسرع وقت 👇',
            'link_url' => $whatsappLink,
            'link_label' => 'افتح شات واتساب',
            'position' => 30,
        ]);

        $this->command?->info('تم بذر شجرة الشات بوت التفاعلي الأولية.');
    }
}
