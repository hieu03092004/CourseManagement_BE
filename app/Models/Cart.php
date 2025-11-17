<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'cart_id';
    public $timestamps = false;

    protected $fillable = ['user_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id')
            ->with('course');
    }
}
