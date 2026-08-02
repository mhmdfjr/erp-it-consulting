<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('identity.settings.manage');

        $settings = SystemSetting::orderBy('key')->paginate(20);

        return view('identity::settings.index', compact('settings'));
    }

    public function edit(SystemSetting $setting): View
    {
        Gate::authorize('identity.settings.manage');

        return view('identity::settings.edit', compact('setting'));
    }

    public function update(Request $request, SystemSetting $setting): RedirectResponse
    {
        Gate::authorize('identity.settings.manage');

        $data = $request->validate([
            'value' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $decoded = json_decode($data['value'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['value' => 'Format JSON tidak valid.'])->withInput();
        }

        $setting->update([
            'value' => $decoded,
            'description' => $data['description'] ?? $setting->description,
        ]);

        return redirect()->route('identity.settings.index')->with('success', 'Setting berhasil diperbarui.');
    }
}
