<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NikeProduct extends Model
{
    protected $table = 'nike_products';

    protected $fillable = [
        'uniq_id', 'name', 'sub_title', 'brand', 'model', 'color',
        'price', 'currency', 'availability', 'description',
        'avg_rating', 'review_count', 'images', 'available_sizes', 'url',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'avg_rating' => 'decimal:2',
    ];
}
