<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\ClassSchedule;
use App\Models\Strand;
use App\Models\Student;
use App\Models\SubjectModel;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYear = now()->year . '-' . now()->copy()->addYear()->year;
        $studentYear = (string) now()->year;
        $curriculum = config('curriculum');
        $semester = '1st Semester';
        $semesterDates = config("school.semesters.{$semester}", []);

        User::updateOrCreate([
            'employee_id' => 'ADMIN-001',
        ], [
            'first_name' => 'Sergs Rafael',
            'middle_name' => null,
            'last_name' => 'Oriel',
            'email' => 'sergs@cipheracademy.edu',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'must_update_credentials' => false,
        ]);

        $teachers = collect([
            ['CA-T-001', 'Dianne', 'Ramirez', 'dianne.ramirez@cipheracademy.edu', 'Cipher@1001'],
            ['CA-T-002', 'John Kurt', 'Bayangat', 'johnkurt.bayangat@cipheracademy.edu', 'Cipher@1002'],
            ['CA-T-003', 'Mark Anthony', 'Ortega', 'markanthony.ortega@cipheracademy.edu', 'Cipher@1003'],
            ['CA-T-004', 'Jhustyn Jhay', 'Datuin', 'jhustynjhay.datuin@cipheracademy.edu', 'Cipher@1004'],
            ['CA-T-005', 'Adrian', 'Montemayor', 'adrian.montemayor@cipheracademy.edu', 'Cipher@1005'],
            ['CA-T-006', 'Rodel', 'Mamaril', 'rodel.mamaril@cipheracademy.edu', 'Cipher@1006'],
            ['CA-T-007', 'Jimar', 'Esmeria', 'jimar.esmeria@cipheracademy.edu', 'Cipher@1007'],
            ['CA-T-008', 'Leslie', 'Sabangan', 'leslie.sabangan@cipheracademy.edu', 'Cipher@1008'],
            ['CA-T-009', 'Jasmine', 'Miranda', 'jasmine.miranda@cipheracademy.edu', 'Cipher@1009'],
        ])->map(function (array $teacher) {
            return User::updateOrCreate([
                'employee_id' => $teacher[0],
            ], [
                'first_name' => $teacher[1],
                'middle_name' => null,
                'last_name' => $teacher[2],
                'email' => $teacher[3],
                'password' => Hash::make($teacher[4]),
                'role' => 'teacher',
                'status' => 'active',
                'must_update_credentials' => true,
            ]);
        })->values();

        $strands = collect(array_keys($curriculum))->mapWithKeys(function (string $strand) {
            return [$strand => Strand::firstOrCreate(['strand_name' => $strand])];
        });

        $subjects = collect($curriculum)
            ->flatMap(fn (array $grades) => collect($grades)->flatten())
            ->unique()
            ->values()
            ->mapWithKeys(fn (string $subject) => [
                $subject => SubjectModel::firstOrCreate(['subject_name' => $subject]),
            ]);

        $firstNames = [
            'Aaliyah', 'Adrian', 'Althea', 'Andre', 'Bianca', 'Caleb', 'Celine', 'Daniel',
            'Elaine', 'Enzo', 'Faith', 'Gabriel', 'Hannah', 'Ivan', 'Jasmine', 'Kyle',
            'Lara', 'Marco', 'Nadine', 'Nathan', 'Olivia', 'Paolo', 'Queenie', 'Rafael',
            'Samantha', 'Theo', 'Ysabel', 'Zion', 'Mika', 'Luis',
        ];
        $lastNames = [
            'Aquino', 'Bautista', 'Castillo', 'Dela Cruz', 'Flores', 'Garcia', 'Gonzales',
            'Lopez', 'Mendoza', 'Morales', 'Navarro', 'Ramos', 'Reyes', 'Rivera',
            'Santos', 'Torres', 'Valdez', 'Villanueva',
        ];

        $sections = collect([
            ['11', 'ABM', 'A'],
            ['11', 'GAS', 'A'],
            ['11', 'HUMSS', 'A'],
            ['11', 'STEM', 'A'],
            ['11', 'TVL', 'A'],
            ['12', 'ABM', 'B'],
            ['12', 'GAS', 'B'],
            ['12', 'HUMSS', 'B'],
            ['12', 'STEM', 'B'],
            ['12', 'TVL', 'B'],
        ]);

        $students = collect();
        $studentNumber = 1;

        foreach ($sections as [$yearLevel, $strandName, $section]) {
            for ($i = 0; $i < 8; $i++) {
                $students->push(Student::updateOrCreate([
                    'student_id' => $studentYear . str_pad((string) $studentNumber, 3, '0', STR_PAD_LEFT),
                ], [
                    'first_name' => $firstNames[($studentNumber - 1) % count($firstNames)],
                    'middle_name' => null,
                    'last_name' => $lastNames[($studentNumber + $i - 1) % count($lastNames)],
                    'strand_id' => $strands[$strandName]->id,
                    'year_level' => $yearLevel,
                    'section' => $section,
                ]));

                $studentNumber++;
            }
        }

        $assignments = collect();

        foreach ($sections as $sectionIndex => [$yearLevel, $strandName, $section]) {
            foreach ($curriculum[$strandName][$yearLevel] as $subjectIndex => $subjectName) {
                $assignments->push(ClassAssignment::updateOrCreate([
                    'teacher_id' => $teachers[($sectionIndex + $subjectIndex) % $teachers->count()]->id,
                    'subject_id' => $subjects[$subjectName]->id,
                    'strand_id' => $strands[$strandName]->id,
                    'year_level' => $yearLevel,
                    'section' => $section,
                    'school_year' => $schoolYear,
                    'semester' => $semester,
                    'semester_start_date' => $semesterDates['start_date'] ?? null,
                    'semester_end_date' => $semesterDates['end_date'] ?? null,
                ]));
            }
        }

        $timeSlots = [
            ['08:00', '09:00'],
            ['09:00', '10:00'],
            ['10:15', '11:15'],
            ['11:15', '12:15'],
            ['13:00', '14:00'],
            ['14:00', '15:00'],
        ];

        foreach ($assignments as $index => $assignment) {
            [$start, $end] = $timeSlots[$index % count($timeSlots)];
            ClassSchedule::updateOrCreate([
                'class_assignment_id' => $assignment->id,
                'day_of_week' => ($index % 5) + 1,
                'start_time' => $start,
            ], [
                'end_time' => $end,
                'room' => 'R-' . (200 + (($index % 12) + 1)),
            ]);
        }

        $schedules = ClassSchedule::with('assignment')->get();

        $period = CarbonPeriod::create(now()->subDays(34)->startOfDay(), now()->startOfDay());

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($students as $studentIndex => $student) {
                $strandName = $student->strand->strand_name;
                $subjectNames = $curriculum[$strandName][$student->year_level];

                foreach ($subjectNames as $subjectIndex => $subjectName) {
                    $subject = $subjects[$subjectName];
                    $assignment = $assignments->first(fn (ClassAssignment $assignment) => $assignment->subject_id === $subject->id
                        && $assignment->strand_id === $student->strand_id
                        && $assignment->year_level === $student->year_level
                        && $assignment->section === $student->section);
                    $schedule = $schedules->first(fn (ClassSchedule $schedule) => $assignment
                        && $schedule->class_assignment_id === $assignment->id
                        && (int) $schedule->day_of_week === (int) $date->dayOfWeekIso);

                    $statusSeed = $studentIndex + ($subjectIndex * 3) + $date->dayOfYear;
                    $status = match (true) {
                        $statusSeed % 19 === 0 => 'Absent',
                        $statusSeed % 8 === 0 => 'Late',
                        default => 'Present',
                    };

                    Attendance::updateOrCreate([
                        'student_id' => $student->student_id,
                        'attendance_date' => $date->toDateString(),
                        'subject_id' => $subject->id,
                    ], [
                        'teacher_id' => $assignment?->teacher_id ?? $teachers[$subjectIndex % $teachers->count()]->id,
                        'class_schedule_id' => $schedule?->id,
                        'status' => $status,
                        'remarks' => match ($status) {
                            'Late' => 'Arrived after the first period check.',
                            'Absent' => 'For adviser follow-up.',
                            default => null,
                        },
                    ]);
                }
            }
        }
    }
}
