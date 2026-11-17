<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlotResource;
use App\Models\Slot;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SlotController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        // Имя и email клиента не загружаем вообще — только id брони.
        $slots = Slot::query()
            ->with('activeBooking:id,slot_id,cancelled_at')
            ->orderBy('starts_at')
            ->get();

        return SlotResource::collection($slots);
    }
}
