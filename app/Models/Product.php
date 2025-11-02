<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'description',
        'price',
        'discount_percentage',
        'stock',
        'thumbnail',
        'status',
        'position',
        'category_id',
        'deleted',
        'slug',
        'deleted_at',
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'price' => 'integer',
        'discount_percentage' => 'integer',
        'stock' => 'integer',
    ];

    // Get fake data for testing (no database)
    public static function getFakeProducts()
    {
        return [
            [
                'id' => 1,
                'title' => 'iPhone 15 Pro Max',
                'price' => 29990000,
                'discount_percentage' => 10,
                'stock' => 50,
                'status' => 'active',
                'position' => 1
            ],
            [
                'id' => 2,
                'title' => 'Samsung Galaxy S24 Ultra',
                'price' => 27990000,
                'discount_percentage' => 5,
                'stock' => 30,
                'status' => 'active',
                'position' => 2
            ],
            [
                'id' => 3,
                'title' => 'MacBook Pro M3',
                'price' => 45990000,
                'discount_percentage' => 0,
                'stock' => 15,
                'status' => 'active',
                'position' => 3
            ],
            [
                'id' => 4,
                'title' => 'iPad Pro 2024',
                'price' => 22990000,
                'discount_percentage' => 8,
                'stock' => 0,
                'status' => 'inactive',
                'position' => 4
            ],
        ];
    }
}

