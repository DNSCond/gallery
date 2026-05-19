// packBools8
function packBools8(bool1, bool2, bool3, bool4, bool5, bool6, bool7, bool8) {
    return (!!bool1 << 0) | (!!bool2 << 1) | (!!bool3 << 2) | (!!bool4 << 3) | (!!bool5 << 4) | (!!bool6 << 5) | (!!bool7 << 6) | (!!bool8 << 7);
}

function unpackBools8(byte) {
    byte &= 0xff;
    return [
        (byte & (1 << 0)) !== 0, // bool1
        (byte & (1 << 1)) !== 0, // bool2
        (byte & (1 << 2)) !== 0, // bool3
        (byte & (1 << 3)) !== 0, // bool4
        (byte & (1 << 4)) !== 0, // bool5
        (byte & (1 << 5)) !== 0, // bool6
        (byte & (1 << 6)) !== 0, // bool7
        (byte & (1 << 7)) !== 0, // bool8
    ];
}
