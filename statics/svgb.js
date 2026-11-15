// parseSVGB
export function parseSVGB(arrayBuffer) {
    const view = new DataView(arrayBuffer);
    let offset = 0;

    // 1. Verify Magic Bytes "SVGB" (4 bytes)
    const magic = String.fromCharCode(
        view.getUint8(offset++),
        view.getUint8(offset++),
        view.getUint8(offset++),
        view.getUint8(offset++)
    );
    if (magic !== "SVGB") {
        throw new Error("Invalid file format: Expected 'SVGB' magic bytes.");
    }

    // 3. Read Canvas Setup Opcode ('>' = 0x3E / 62)
    const setupOp = view.getInt8(offset++);
    if (setupOp !== 0x3E) {
        throw new Error("Expected canvas setup opcode '>' at header start.");
    }

    // Read 6 float32 values for width, height, viewBox (minX, minY, width, height)
    const width = view.getFloat32(offset, true);
    offset += 4;
    const height = view.getFloat32(offset, true);
    offset += 4;
    const vbX = view.getFloat32(offset, true);
    offset += 4;
    const vbY = view.getFloat32(offset, true);
    offset += 4;
    const vbW = view.getFloat32(offset, true);
    offset += 4;
    const vbH = view.getFloat32(offset, true);
    offset += 4;

    const canvasHeader = {
        width, height,
        viewBox: {
            x: vbX,
            y: vbY,
            w: vbW,
            h: vbH,
        }
    };

    const commands = [];

    // Default stateful attributes
    const state = {
        fillColor: 0x000000FF,  // Default black RGBA
        strokeColor: 0x000000FF,
        strokeWidth: 0.0,
        antialias: false,
    };

    // 4. Parse the remaining command and state opcodes
    while (offset < view.byteLength) {
        const opcode = view.getInt8(offset++);

        if (opcode > 0) {
            // Positive opcode: Drawing command or path element
            const char = String.fromCharCode(opcode);

            if (char === 'M' || char === 'm' || char === 'L' || char === 'l') {
                const x = view.getFloat32(offset, true);
                offset += 4;
                const y = view.getFloat32(offset, true);
                offset += 4;
                commands.push({type: char, x, y, state: {...state}});
            } else if (char === 'Z' || char === 'z') {
                commands.push({type: char, state: {...state}});
            } else if (char === '[') { // Rect: x, y, width, height
                const x = view.getFloat32(offset, true);
                offset += 4;
                const y = view.getFloat32(offset, true);
                offset += 4;
                const w = view.getFloat32(offset, true);
                offset += 4;
                const h = view.getFloat32(offset, true);
                offset += 4;
                commands.push({type: 'rect', x, y, w, h, state: {...state}});
            } else if (char === '(') { // Circle: cx, cy, r
                const cx = view.getFloat32(offset, true);
                offset += 4;
                const cy = view.getFloat32(offset, true);
                offset += 4;
                const r = view.getFloat32(offset, true);
                offset += 4;
                commands.push({type: 'circle', cx, cy, r, state: {...state}});
            }
        } else {
            // Negative opcode: Stateful attribute modifier
            const attrChar = String.fromCharCode(-opcode);

            if (attrChar === 'c') { // Fill color (uint32le)
                state.fillColor = view.getUint32(offset, true);
                offset += 4;
            } else if (attrChar === 's') { // Stroke color (uint32le)
                state.strokeColor = view.getUint32(offset, true);
                offset += 4;
            } else if (attrChar === 'w') { // Stroke width (float32)
                state.strokeWidth = view.getFloat32(offset, true);
                offset += 4;
            } else if (attrChar === 'a') { // Anti-aliasing toggle (uint8: 1 or 0)
                state.antialias = view.getUint8(offset++) === 1;
            } else if (attrChar === '/') { // Comment / Metadata skip
                const nameLen = view.getUint8(offset++);
                offset += nameLen; // Skip name bytes
                const valLen = view.getUint16(offset, true);
                offset += 2;
                offset += valLen; // Skip value bytes
            }
        }
    }

    return {header: canvasHeader, commands};
}

Uint8Array.fromModStringAndNumbers = function (array) {
    return new this(Array.from(array, elem => {
        if (elem === null) throw TypeError('null Received where Integer need to be gotten');
        switch (typeof elem) {
            case "undefined":
                throw TypeError('Undefined Received where Integer need to be gotten');
            case "bigint":
                return Number(BigInt.asIntN(8, elem));
            case "string":
                let utf16 = stringToUTF16(elem);
                utf16 = utf16.map(uint16 => (uint16 > 255 && uint16 <= 65535) ? [uint16 & 0xff, uint16 >> 8] : uint16 & 0xff);
                return Array.from(utf16);
            case "number":
            case "boolean":
            case "function":
            case "object":
                return +elem;
            case "symbol":
                throw TypeError('Symbol Cannot be Converted to a Bumber');
            default:
                throw new TypeError(`Unsupported type: ${typeof elem}`);
        }
    }).flat());
};

export function convertArrayTypes(from, constructor) {
    const {length} = from.length, array = new constructor(length);
    for (let i = 0; i < length; i++) {
        array[i] = from[i];
    }
}

{
    const u8 = Uint8Array.fromModStringAndNumbers(
        [
            'SVGB>',
            255, 255, 0, 0, 255, 255,
        ]);
    console.log(parseSVGB(convertArrayTypes(u8,).buffer));
}

export function stringToUTF16(strx) {
    const str = `${strx}`, uint16 = new Uint16Array(str.length);
    for (let i = 0; i < str.length; i++) uint16[i] = str.charCodeAt(i);
    return uint16;
}
