<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    protected $table = 'course_modules';
    protected $primaryKey = 'courses_modules_id';
    public $timestamps = false;

    protected $fillable = [
        'courses_id', 'order_index', 'title'
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'courses_modules_id', 'courses_modules_id')
                    ->orderBy('order_index');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courses_id', 'courses_id');
    }
}

