<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = [
        'calendar_id',
        'name',
        'lastname',
        'birthdate',
        'tel',
        'email',
        'description',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
