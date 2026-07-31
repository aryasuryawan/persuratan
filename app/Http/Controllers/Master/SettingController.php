<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('master.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $rules = [
            'app_name' => 'nullable|string|max:255',
            'lembaga_nama' => 'nullable|string|max:255',
            'lembaga_alamat' => 'nullable|string',
            'lembaga_telepon' => 'nullable|string|max:50',
            'lembaga_email' => 'nullable|email|max:255',
            'lembaga_website' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string',
            'footer_copyright' => 'nullable|string|max:255',
            'footer_links' => 'nullable|array',
            'footer_links.*.label' => 'nullable|string|max:100',
            'footer_links.*.url' => 'nullable|url|max:255',
            'app_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'app_favicon' => 'nullable|image|mimes:png,ico,webp|max:512',
        ];

        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            if (in_array($key, ['app_logo', 'app_favicon'])) {
                if ($request->hasFile($key)) {
                    $old = Setting::get($key);
                    if ($old) {
                        Storage::disk('public')->delete($old);
                    }
                    $path = $request->file($key)->store('settings', 'public');
                    Setting::set($key, $path);
                }
                continue;
            }

            if ($value !== null) {
                if ($key === 'footer_links') {
                    Setting::set($key, json_encode(array_values(array_filter($value, fn($link) => !empty($link['label']) || !empty($link['url'])))));
                } else {
                    Setting::set($key, $value);
                }
            }
        }

        return redirect()->route('master.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
