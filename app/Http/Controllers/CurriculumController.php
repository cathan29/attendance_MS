<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\Strand;
use App\Models\SubjectModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    private const SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function index(): View
    {
        return view('curriculum.index', [
            'assignments' => ClassAssignment::with(['teacher', 'subject', 'strand'])
                ->orderBy('year_level')
                ->orderBy('section')
                ->latest()
                ->get(),
            'teachers' => User::where('role', 'teacher')->where('status', 'active')->orderBy('last_name')->get(),
            'subjects' => SubjectModel::orderBy('subject_name')->get(),
            'strands' => Strand::orderBy('strand_name')->get(),
            'sections' => self::SECTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
        ]);

        ClassAssignment::firstOrCreate($data);

        return back()->with('success', 'Class assignment saved.');
    }

    public function destroy(ClassAssignment $curriculum): RedirectResponse
    {
        $curriculum->delete();

        return back()->with('success', 'Class assignment removed.');
    }
}
