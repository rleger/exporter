<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'entry_id',
        'subject',
        'date',
    ];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
