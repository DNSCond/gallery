<?php
function jssort(array &$array, ?string $propertyKey = null): array
{
    // Use a custom comparator that mimics JS string conversion
    usort($array, function ($a, $b) use ($propertyKey) {
        // Convert both values to strings
        $strA = (string)(is_string($propertyKey) ? $a[$propertyKey] : $a);
        $strB = (string)(is_string($propertyKey) ? $b[$propertyKey] : $b);
        $min = min($lenA = strlen($strA), $lenB = strlen($strB));
        for ($i = 0; $i < $min; $i++) {
            $cx = ord($strA[$i]);
            $cy = ord($strB[$i]);
            if ($cx < $cy) return -1;
            if ($cx > $cy) return +1;
        }
        if ($lenA < $lenB) return -1;
        if ($lenA > $lenB) return +1;
        return 0;
    });
    return $array;
}

function lexiosort(
    array         &$array,
    ?string       $propertyKey = null,
    bool          $insensitive = false,
    null|callable $exceptions = null,
    null|callable $keyFor = null): array
{
    // Use a custom comparator that mimics JS string conversion
    usort($array, function ($a, $b) use ($propertyKey, $insensitive, $exceptions, $keyFor) {
        // Convert both values to strings
        $propA = (is_string($propertyKey) ? $a[$propertyKey] : $a);
        $propB = (is_string($propertyKey) ? $b[$propertyKey] : $b);

        if ($propA === null && $propB === null) return +0;
        if ($propA === null) return +1;
        if ($propB === null) return -1;
        if (is_callable($exceptions)) {
            if (($exception = $exceptions($propA, $propB, $a, $b)) !== 0) {
                return $exception;
            }
        }
        if (is_callable($keyFor)) {
            if (is_string($exception = $keyFor($propA, $a))) $propA = $exception;
            if (is_string($exception = $keyFor($propB, $b))) $propB = $exception;
        }
        $strA = (string)$propA;
        $strB = (string)$propB;
        $min = min($lenA = strlen($strA), $lenB = strlen($strB));
        for ($i = 0; $i < $min; $i++) {
            $cx = ord($strA[$i]);
            $cy = ord($strB[$i]);
            if ($insensitive) {
                if ($cx >= 0x61 && $cx <= 0x7a) $cx = $cx - 0b100000;
                if ($cy >= 0x61 && $cy <= 0x7a) $cy = $cy - 0b100000;
            }
            if ($cx < $cy) return -1;
            if ($cx > $cy) return +1;
        }
        if ($lenA < $lenB) return -1;
        if ($lenA > $lenB) return +1;
        return 0;
    });
    return $array;
}
