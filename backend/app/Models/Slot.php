<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    protected $fillable = ['starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Текущая активная (не отменённая) бронь слота. */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->whereNull('cancelled_at');
    }

    /** Слот прошёл, если время начала <= текущему серверному времени. */
    public function isPast(): bool
    {
        return $this->starts_at->lte(now());
    }
}
