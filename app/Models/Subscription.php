<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models;
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'status', 'price', 'payment_method', 'receipt'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    
    protected static function boot()
{
    parent::boot();

    static::creating(function ($subscription) {
        // لو فيه course_id نحمله وناخد السعر منه
        $course = Course::find($subscription->course_id);

        if ($course) {
            $subscription->price = $course->price;
        } else {
            $subscription->price = 0; // fallback علشان مايحصلش خطأ
        }
    });
}

}
