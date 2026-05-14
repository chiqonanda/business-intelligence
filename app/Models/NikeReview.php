<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NikeReview extends Model
{
    protected $table = 'nike_reviews';

    protected $fillable = [
        'rating', 'review_date', 'location', 'username', 'review',
        'fit_feedback', 'comfort_feedback', 'recommend_feedback',
        'product_title', 'subtitle', 'color_description',
        'full_price', 'discounted', 'current_price',
        'is_promo_review', 'is_launch', 'pid', 'label',
    ];

    protected $casts = [
        'rating'         => 'decimal:1',
        'full_price'     => 'decimal:2',
        'current_price'  => 'decimal:2',
        'discounted'     => 'boolean',
        'is_promo_review'=> 'boolean',
        'is_launch'      => 'boolean',
        'review_date'    => 'date',
    ];
}
