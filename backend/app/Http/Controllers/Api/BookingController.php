<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $slot = Slot::findOrFail($data['slot_id']);

        if ($slot->isPast()) {
            return response()->json(['message' => 'Этот слот уже прошёл, его нельзя забронировать.'], 409);
        }

        try {
            $booking = Booking::create([
                'slot_id' => $slot->id,
                'active_slot_id' => $slot->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Этот слот уже занят. Выберите другое время.'], 409);
        }

        return response()->json(['id' => $booking->id], 201);
    }

    public function destroy(int $id): Response
    {
        Booking::findOrFail($id);

        Booking::query()
            ->whereKey($id)
            ->whereNull('cancelled_at')
            ->update(['cancelled_at' => now(), 'active_slot_id' => null]);

        return response()->noContent();
    }
}
