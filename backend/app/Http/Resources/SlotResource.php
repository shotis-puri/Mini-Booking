<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'status' => match (true) {
                $this->isPast() => self::STATUS_PAST,
                $booking !== null => self::STATUS_BOOKED,
                default => self::STATUS_AVAILABLE,
            },
            'booking_id' => $booking?->id,
        ];
    }
}
