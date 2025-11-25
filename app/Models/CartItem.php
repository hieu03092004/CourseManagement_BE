<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_item';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['cart_id', 'courses_id', 'created_at'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courses_id', 'courses_id');
    }
}
