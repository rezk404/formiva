<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->forceFill($request->validated())->save();

        return back()->with('status', 'Profile details updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $newPassword = $request->string('password')->toString();
        $request->user()->forceFill(['password' => Hash::make($newPassword)])->save();
        Auth::logoutOtherDevices($newPassword);
        $request->session()->regenerate();

        return back()->with('status', 'Password updated and other sessions signed out.');
    }
}
