<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class TeacherProfileController extends Controller
{
    public function updateCredentials(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'teacher', 403);

        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $user->forceFill([
            'email' => $data['email'],
            'password' => $data['password'],
            'must_update_credentials' => false,
        ])->save();

        return back()->with('success', 'Email and password updated successfully.');
    }
}
