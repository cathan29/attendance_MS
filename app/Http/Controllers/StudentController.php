<?php

namespace App\Http\Controllers;

use App\Models\Strand;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function index(): View
    {
        return view('students.index', [
            'students' => Student::with('strand')->orderBy('year_level')->orderBy('section')->orderBy('last_name')->get(),
            'strands' => Strand::orderBy('strand_name')->get(),
            'sections' => self::SECTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
        ]);

        Student::updateOrCreate(['student_id' => $data['student_id']], $data);

        return back()->with('success', 'Student saved successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return back()->with('success', 'Student deleted.');
    }
}
