<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        return view('teachers.index', [
            'teachers' => User::where('role', 'teacher')->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $teacher = User::firstOrNew(['employee_id' => $data['employee_id']]);
        $teacher->fill([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'role' => 'teacher',
            'status' => $data['status'],
        ]);
        if (!$teacher->exists || filled($data['password'] ?? null)) {
            $teacher->password = Hash::make($data['password'] ?: 'Teacher@123');
        }
        $teacher->save();

        return back()->with('success', 'Teacher saved successfully.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'teacher', 404);
        $teacher->delete();

        return back()->with('success', 'Teacher deleted.');
    }
}
