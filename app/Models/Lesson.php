<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{

    protected $table = 'lesson';

    protected $fillable = [
        'courses_module_id',
        'title',
        'video_url',
        'duration',
        'order_index'
    ];
}
