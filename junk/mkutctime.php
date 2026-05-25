<?php
function mkutctime(int $hour, ?int $minute = null, ?int $second = null, ?int $month = null, ?int $day = null, ?int $year = null): int
{
    $time = time();
    //(int $year = 2024, int $month = 1, int $day = 1, int $hour = 0, int $minute = null, ?int $second = 0): int
    $year ??= +gmdate('Y', $time);
    $month ??= +gmdate('m', $time);
    $day ??= +gmdate('d', $time);
    $hour ??= +gmdate('H', $time);
    $minute ??= +gmdate('i', $time);
    $second ??= +gmdate('s', $time);
    // 1. Roll over seconds to minutes
    if ($second < 0 || $second >= 60) {
        $minute += floor($second / 60);
        $second = $second % 60;
        if ($second < 0) $second += 60;
    }

    // 2. Roll over minutes to hours
    if ($minute < 0 || $minute >= 60) {
        $hour += floor($minute / 60);
        $minute = $minute % 60;
        if ($minute < 0) $minute += 60;
    }

    // 3. Roll over hours to days
    if ($hour < 0 || $hour >= 24) {
        $day += floor($hour / 24);
        $hour = $hour % 24;
        if ($hour < 0) $hour += 24;
    }

    // 4. Roll over months to years
    // PHP months are 1-indexed, so we shift to 0-indexed (0-11) for the math, then shift back
    $month -= 1;
    $year += floor($month / 12);
    $month = $month % 12;
    if ($month < 0) {
        $month += 12;
    }
    $month += 1; // Back to 1-12

    // 5. Roll over days to months/years (This is the hardest part because month lengths vary)
    $days_in_months = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    // Handle positive day rollover (e.g., day 45 in January)
    while ($day > ($limit = ($month == 2 && _is_leap_year_helper($year)) ? 29 : $days_in_months[$month])) {
        $day -= $limit;
        $month++;
        if ($month > 12) {
            $month = 1;
            $year++;
        }
    }

    // Handle negative day rollover (e.g., day -5)
    while ($day <= 0) {
        $month--;
        if ($month < 1) {
            $month = 12;
            $year--;
        }
        $limit = ($month == 2 && _is_leap_year_helper($year)) ? 29 : $days_in_months[$month];
        $day += $limit;
    }

    // Now that everything is normalized, calculate total days from 1970
    $total_days = 0;
    if ($year >= 1970) {
        for ($y = 1970; $y < $year; $y++) {
            $total_days += _is_leap_year_helper($y) ? 366 : 365;
        }
    } else {
        for ($y = 1969; $y >= $year; $y--) {
            $total_days -= _is_leap_year_helper($y) ? 366 : 365;
        }
    }

    for ($m = 1; $m < $month; $m++) {
        if ($m == 2 && _is_leap_year_helper($year)) {
            $total_days += 29;
        } else {
            $total_days += $days_in_months[$m];
        }
    }

    $total_days += ($day - 1);

    return ($total_days * 86400) + ($hour * 3600) + ($minute * 60) + $second;
}


function _is_leap_year_helper($year): bool
{
    return (($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0);
}
