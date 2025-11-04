<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasQuesstion extends Model
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
        return $this->belongsTo(QuizzAttemps::class, 'quiz_attemps_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
