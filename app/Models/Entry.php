<?php

namespace App\Models;

use App\Traits\HasSubjectColors;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasSubjectColors;

    protected $fillable = [
        'calendar_id',
        'name',
        'lastname',
        'birthdate',
        'tel',
        'email',
        'subject',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getColorClassesAttribute()
    {
        return $this->getColorClasses('subject');
    }
}
