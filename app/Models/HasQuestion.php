<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasQuestion extends Model
{
    public $timestamps = false;

    protected $table = 'has_question';

    protected $fillable = [
        'quiz_attemps_id',
        'question_id',
        'user_choices'
    ];

    public function quizzattemp()
    {
        return $this->belongsTo(QuizzAttemps::class, 'quiz_attemps_id', 'quiz_attemps_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}
