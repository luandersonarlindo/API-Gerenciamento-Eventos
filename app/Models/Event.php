<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'event_date',
        'event_time',
        'max_participants',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date:Y-m-d',
            'max_participants' => 'integer',
        ];
    }

    /** Usuário que cadastrou o evento. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /** Indica se o evento já atingiu a lotação máxima. */
    public function isFull(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }

        return $this->registrations()->count() >= $this->max_participants;
    }
}
