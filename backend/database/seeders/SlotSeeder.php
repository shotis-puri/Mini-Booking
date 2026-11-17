<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    private const DAYS = 7;

    private const FIRST_HOUR = 9;

    private const LAST_HOUR = 17;

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
