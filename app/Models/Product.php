<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'description',
        'photo',
        'prince',
        'stock',
    ];

    public function getPhotoAttribute()
    {
        if ($this->attributes['photo']) {
            return url('') . Storage::url($this->attributes['photo']);
        } else {
            return null;
        }
    }
}
