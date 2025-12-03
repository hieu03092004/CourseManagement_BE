<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'lesson_id';

    protected $table = 'lesson';

    protected $fillable = [
        'courses_modules_id',
        'title',
        'video_url',
        'duration',
        'order_index'
    ];

    public function quizzes()
    {
        return $this->hasMany(Quizz::class, 'lesson_id', 'lesson_id');
    }

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class, 'courses_modules_id', 'courses_modules_id');
    }
}
