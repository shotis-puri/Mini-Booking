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
            // active_slot_id = slot_id для активной брони. UNIQUE-индекс в БД
            // гарантирует, что при параллельных запросах вставится ровно одна запись.
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
        // Несуществующий ID -> 404.
        Booking::findOrFail($id);

        // Одним UPDATE: только если бронь ещё активна. active_slot_id = NULL
        // освобождает UNIQUE-индекс, и слот снова можно бронировать.
        // Повторная отмена ничего не меняет и тоже отвечает 204 (идемпотентно).
        Booking::query()
            ->whereKey($id)
            ->whereNull('cancelled_at')
            ->update(['cancelled_at' => now(), 'active_slot_id' => null]);

        return response()->noContent();
    }
}
