<?php // packBools8
function packBools8(
    bool $bool1 = false, bool $bool2 = false, bool $bool3 = false, bool $bool4 = false,
    bool $bool5 = false, bool $bool6 = false, bool $bool7 = false, bool $bool8 = false): int
{return ($bool1 << 0) | ($bool2 << 1) | ($bool3 << 2) | ($bool4 << 3) | ($bool5 << 4) | ($bool6 << 5) | ($bool7 << 6) | ($bool8 << 7);}

function unpackBools8(int $byte): array
{
    $byte &= 0xff;
    return [
        ($byte & (1 << 0)) !== 0, // bool1
        ($byte & (1 << 1)) !== 0, // bool2
        ($byte & (1 << 2)) !== 0, // bool3
        ($byte & (1 << 3)) !== 0, // bool4
        ($byte & (1 << 4)) !== 0, // bool5
        ($byte & (1 << 5)) !== 0, // bool6
        ($byte & (1 << 6)) !== 0, // bool7
        ($byte & (1 << 7)) !== 0, // bool8
    ];
}
