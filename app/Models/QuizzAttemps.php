<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizzAttemps extends Model
{
    public $timestamps = false;

    protected $table = 'quizz_attemps';
    protected $primaryKey = 'quiz_attemps_id';

    protected $fillable = [
        'quiz_id',
        'user_id'
    ];

    public function hasquestions()
    {
        return $this->hasMany(HasQuestion::class, "quiz_attemps_id", "quiz_attemps_id");
    }

    public function quiz()
    {
        return $this->belongsTo(Quizz::class, "quiz_id", "quiz_id");
    }
}
