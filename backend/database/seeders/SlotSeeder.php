<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    private const DAYS = 7;

    private const FIRST_HOUR = 9;

    private const LAST_HOUR = 17; // последний слот 17:00–18:00

    /**
     * Создаёт расписание на 7 календарных дней, считая сегодня, по 9 слотов в день.
     * Время считается в часовом поясе config('app.timezone').
     *
     * Идемпотентно: UNIQUE(starts_at) + INSERT OR IGNORE — существующие слоты
     * (и связанные с ними брони) не затрагиваются, добавляются только недостающие.
     */
    public function run(): void
    {
        $today = now()->startOfDay();
        $stamp = now()->format('Y-m-d H:i:s');
        $rows = [];

        for ($day = 0; $day < self::DAYS; $day++) {
            for ($hour = self::FIRST_HOUR; $hour <= self::LAST_HOUR; $hour++) {
                $start = $today->copy()->addDays($day)->setTime($hour, 0);

                $rows[] = [
                    'starts_at' => $start->format('Y-m-d H:i:s'),
                    'ends_at' => $start->copy()->addHour()->format('Y-m-d H:i:s'),
                    'created_at' => $stamp,
                    'updated_at' => $stamp,
                ];
            }
        }

        Slot::query()->insertOrIgnore($rows);
    }
}
