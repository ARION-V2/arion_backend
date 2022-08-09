<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'product_id',
        'quantity',
    ];

    protected $appends = ['detail_product'];

    public function getDetailProductAttribute()
    {
        $result = Product::find($this->product_id);

        return $result;
    }



}
