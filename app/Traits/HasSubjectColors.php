<?php

namespace App\Traits;

trait HasSubjectColors
{
    protected static $subjectColors = [
        'Annulé'                     => ['bg' => 'bg-gray-500',    'text' => 'text-gray-600  line-through '],
        'Follow-up'                  => ['bg' => 'bg-cyan-500',    'text' => 'text-cyan-600'],
        'Injection toxine botulique' => ['bg' => 'bg-red-500',     'text' => 'text-red-600'],
        'Injection Filler'           => ['bg' => 'bg-indigo-500',  'text' => 'text-indigo-600'],
        'Injection'                  => ['bg' => 'bg-indigo-500',  'text' => 'text-indigo-600'],
        'Traitement laser'           => ['bg' => 'bg-yellow-500',  'text' => 'text-yellow-600'],
        'Ulthera'                    => ['bg' => 'bg-yellow-400',  'text' => 'text-yellow-500'],
    ];

    public function getColorClasses($attribute)
    {
        $defaultColors = [
            'bg'   => 'bg-gray-500',
            'text' => 'text-gray-800',
        ];

        $value = $this->$attribute;

        foreach (self::$subjectColors as $keyword => $colors) {
            if (str_contains($value, $keyword)) {
                return $colors;
            }
        }

        return $defaultColors;
    }
}
