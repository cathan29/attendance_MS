<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $fillable = [
        'class_assignment_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
    ];

    public function assignment()
    {
        return $this->belongsTo(ClassAssignment::class, 'class_assignment_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function dayName(): string
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$this->day_of_week - 1] ?? 'Unknown';
    }
}
