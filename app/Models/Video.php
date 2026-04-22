<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'name',
        'description',
        'video', // يخزن إما رابط خارجي أو مسار الملف المرفوع في Storage
        'duration',
        'scheduled_at'
    ];

    /**
     * التحويل التلقائي للحقول (Casting)
     * لضمان التعامل مع التاريخ ككائن من نوع Carbon
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /**
     * العلاقة مع القسم (Section)
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
