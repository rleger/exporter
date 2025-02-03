<?php

namespace App\Models;

use Illuminate\Support\Str;
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

    /**
     * Get only the canceled appointments.
     */
    public function getNotCanceledAppointmentsAttribute()
    {
        return $this->appointments->filter(function ($app) {
            // Use a case-insensitive check for 'annul'
            return !Str::contains(mb_strtolower($app->subject), 'annul');
        });
    }

    /**
     * Get only the canceled appointments.
     */
    public function getCanceledAppointmentsAttribute()
    {
        return $this->appointments->filter(function ($app) {
            // Use a case-insensitive check for 'annul'
            return Str::contains(mb_strtolower($app->subject), 'annul');
        });
    }

    /**
     * Compute the total canceled hours.
     */
    public function getConsultationHoursAttribute()
    {
        return $this->not_canceled_appointments->sum(function ($app) {
            return $app->duration_hours;
        });
    }

    /**
     * Compute the total canceled hours.
     */
    public function getCanceledHoursAttribute()
    {
        return $this->canceled_appointments->sum(function ($app) {
            return $app->duration_hours;
        });
    }

    /**
     * Compute the canceled hours that were not replaced.
     *
     * For each canceled appointment, we check for a replacement appointment in the same calendar.
     * A replacement is valid if:
     *  - It is not the canceled appointment itself.
     *  - Its start_date is at or before the canceled appointment's start_date.
     *  - Its end_date is at or after the canceled appointment's end_date.
     *  - It was created after the cancellation (i.e. after the canceled appointment’s updated_at).
     *  - Its subject does NOT contain 'annul'.
     */
    public function getCanceledHoursNotReplacedAttribute()
    {
        return $this->canceled_appointments->sum(function ($cancelled) {
            // Get the calendar id from the canceled appointment’s entry.
            $calendarId = $cancelled->entry->calendar->id;

            // Check for a replacement appointment in the same calendar.
            $replacementExists = Appointment::whereHas('entry', function ($query) use ($calendarId) {
                $query->where('calendar_id', $calendarId);
            })
            ->where('id', '<>', $cancelled->id)
            ->where('start_date', '<=', $cancelled->start_date)
            ->where('end_date', '>=', $cancelled->end_date)
            ->where('created_at', '>', $cancelled->updated_at)
            ->where('subject', 'not like', '%annul%')
            ->exists();

            // If a replacement exists, no time is "lost" for this cancellation.
            return $replacementExists ? 0 : $cancelled->duration_hours;
        });
    }
}
