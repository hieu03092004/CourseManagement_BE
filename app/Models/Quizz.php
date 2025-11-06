<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quizz extends Model
{
    public $timestamps = false;

    protected $table = 'quizz';
    protected $primaryKey = 'quiz_id';

    protected $fillable = [
        'lesson_id',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id', 'quiz_id');
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'quiz_id', 'quiz_id');
    }
}
