<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('identity.settings.manage');

        $profile = CompanyProfile::first() ?? new CompanyProfile();

        return view('identity::company-profile.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('identity.settings.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $profile = CompanyProfile::first();

        $profile ? $profile->update($data) : CompanyProfile::create($data);

        return redirect()->route('identity.company-profile.edit')->with('success', 'Profil perusahaan diperbarui.');
    }
}
