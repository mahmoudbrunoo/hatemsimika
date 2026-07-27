<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** إدارة نصوص وصور وروابط كل صفحات الموقع من مكان واحد */
class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'groups' => SiteSetting::orderBy('group')->orderBy('position')->orderBy('id')
                ->get()
                ->groupBy('group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $values = (array) $request->input('settings', []);
        $files = (array) $request->file('settings_files', []);

        foreach (SiteSetting::all() as $setting) {
            if (isset($files[$setting->key]) && $files[$setting->key] !== null) {
                $request->validate([
                    "settings_files.{$setting->key}" => ['image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
                ]);

                $setting->update(['value' => $files[$setting->key]->store('settings', 'public')]);
                continue;
            }

            if (array_key_exists($setting->key, $values)) {
                $setting->update(['value' => $values[$setting->key] !== '' ? $values[$setting->key] : null]);
            }
        }

        SettingsService::flush();

        return back()->with('status', 'تم حفظ إعدادات الموقع.');
    }
}
