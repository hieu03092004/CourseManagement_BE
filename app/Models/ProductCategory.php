<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'products_category';

    protected $fillable = [
        'title',
        'parent_id',
        'description',
        'thumbnail',
        'status',
        'position',
        'deleted',
        'slug',
        'deleted_at',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    // Example: Get fake data for testing
    public static function getFakeStatistics()
    {
        return [
            'total' => 25,
            'active' => 18,
            'inactive' => 7,
        ];
    }
}

