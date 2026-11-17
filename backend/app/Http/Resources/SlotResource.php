<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Slot */
class SlotResource extends JsonResource
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_PAST = 'past';

    public function toArray(Request $request): array
    {
        $booking = $this->activeBooking;

        return [
            'id' => $this->id,
            // ISO 8601 со смещением часового пояса приложения, напр. 2026-11-16T09:00:00+04:00
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            // past — приоритетнее: слот с началом <= текущему времени считается прошедшим.
            'status' => match (true) {
                $this->isPast() => self::STATUS_PAST,
                $booking !== null => self::STATUS_BOOKED,
                default => self::STATUS_AVAILABLE,
            },
            'booking_id' => $booking?->id,
        ];
    }
}
