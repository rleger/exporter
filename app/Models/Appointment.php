<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
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
            'start_date' => 'datetime',
            'end_date'   => 'datetime',
        ];
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    protected static $subjectColors = [
        'Annulé'                     => ['bg' => 'bg-gray-500',    'text' => 'text-gray-500'],
        'Follow-up'                  => ['bg' => 'bg-cyan-500',    'text' => 'text-cyan-800'],
        'Injection toxine botulique' => ['bg' => 'bg-red-500',     'text' => 'text-red-800'],
        'Injections autres'          => ['bg' => 'bg-indigo-400',     'text' => 'text-indigo-600'],
        'Traitement laser'           => ['bg' => 'bg-yellow-500',  'text' => 'text-yellow-800'],
        'Ulthera'                    => ['bg' => 'bg-yellow-400',  'text' => 'text-yellow-600'],
        'Injection Filler'           => ['bg' => 'bg-indigo-500',  'text' => 'text-indigo-800'],
    ];

    public function getColorClassesAttribute()
    {
        $defaultColors = [
            'bg'   => 'bg-gray-500',
            'text' => 'text-gray-800',
        ];

        foreach (self::$subjectColors as $keyword => $colors) {
            if (str_contains($this->subject, $keyword)) {
                return $colors;
            }
        }

        return $defaultColors;
    }
}
