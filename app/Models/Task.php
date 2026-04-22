<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'description',
        'answer',
        'submitted_at',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

