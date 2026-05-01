<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strand extends Model
{
    protected $fillable = ['strand_name'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
