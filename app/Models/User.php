<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'full_name',
        'username',
        'email',
        'phone',
        'password_hash',
        'avt',
        'gender',
        'birth_date',
        'status',
        'created_at',
        'updated_at'
    ];

    // Quan hệ với Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Review của user
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // Courses do user tạo
    public function courses()
    {
        return $this->hasMany(Course::class, 'user_id');
    }

    // Orders
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // Cart
    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
}
