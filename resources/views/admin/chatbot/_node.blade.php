{{-- عقدة واحدة في شجرة الشات بوت + فروعها بشكل متداخل (recursive) --}}
@php $children = $grouped[$option->id] ?? collect(); @endphp

<div>
    <div class="card-pad" x-data="{ edit: false }">
        <div x-show="!edit">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="font-extrabold text-slate-900 dark:text-white">{{ $option->label }}</p>
                    @if ($option->response)
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">
                            {{ \Illuminate\Support\Str::limit(strip_tags($option->response), 160) }}
                        </p>
                    @endif
                    @if ($option->link_url)
                        <p class="mt-2 text-xs font-semibold text-slate-400" dir="ltr">
                            🔗 {{ $option->link_label ?: 'رابط' }} — {{ \Illuminate\Support\Str::limit($option->link_url, 60) }}
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    <div class="flex items-center gap-1.5">
                        @if ($children->isNotEmpty())
                            <span class="badge-gray">{{ $children->count() }} فرعي</span>
                        @endif
                        @if ($option->is_active)
                            <span class="badge-green">ظاهر</span>
                        @else
                            <span class="badge-gray">مخفي</span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.chatbot.index', ['parent' => $option->id]) }}#add-option"
                           class="btn-secondary btn-sm" title="إضافة خيار فرعي جوه الخيار ده">＋ فرعي</a>
                        <button type="button" @click="edit = true" class="btn-secondary btn-sm">تعديل</button>
                        <form method="POST" action="{{ route('admin.chatbot.destroy', $option) }}"
                              onsubmit="return confirm('حذف الخيار وكل الخيارات الفرعية بداخله نهائياً؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- تعديل سريع --}}
        <form x-show="edit" x-cloak method="POST" action="{{ route('admin.chatbot.update', $option) }}" class="grid gap-3 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <div>
                <label for="label-{{ $option->id }}" class="label">نص الزر</label>
                <input id="label-{{ $option->id }}" name="label" type="text" maxlength="120"
                       value="{{ $option->label }}" class="input" required>
            </div>
            <div>
                <label for="parent-{{ $option->id }}" class="label">مكان الخيار</label>
                <select id="parent-{{ $option->id }}" name="parent_id" class="input">
                    <option value="">— خيار رئيسي في القائمة الأساسية —</option>
                    @foreach ($flat as $row)
                        <option value="{{ $row['option']->id }}"
                                @selected($option->parent_id === $row['option']->id)
                                @disabled($row['option']->id === $option->id || in_array($row['option']->id, $descendants[$option->id] ?? [], true))>
                            {{ str_repeat('— ', $row['depth']) }}{{ $row['option']->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="response-{{ $option->id }}" class="label">الرد (اختياري)</label>
                <textarea id="response-{{ $option->id }}" name="response" rows="3" class="input">{{ $option->response }}</textarea>
            </div>
            <div>
                <label for="link-url-{{ $option->id }}" class="label">رابط الزر (اختياري)</label>
                <input id="link-url-{{ $option->id }}" name="link_url" type="url" maxlength="500"
                       value="{{ $option->link_url }}" class="input" dir="ltr">
            </div>
            <div>
                <label for="link-label-{{ $option->id }}" class="label">نص زر الرابط</label>
                <input id="link-label-{{ $option->id }}" name="link_label" type="text" maxlength="120"
                       value="{{ $option->link_label }}" class="input">
            </div>
            <div>
                <label for="position-{{ $option->id }}" class="label">ترتيب الظهور</label>
                <input id="position-{{ $option->id }}" name="position" type="number" min="0"
                       value="{{ $option->position }}" class="input" dir="ltr">
            </div>
            <div class="flex items-center gap-3 self-end">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($option->is_active)
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                    ظاهر للطلاب
                </label>
                <button type="submit" class="btn-primary btn-sm">حفظ التعديل</button>
                <button type="button" @click="edit = false" class="btn-secondary btn-sm">إلغاء</button>
            </div>
        </form>
    </div>

    {{-- الفروع --}}
    @if ($children->isNotEmpty())
        <div class="mr-4 mt-3 space-y-3 border-r-2 border-dashed border-slate-200 pr-4 dark:border-night-700">
            @foreach ($children as $child)
                @include('admin.chatbot._node', ['option' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
