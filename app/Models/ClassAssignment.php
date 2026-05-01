<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'strand_id',
        'year_level',
        'section',
        'school_year',
        'semester',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(SubjectModel::class, 'subject_id');
    }

    public function strand()
    {
        return $this->belongsTo(Strand::class);
    }

    public function label(): string
    {
        return $this->subject->subject_name . ' / Grade ' . $this->year_level . ' ' . $this->strand->strand_name . '-' . $this->section;
    }
}
