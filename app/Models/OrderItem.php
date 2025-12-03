<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_item';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['courses_id', 'orders_id', 'unit_price', 'expired_at'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courses_id', 'courses_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }
}
