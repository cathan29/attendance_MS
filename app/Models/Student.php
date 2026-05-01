<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'first_name',
        'middle_name',
        'last_name',
        'strand_id',
        'year_level',
        'section',
    ];

    public function strand()
    {
        return $this->belongsTo(Strand::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }
}
