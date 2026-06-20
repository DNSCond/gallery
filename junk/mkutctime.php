<?php
function mkutctime(int $hour, ?int $minute = null, ?int $second = null, ?int $month = null, ?int $day = null, ?int $year = null): ?int
{
    try {
        $tz = new DateTimeZone('UTC');
        $dt = new DateTimeImmutable("now", $tz);
        // setDate and setTime naturally handle underflows and overflows instantly
        $dt = $dt->setDate($year, $month, $day)->setTime($hour, $minute, $second);
        return $dt->getTimestamp();
    } catch (DateMalformedStringException) {
        return null;
    }
}
