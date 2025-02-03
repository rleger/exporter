<?php

namespace App\Models;

use App\Traits\HasSubjectColors;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasSubjectColors;

    protected $fillable = [
        'entry_id',
        'date',
        'subject',
        'created_at',
        'updated_at',
        'start_date',
        'end_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'datetime',
            'start_date' => 'datetime',
            'end_date'   => 'datetime',
        ];
    }

    public function getDurationHoursAttribute()
    {
        if ($this->start_date && $this->end_date) {
            // Calculate the difference in minutes, then convert to hours.
            return $this->start_date->diffInMinutes($this->end_date) / 60;
        }

        return 0;
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    public function getColorClassesAttribute()
    {
        return $this->getColorClasses('subject');
    }
}
