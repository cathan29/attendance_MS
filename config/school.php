<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    |
    | Add or remove section labels here when enrollment grows. These values are
    | used by student enrollment, curriculum assignments, and section reports.
    |
    */
    'sections' => range('A', 'Z'),

    /*
    |--------------------------------------------------------------------------
    | Semester Defaults
    |--------------------------------------------------------------------------
    |
    | These are defaults for new class assignments and seed data. The actual
    | dates are stored per assignment so they can be adjusted per school year.
    |
    */
    'semesters' => [
        '1st Semester' => [
            'start_date' => '2026-06-03',
            'end_date' => '2026-10-31',
        ],
        '2nd Semester' => [
            'start_date' => '2026-11-03',
            'end_date' => '2027-03-31',
        ],
    ],
];
