<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumSubject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'strand_id',
        'subject_id',
        'year_level',
    ];

    public function strand()
    {
        return $this->belongsTo(Strand::class);
    }

    public function subject()
    {
        return $this->belongsTo(SubjectModel::class, 'subject_id');
    }
}
