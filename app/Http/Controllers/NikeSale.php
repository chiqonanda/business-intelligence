<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NikeSale extends Model
{
    protected $table = 'nike_sales';

    protected $fillable = [
        'order_id', 'gender_category', 'product_line', 'product_name', 'size',
        'units_sold', 'mrp', 'discount_applied', 'revenue',
        'order_date', 'sales_channel', 'region', 'profit',
    ];

    protected $casts = [
        'units_sold'       => 'decimal:2',
        'mrp'              => 'decimal:2',
        'discount_applied' => 'decimal:4',
        'revenue'          => 'decimal:2',
        'profit'           => 'decimal:2',
        'order_date'       => 'date',
    ];
}
