<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'review_id';
    public $timestamps = false;

    protected $fillable = [
        'courses_id', 'user_id', 'context', 'rating', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

