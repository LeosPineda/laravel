<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_name',
        'brand_logo',
        'brand_image',
        'qr_code_image',
        'qr_mobile_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relationship to Orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
