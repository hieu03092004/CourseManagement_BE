<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public $timestamps = false;

    protected $table = 'question';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'quiz_id',
        'content',
        'true_answer',
        'order_index'
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'question_id');
    }
}
