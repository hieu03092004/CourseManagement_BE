<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'courses_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'title', 'description', 'target', 'result', 'image',
        'duration', 'updated_at', 'price', 'type', 'rating_avg',
        'total_students', 'created_at', 'discount_percent'
    ];

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'courses_id', 'courses_id')
                    ->orderBy('order_index');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'courses_id', 'courses_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

