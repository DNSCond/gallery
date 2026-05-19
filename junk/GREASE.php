<?php // Generate Random extensions And Sustain Extensibility
use Random\RandomException;

/**
 * @throws RandomException
 */
function createGREASEJsonKey(int $length = 6): string
{
    $left = floor($length / 2);
    $right = ceil($length / 2);
    $result = '';
    for ($i = 0; $i < $left; $i++) {
        $result .= chr(random_int(0x21, 0x7e));
    }
    foreach (str_split('grease') as $item) {
        if (random_int(0, 99) >= 50) $item = strtoupper($item);
        $result .= $item;
    }
    for ($i = 0; $i < $right; $i++) {
        $result .= chr(random_int(0x21, 0x7e));
    }
    return $result;
}

/**
 * @throws RandomException
 */
function createGREASEJsonValue(): mixed
{
    return _recursive_createGREASEJsonValue(random_int(0, 6));
}

/**
 * @throws RandomException
 */
function createGREASEJsonNumberValue(): int|float
{
    $unpacked = unpack('d', random_bytes(8))[1];
    if (is_nan($unpacked) || is_infinite($unpacked)) return 0;
    return $unpacked;
}

/**
 * @throws RandomException
 */
function _recursive_createGREASEJsonValue(int $max_depth = 0, int $depth = 0): mixed
{
    switch (random_int(($depth < $max_depth) ? 0 : 2, 6)) {
        case 4: // number
            return createGREASEJsonNumberValue();
        case 5: // string
            $result = '';
            $length = random_int(3, 16);
            for ($i = 0; $i < $length; $i++) {
                $result .= chr(random_int(0x21, 0x7e));
            }
            return $result;
        case 6: // int
            return random_int(-1280000000, +1280000000);
        case 2: // null
            return null;
        case 3: // boolean
            return random_int(0, 99) >= 50;
        case 0: // object
            $result = new stdClass();
            $length = random_int(1, max(1, 6 - $depth));
            for ($i = 0; $i < $length; $i++) {
                $result->{createGREASEJsonKey()} = _recursive_createGREASEJsonValue($max_depth, $depth + 1);
            }
            return $result;
        case 1: // array
            $result = array();
            $length = random_int(1, max(1, 6 - $depth));
            for ($i = 0; $i < $length; $i++) {
                $result[] = _recursive_createGREASEJsonValue($max_depth, $depth + 1);
            }
            return $result;
    }
    return null;
}
