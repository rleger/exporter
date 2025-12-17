<?php

namespace App\Models;

use App\Traits\HasSubjectColors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Entry extends Model implements CipherSweetEncrypted
{
    use HasSubjectColors;
    use UsesCipherSweet;

    protected $fillable = [
        'calendar_id',
        'name',
        'lastname',
        'birthdate',
        'tel',
        'email',
        'subject',
    ];

    /**
     * Normalize text fields to lowercase for case-insensitive search.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value ? mb_strtolower($value) : $value;
    }

    public function setLastnameAttribute($value): void
    {
        $this->attributes['lastname'] = $value ? mb_strtolower($value) : $value;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value ? mb_strtolower($value) : $value;
    }

    /**
     * Format name for display: Firstname (title case)
     */
    public function getFormattedNameAttribute(): string
    {
        return $this->attributes['name'] ? mb_convert_case($this->attributes['name'], MB_CASE_TITLE, 'UTF-8') : '';
    }

    /**
     * Format lastname for display: LASTNAME (uppercase)
     */
    public function getFormattedLastnameAttribute(): string
    {
        return $this->attributes['lastname'] ? mb_strtoupper($this->attributes['lastname']) : '';
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('name')
            ->addBlindIndex('name', new BlindIndex('name_index', [], 32))
            ->addField('lastname')
            ->addBlindIndex('lastname', new BlindIndex('lastname_index', [], 32))
            ->addOptionalTextField('tel')
            ->addBlindIndex('tel', new BlindIndex('tel_index', [], 32))
            ->addOptionalTextField('email')
            ->addBlindIndex('email', new BlindIndex('email_index', [], 32));
    }

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

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
     * Get only the non-canceled appointments.
     */
    public function getNotCanceledAppointmentsAttribute()
    {
        return $this->appointments->filter(function ($app) {
            return !Str::contains(mb_strtolower($app->subject), 'annul');
        });
    }

    /**
     * Get only the canceled appointments.
     */
    public function getCanceledAppointmentsAttribute()
    {
        return $this->appointments->filter(function ($app) {
            return Str::contains(mb_strtolower($app->subject), 'annul');
        });
    }

    /**
     * Compute the total consultation hours.
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
     */
    public function getCanceledHoursNotReplacedAttribute()
    {
        return $this->canceled_appointments->sum(function ($cancelled) {
            $calendarId = $cancelled->entry->calendar->id;

            $replacementExists = Appointment::whereHas('entry', function ($query) use ($calendarId) {
                $query->where('calendar_id', $calendarId);
            })
            ->where('id', '<>', $cancelled->id)
            ->where('start_date', '<=', $cancelled->start_date)
            ->where('end_date', '>=', $cancelled->end_date)
            ->where('created_at', '>', $cancelled->updated_at)
            ->where('subject', 'not like', '%annul%')
            ->exists();

            return $replacementExists ? 0 : $cancelled->duration_hours;
        });
    }
}
