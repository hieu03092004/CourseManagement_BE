<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $table = 'lesson';

    protected $fillable = [
        'quiz_id',
        'title',
        'content',
        'true_answer',
        'order_index'
    ];
}
