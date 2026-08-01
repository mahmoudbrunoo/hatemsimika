<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * خيار في شجرة الشات بوت التفاعلي.
 * كل خيار: زر بعنوان + رد اختياري (نص/HTML) + رابط اختياري + خيارات فرعية بلا حدود.
 */
class ChatbotOption extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('chatbot_options.tree'));
        static::deleted(fn () => Cache::forget('chatbot_options.tree'));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /**
     * الشجرة الكاملة النشطة للواجهة — مخبأة وتتصفر تلقائياً مع أي تعديل.
     *
     * @return array<int, array{id: int, label: string, response: string|null, link_url: string|null, link_label: string|null, children: array}>
     */
    public static function tree(): array
    {
        try {
            return Cache::rememberForever('chatbot_options.tree', function () {
                $grouped = static::where('is_active', true)
                    ->orderBy('position')->orderBy('id')
                    ->get(['id', 'parent_id', 'label', 'response', 'link_url', 'link_label'])
                    ->groupBy(fn ($option) => $option->parent_id ?? 0);

                $build = function (int $parentId) use (&$build, $grouped): array {
                    return ($grouped[$parentId] ?? collect())->map(fn ($option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'response' => $option->response,
                        'link_url' => $option->link_url,
                        'link_label' => $option->link_label,
                        'children' => $build($option->id),
                    ])->values()->all();
                };

                return $build(0);
            });
        } catch (\Throwable) {
            return []; // قبل تشغيل الهجرات
        }
    }
}
