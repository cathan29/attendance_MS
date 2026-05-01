<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('teachers.index', [
            'teachers' => User::where('role', 'teacher')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'middle_name' => trim((string) $request->input('middle_name')) ?: null,
            'last_name' => trim((string) $request->input('last_name')),
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÑñ .\'-]+$/'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-zÑñ .\'-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÑñ .\'-]+$/'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $duplicate = User::where('role', 'teacher')
            ->where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['first_name' => 'A teacher with the same first and last name already exists.'])
                ->withInput();
        }

        $employeeId = $this->nextEmployeeId();
        $temporaryPassword = $this->generateTemporaryPassword();

        $teacher = User::create([
            'employee_id' => $employeeId,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'password' => Hash::make($temporaryPassword),
            'role' => 'teacher',
            'status' => $data['status'],
            'must_update_credentials' => true,
        ]);
        AuditLog::record('teacher_created', "Created teacher {$teacher->employee_id}: {$teacher->last_name}, {$teacher->first_name}", $teacher, null, $teacher->only(['employee_id', 'first_name', 'last_name', 'status']));

        return back()
            ->with('success', 'Teacher account created. Share the temporary credentials securely.')
            ->with('generated_teacher', [
                'employee_id' => $employeeId,
                'password' => $temporaryPassword,
            ]);
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'teacher', 404);
        $oldValues = $teacher->only(['employee_id', 'first_name', 'last_name', 'email', 'status']);
        AuditLog::record('teacher_deleted', "Deleted teacher {$teacher->employee_id}: {$teacher->last_name}, {$teacher->first_name}", $teacher, $oldValues, null);
        $teacher->delete();

        return back()->with('success', 'Teacher deleted.');
    }

    public function resetPassword(User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'teacher', 404);

        $temporaryPassword = $this->generateTemporaryPassword();
        $teacher->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_update_credentials' => true,
        ])->save();
        AuditLog::record('teacher_password_reset', "Reset password for teacher {$teacher->employee_id}: {$teacher->last_name}, {$teacher->first_name}", $teacher);

        return back()
            ->with('success', 'Teacher password reset. Share the temporary credentials securely.')
            ->with('generated_teacher', [
                'employee_id' => $teacher->employee_id,
                'password' => $temporaryPassword,
            ]);
    }

    private function nextEmployeeId(): string
    {
        $year = now()->year;
        $prefix = "CA-{$year}";
        $latest = User::where('employee_id', 'like', $prefix . '%')
            ->orderByDesc('employee_id')
            ->value('employee_id');

        $next = $latest ? ((int) substr($latest, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generateTemporaryPassword(): string
    {
        return 'CA-' . Str::upper(Str::random(4)) . random_int(1000, 9999);
    }
}
