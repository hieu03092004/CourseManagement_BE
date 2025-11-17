<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'orders_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'total_price', 'payment_status', 'payment_time', 'cancel_reason', 'created_at'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'orders_id', 'orders_id')
            ->with('course');
    }
}

