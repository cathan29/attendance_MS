<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\Strand;
use App\Models\Student;
use App\Models\SubjectModel;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate([
            'employee_id' => 'ADMIN-001',
        ], [
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'email' => 'admin@cipheracademy.edu',
            'must_update_credentials' => false,
        ]);

        $teacherYear = now()->year;
        $teachers = collect([
            ["CA-{$teacherYear}001", 'Maria', 'Santos', 'maria.santos@cipheracademy.edu', 'Cipher@1001'],
            ["CA-{$teacherYear}002", 'Jose', 'Reyes', 'jose.reyes@cipheracademy.edu', 'Cipher@1002'],
            ["CA-{$teacherYear}003", 'Ana', 'Cruz', 'ana.cruz@cipheracademy.edu', 'Cipher@1003'],
            ["CA-{$teacherYear}004", 'Ramon', 'Garcia', 'ramon.garcia@cipheracademy.edu', 'Cipher@1004'],
        ])->map(function ($teacher) {
            $model = User::where('email', $teacher[3])
                ->orWhere('employee_id', $teacher[0])
                ->first() ?? new User();

            $model->fill([
                'employee_id' => $teacher[0],
                'first_name' => $teacher[1],
                'middle_name' => null,
                'last_name' => $teacher[2],
                'password' => Hash::make($teacher[4]),
                'role' => 'teacher',
                'status' => 'active',
                'email' => $teacher[3],
                'must_update_credentials' => true,
            ])->save();

            return $model;
        })->values();

        $strands = collect(['ABM', 'GAS', 'HUMSS', 'STEM', 'TVL'])->mapWithKeys(function ($strand) {
            return [$strand => Strand::firstOrCreate(['strand_name' => $strand])];
        });

        $subjects = collect(['English', 'Filipino', 'Mathematics', 'Science', 'Practical Research'])->mapWithKeys(function ($subject) {
            return [$subject => SubjectModel::firstOrCreate(['subject_name' => $subject])];
        });

        $firstNames = ['Liam', 'Sophia', 'Miguel', 'Isabella', 'Noah', 'Ava', 'Gabriel', 'Mia', 'Ethan', 'Chloe', 'Lucas', 'Zoe', 'Daniel', 'Yuna', 'Nathan', 'Amara', 'Caleb', 'Luna', 'Adrian', 'Nina', 'Marco', 'Elena', 'Jasper', 'Bianca'];
        $lastNames = ['Dela Cruz', 'Mendoza', 'Aquino', 'Villanueva', 'Torres', 'Flores', 'Ramos', 'Castillo', 'Rivera', 'Bautista', 'Morales', 'Navarro'];
        $sections = [
            ['11', 'STEM', 'A'],
            ['11', 'ABM', 'B'],
            ['12', 'HUMSS', 'A'],
            ['12', 'TVL', 'C'],
        ];

        $students = collect();
        $counter = 1;
        foreach ($sections as [$year, $strandName, $section]) {
            for ($i = 0; $i < 10; $i++) {
                $students->push(Student::updateOrCreate([
                    'student_id' => 'S-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
                ], [
                    'first_name' => $firstNames[($counter - 1) % count($firstNames)],
                    'middle_name' => null,
                    'last_name' => $lastNames[($counter - 1) % count($lastNames)],
                    'strand_id' => $strands[$strandName]->id,
                    'year_level' => $year,
                    'section' => $section,
                ]));
                $counter++;
            }
        }

        $assignments = collect();
        foreach ($sections as $sectionIndex => [$year, $strandName, $section]) {
            foreach ($subjects->values() as $subjectIndex => $subject) {
                $assignments->push(ClassAssignment::updateOrCreate([
                    'teacher_id' => $teachers[($sectionIndex + $subjectIndex) % $teachers->count()]->id,
                    'subject_id' => $subject->id,
                    'strand_id' => $strands[$strandName]->id,
                    'year_level' => $year,
                    'section' => $section,
                    'school_year' => now()->year . '-' . now()->addYear()->year,
                    'semester' => '1st Semester',
                ]));
            }
        }

        $period = CarbonPeriod::create(now()->subDays(27)->startOfDay(), now()->startOfDay());
        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($students as $index => $student) {
                foreach ($subjects->values() as $subjectIndex => $subject) {
                    if (($index + $subjectIndex + $date->day) % 5 === 0) {
                        continue;
                    }

                    $statusSeed = ($index * 3) + $subjectIndex + $date->dayOfYear;
                    $status = match (true) {
                        $statusSeed % 17 === 0 => 'Absent',
                        $statusSeed % 7 === 0 => 'Late',
                        default => 'Present',
                    };

                    Attendance::updateOrCreate([
                        'student_id' => $student->student_id,
                        'attendance_date' => $date->toDateString(),
                        'subject_id' => $subject->id,
                    ], [
                        'teacher_id' => $assignments
                            ->first(fn ($assignment) => $assignment->subject_id === $subject->id
                                && $assignment->strand_id === $student->strand_id
                                && $assignment->year_level === $student->year_level
                                && $assignment->section === $student->section)
                            ?->teacher_id ?? $teachers[($subjectIndex + $index) % $teachers->count()]->id,
                        'status' => $status,
                        'remarks' => $status === 'Present' ? null : ($status === 'Late' ? 'Arrived after the bell.' : 'Needs follow-up.'),
                    ]);
                }
            }
        }
    }
}
