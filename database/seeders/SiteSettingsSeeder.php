<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

/**
 * تسجيل كل النصوص والروابط والصور القابلة للتعديل من لوحة التحكم.
 * آمن لإعادة التشغيل — لا يستبدل قيماً عدّلها الأدمن، فقط يضيف المفاتيح الناقصة.
 */
class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $position = 0;

        foreach ($this->definitions() as [$key, $group, $label, $type, $default]) {
            $position += 10;

            $setting = SiteSetting::firstOrCreate(
                ['key' => $key],
                ['group' => $group, 'label' => $label, 'type' => $type, 'value' => $default, 'position' => $position],
            );

            // تحديث البيانات الوصفية فقط (المجموعة/التسمية/الترتيب) دون لمس القيمة المحفوظة
            $setting->update(['group' => $group, 'label' => $label, 'type' => $type, 'position' => $position]);
        }

        SettingsService::flush();

        $this->command?->info('تم تسجيل إعدادات محتوى الموقع (' . SiteSetting::count() . ' مفتاح).');
    }

    /** @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: string|null}> */
    protected function definitions(): array
    {
        return [
            // ------------------------------------------------------ بيانات الموقع الأساسية
            ['site.name', 'site', 'اسم المنصة', 'text', 'منصة حاتم سميكة'],
            ['site.logo', 'site', 'شعار المنصة', 'image', null],
            ['site.developer', 'site', 'سطر حقوق التطوير في الفوتر', 'text', 'Developed by="Omar , Emad" andPowered=true'],

            // ------------------------------------------------------ الهيرو (الواجهة الرئيسية)
            ['hero.title', 'hero', 'العنوان الرئيسي', 'textarea', 'منصتك الأولى لتعلم وفهم الكيمياء بأسلوب بسيط وممتع'],
            ['hero.welcome', 'hero', 'سطر الترحيب الغامق', 'text', 'اهلاً بيك في بيتك التاني!'],
            ['hero.subtitle', 'hero', 'الوصف تحت العنوان', 'textarea', 'سواء كنت في أولى، تانية، أو تالتة ثانوي، هنا هتلاقي كل اللي تحتاجه علشان تتفوق، وتفهمها صح، وتطبقها بسهولة.'],
            ['hero.cta', 'hero', 'نص زر الاشتراك', 'text', 'اشترك دلوقتي !'],
            ['hero.image', 'hero', 'صورة المدرس في الهيرو', 'image', null],

            // ------------------------------------------------------ إحصائيات الهيرو
            ['stats.facebook_count', 'hero', 'رقم متابعين الفيسبوك', 'text', '1.0M+'],
            ['stats.facebook_label', 'hero', 'وصف متابعين الفيسبوك', 'text', 'متابعين على الفيسبوك'],
            ['stats.youtube_count', 'hero', 'رقم متابعين اليوتيوب', 'text', '2.0M+'],
            ['stats.youtube_label', 'hero', 'وصف متابعين اليوتيوب', 'text', 'متابعين على اليوتيوب'],

            // ------------------------------------------------------ ليه تشترك معانا؟
            ['why.title', 'why', 'عنوان القسم', 'text', 'ليه تشترك معانا؟'],
            ['why.card1', 'why', 'الكارت 1', 'text', 'شرح بسيط ومفهوم'],
            ['why.card2', 'why', 'الكارت 2', 'text', 'فيديوهات برسومات توضيحية'],
            ['why.card3', 'why', 'الكارت 3', 'text', 'تمارين تفاعلية على الدروس'],
            ['why.card4', 'why', 'الكارت 4', 'text', 'مرونة كاملة في المذاكرة'],
            ['why.card5', 'why', 'الكارت 5', 'text', 'اختبارات مستمرة'],
            ['why.card6', 'why', 'الكارت 6', 'text', 'محتوى متكامل ومنظم'],
            ['why.card7', 'why', 'الكارت 7', 'text', 'تحديث مستمر حسب المنهج'],
            ['why.card8', 'why', 'الكارت 8', 'text', 'مجتمع طلابي ضخم'],

            // ------------------------------------------------------ قسم الكورسات
            ['courses.title', 'courses', 'عنوان قسم الكورسات', 'text', 'كورساتنا المتاحة للعام 2025/2026'],
            ['courses.all_button', 'courses', 'نص زر عرض الكل', 'text', 'الكل'],
            ['courses.empty', 'courses', 'رسالة عدم وجود كورسات', 'text', 'سيتم اضافه المحاضرات قريبًا...'],

            // ------------------------------------------------------ المميزات (3 كروت)
            ['features.f1_title', 'features', 'الميزة 1 — العنوان', 'text', 'تنظيم الدروس والوحدات'],
            ['features.f1_text', 'features', 'الميزة 1 — الوصف', 'textarea', 'كورسات مُقسّمة لوحدات صغيرة علشان تذاكر بترتيب وتفضّل مُتابع بسهولة.'],
            ['features.f2_title', 'features', 'الميزة 2 — العنوان', 'text', 'دروس بالفيديو والصور التوضيحية'],
            ['features.f2_text', 'features', 'الميزة 2 — الوصف', 'textarea', 'شروحات مصوّرة مُفصلة مع رسومات توضيحية وأسئلة شائعة مُجاب عنها.'],
            ['features.f3_title', 'features', 'الميزة 3 — العنوان', 'text', 'تطبيقات وتمارين تفاعلية'],
            ['features.f3_text', 'features', 'الميزة 3 — الوصف', 'textarea', 'تمارين تفاعلية بعد كل درس علشان تثبت المعلومة وتختبر نفسك.'],

            // ------------------------------------------------------ عن المدرس
            ['about.title', 'about', 'عنوان القسم', 'text', 'عن م/حاتم سميكة'],
            ['about.text', 'about', 'النص داخل اللوحة الحمراء', 'textarea', 'صحصح شوية وشد حيلك معانا... هنمشيها سوا خطوة بخطوة لحد ما تلم المنهج وتبقى لعبه في ايدك .'],
            ['about.image', 'about', 'صورة المدرس في قسم عن المدرس', 'image', null],
            ['about.f1', 'about', 'ميزة 1 (بجانب الأيقونة)', 'text', 'شروحات فيديو تفصيلية.'],
            ['about.f2', 'about', 'ميزة 2 (بجانب الأيقونة)', 'text', 'تمارين تفاعلية.'],
            ['about.f3', 'about', 'ميزة 3 (بجانب الأيقونة)', 'text', 'اختبارات وواجبات دورية.'],

            // ------------------------------------------------------ الفوتر
            ['footer.bio', 'footer', 'نبذة المنصة في الفوتر', 'textarea', 'منصة تعليمية متخصصة لطلاب الثانوية العامة — شرح مبسط، امتحانات دورية، ومتابعة مستمرة لكل طالب.'],
            ['footer.copyright', 'footer', 'سطر الحقوق', 'text', 'جميع الحقوق محفوظة'],

            // ------------------------------------------------------ روابط السوشيال ميديا
            ['social.facebook', 'social', 'رابط فيسبوك', 'url', 'https://www.facebook.com/share/14cpLcJ6Yx5/'],
            ['social.instagram', 'social', 'رابط انستجرام', 'url', 'https://www.instagram.com/hatemsimika?igsh=eHA1b3FlOHliZmhj'],
            ['social.tiktok', 'social', 'رابط تيك توك', 'url', 'https://www.tiktok.com/@hatem.simika?_r=1&_t=ZS-96ITC7fEcVs'],
            ['social.youtube', 'social', 'رابط يوتيوب', 'url', 'https://youtube.com/@hatem_simika?si=mP6yMz6MdCNx63jL'],

            // ------------------------------------------------------ التواصل والدعم
            ['support.whatsapp', 'whatsapp', 'رقم واتساب الدعم الفني', 'text', '01003878666'],

            // ------------------------------------------------------ أرقام الدفع
            ['pay.vodafone', 'payments', 'رقم فودافون كاش', 'text', '01003878666'],
            ['pay.instapay', 'payments', 'عنوان انستاباي', 'text', null],

            // ------------------------------------------------------ الشات بوت
            ['chatbot.welcome', 'contact', 'رسالة الترحيب في الشات بوت', 'text', 'أهلاً بيك! اسألني عن الاشتراك أو الدفع أو المنصة.'],
            ['chatbot.fallback', 'contact', 'رد الشات بوت عند عدم الفهم', 'text', 'معرفتش أجاوبك على السؤال ده — كلمنا على واتساب وهنساعدك فوراً.'],

            // ------------------------------------------------------ صفحات الدخول والتسجيل
            ['auth.video_url', 'auth', 'رابط فيديو شرح التسجيل (يوتيوب) — اتركه فاضي لإخفاء الزر', 'url', null],
            ['auth.video_label', 'auth', 'نص زر فيديو الشرح', 'text', 'شاهد طريقة التسجيل'],
        ];
    }
}
