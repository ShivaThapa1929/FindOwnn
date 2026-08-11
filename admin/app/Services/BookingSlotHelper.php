<?php

namespace App\Services;

/**
 * Helpers for hourly slot ranges and merged display.
 */
class BookingSlotHelper
{
    /** Normalize TIME/H:i/H:i:s to H:i */
    public static function normalizeTime(?string $time): string
    {
        if ($time === null || $time === '') {
            return '';
        }
        return substr(trim($time), 0, 5);
    }

    /** Minutes from midnight for sort/compare (supports times after midnight). */
    public static function minutesFromMidnight(?string $time): int
    {
        $time = self::normalizeTime($time);
        if ($time === '' || !str_contains($time, ':')) {
            return 0;
        }
        [$h, $m] = array_map('intval', explode(':', $time));
        return ($h * 60) + $m;
    }

    /** True when two hourly slots are back-to-back (end of A == start of B). */
    public static function slotsAreConsecutive(?string $endA, ?string $startB): bool
    {
        return self::normalizeTime($endA) === self::normalizeTime($startB);
    }

    /**
     * Merge consecutive slot payloads into one range (API / offline booking).
     *
     * @param array<int, array{start_time:string,end_time:string}> $slots
     * @return array{start_time:string,end_time:string,hours:float,slot_count:int}|null
     */
    public static function mergeConsecutiveSlots(array $slots): ?array
    {
        if (empty($slots)) {
            return null;
        }

        usort($slots, fn($a, $b) => self::minutesFromMidnight($a['start_time'] ?? '')
            <=> self::minutesFromMidnight($b['start_time'] ?? ''));

        $normalized = [];
        foreach ($slots as $slot) {
            $start = self::normalizeTime($slot['start_time'] ?? '');
            $end = self::normalizeTime($slot['end_time'] ?? '');
            if ($start === '' || $end === '' || $end <= $start) {
                return null;
            }
            $normalized[] = ['start_time' => $start, 'end_time' => $end];
        }

        for ($i = 1, $n = count($normalized); $i < $n; $i++) {
            if (!self::slotsAreConsecutive($normalized[$i - 1]['end_time'], $normalized[$i]['start_time'])) {
                return null;
            }
        }

        $first = $normalized[0];
        $last = $normalized[count($normalized) - 1];
        $hours = self::hoursBetween($first['start_time'], $last['end_time']);

        return [
            'start_time' => $first['start_time'],
            'end_time'   => $last['end_time'],
            'hours'      => $hours,
            'slot_count' => count($normalized),
        ];
    }

    public static function hoursBetween(string $start, string $end): float
    {
        $startMin = self::minutesFromMidnight($start);
        $endMin = self::minutesFromMidnight($end);
        if ($endMin <= $startMin) {
            $endMin += 24 * 60;
        }
        return ($endMin - $startMin) / 60;
    }

    /**
     * Collapse consecutive booked grid cells that belong to the same booking.
     *
     * @param array<int, array<string, mixed>> $slots
     * @return array<int, array<string, mixed>>
     */
    public static function mergeBookedSlotDisplay(array $slots): array
    {
        if ($slots === []) {
            return $slots;
        }

        $merged = [];
        $i = 0;
        $count = count($slots);

        while ($i < $count) {
            $current = $slots[$i];

            if (empty($current['is_booked'])) {
                $merged[] = $current;
                $i++;
                continue;
            }

            $bookingId = $current['booking']['id'] ?? null;
            $mergedEnd = $current['end_time'];
            $j = $i + 1;

            while ($j < $count && !empty($slots[$j]['is_booked'])) {
                $next = $slots[$j];
                if (($next['booking']['id'] ?? null) !== $bookingId) {
                    break;
                }
                if (!self::slotsAreConsecutive($mergedEnd, $next['start_time'])) {
                    break;
                }
                $mergedEnd = $next['end_time'];
                $j++;
            }

            $span = $j - $i;
            $current['end_time'] = $mergedEnd;
            $current['merged_hours'] = $span;
            $current['is_merged_display'] = $span > 1;
            $merged[] = $current;
            $i = $j;
        }

        return $merged;
    }

    /** Standard overlap check for two time ranges on the same day. */
    public static function rangesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startA = self::normalizeTime($startA);
        $endA = self::normalizeTime($endA);
        $startB = self::normalizeTime($startB);
        $endB = self::normalizeTime($endB);

        return $startA < $endB && $endA > $startB;
    }
}
