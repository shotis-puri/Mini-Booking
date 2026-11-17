<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function futureSlot(): Slot
    {
        $start = now()->addDay()->startOfHour();

        return Slot::create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);
    }

    public function test_free_future_slot_can_be_booked(): void
    {
        $slot = $this->futureSlot();

        $response = $this->postJson('/api/bookings', [
            'slot_id' => $slot->id,
            'customer_name' => 'Anna',
            'customer_email' => 'anna@example.com',
        ]);

        $response->assertCreated()->assertJsonStructure(['id']);

        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('id'),
            'slot_id' => $slot->id,
            'active_slot_id' => $slot->id,
            'customer_email' => 'anna@example.com',
            'cancelled_at' => null,
        ]);
    }

    public function test_repeated_request_for_booked_slot_returns_409_and_creates_no_second_booking(): void
    {
        $slot = $this->futureSlot();

        $this->postJson('/api/bookings', [
            'slot_id' => $slot->id,
            'customer_name' => 'Anna',
            'customer_email' => 'anna@example.com',
        ])->assertCreated();

        $this->postJson('/api/bookings', [
            'slot_id' => $slot->id,
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
        ])->assertStatus(409)->assertJsonStructure(['message']);

        $this->assertSame(1, Booking::where('slot_id', $slot->id)->whereNull('cancelled_at')->count());
        $this->assertDatabaseMissing('bookings', ['customer_email' => 'bob@example.com']);
    }
}
