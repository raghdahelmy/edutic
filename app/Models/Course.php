<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_category_id', 'user_id', 'name', 'description', 'image', 'short_video','type',
    'price',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function reviews()
{
    return $this->hasMany(Review::class);
}

public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }
    
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
