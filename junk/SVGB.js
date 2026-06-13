// SVGB

export function decodeSVGB(arrayBuffer, littleEndian = true, maxAssetCount = 100, document = null) {
    document ??= globalThis.document;
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
        view = new DataView(arrayBuffer), le = Boolean(littleEndian);
    if (view.getUint32(0, le) !== 0x53564742) return null;
    const assetCount = Math.min(view.getUint16(4, le), maxAssetCount),
        width = view.getFloat32(8, le), height = view.getFloat32(4 * 3, le);
    let offset = new PointerForwarder(Infinity, 4 * 4), proc = 0;
    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
    svg.setAttribute("height", `${height}`);
    svg.setAttribute("width", `${width}`);
    while (proc++ < assetCount) {
        const type = view.getUint8(offset.postfixnext(1));
        if (type & 0b1) {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            addColors(type, path, view, offset, le);
            const commandcount = view.getUint16(offset.postfixnext(2), le), d = Array(commandcount);
            for (let index = 0; index < commandcount; index++) {
                const cmdsvgByte = view.getUint8(offset.postfixnext(1)),
                    cmdsvg = String.fromCharCode(cmdsvgByte), argcount = table[cmdsvg];
                if (Number.isSafeInteger(argcount)) {
                    const cmdlet = Array(argcount);
                    for (let arg = 0; arg < cmdlet.length; arg++) {
                        cmdlet[arg] = view.getFloat32(offset.postfixnext(4), le);
                    }
                    d[index] = Array.of(cmdsvg).concat(cmdlet).join(' ');
                } else if (argcount === 'a') {
                    const bitwise = view.getUint8(offset.postfixnext(1));
                    // noinspection JSCheckFunctionSignatures
                    d[index] = Array.of(cmdsvg,
                        view.getFloat32(offset.postfixnext(4), le),
                        view.getFloat32(offset.postfixnext(4), le),
                        view.getFloat32(offset.postfixnext(4), le),
                        bitwise & 0b01, bitwise & 0b10,
                        view.getFloat32(offset.postfixnext(4), le),
                        view.getFloat32(offset.postfixnext(4), le),
                    ).join(' ');
                }
            }
            path.setAttribute('d', d.join(' '));
            svg.append(path);
        } else {
            // 1. Read the tag type immediately after the main 'type' byte
            const tagOffset = offset.postfixnext(1), tagNumber = view.getUint8(tagOffset);
            const tagType = ({
                1: {name: 'rect', args: ['x', 'y', 'width', 'height']},
                2: {name: 'circle', args: ['cx', 'cy', 'r']},
                3: {name: 'ellipse', args: ['cx', 'cy', 'rx', 'ry']},
            })[tagNumber];

            if (!tagType) throw TypeError(`Invalid tag at ${tagOffset}`);

            const shape = document.createElementNS("http://www.w3.org/2000/svg", tagType.name);

            // 2. Add colors (this moves the offset forward correctly)
            addColors(type, shape, view, offset, le);

            // 3. Read the specific shape arguments
            for (const arg of tagType.args) {
                // noinspection JSCheckFunctionSignatures
                shape.setAttribute(arg, view.getFloat32(offset.postfixnext(4), le));
            }
            svg.append(shape);
        }
    }
    return svg;
}

class AttributeMap extends Map {
    setAttribute(name, value) {
        this.set(`${name}`, `${value}`);
    }
}

function addColors(type, path, view, offset, le) {
    // noinspection JSBitwiseOperatorUsage
    if (type & 0b10) {
        path.setAttribute("fill", toRGBA(view.getUint32(offset.postfixnext(4), le)));
    } else {
        path.setAttribute("fill", "transparent");
    }
    // noinspection JSBitwiseOperatorUsage
    if (type & 0b100) {
        path.setAttribute("stroke-width", `${view.getUint8(offset.postfixnext(1))}`);
        path.setAttribute("stroke", toRGBA(view.getUint32(offset.postfixnext(4), le)));
    }
}

export const table = {
    m: 2,
    M: 2,
    l: 2,
    L: 2,
    v: 1,
    V: 1,
    h: 1,
    H: 1,
    c: 3 * 2,
    C: 3 * 2,
    q: 4,
    Q: 4,
    s: 2 * 2,
    S: 2 * 2,
    a: 'a',
    A: 'a',
    t: 2,
    T: 2,
    z: 0,
    Z: 0
};

export class PointerForwarder {
    #int = 0;
    #max;

    constructor(max, min = 0) {
        if (max === Infinity) max = Number.MAX_SAFE_INTEGER;
        if (!Number.isSafeInteger(max) || max < 0)
            throw RangeError('max is not a positive safe integer');
        if (!Number.isSafeInteger(min) || min < 0)
            throw RangeError('min is not a positive safe integer');
        this.#int = min;
        this.#max = max;
    }

    next(number) {
        if (!Number.isSafeInteger(number + this.#int) || number < 0)
            throw RangeError('max is not a positive safe integer');
        const value = (this.#int += number), done = value >= this.#max;
        return value; //return { value, done };
    }

    postfixnext(number) {
        if (!Number.isSafeInteger(number + this.#int) || number < 0)
            throw RangeError('max is not a positive safe integer');
        const value = this.#int;
        this.#int += number;
        return value;
        // const done = value >= this.#max;
        // return { value, done };
    }

    valueOf() {
        return this.#int;
    }

    createWithOffset(offset) {
        if (!Number.isSafeInteger(this.#int + offset))
            throw RangeError('offset is not a safe integer');
        return new this.constructor(this.#max, this.#int + offset);
    }
}

export function toRGBA(val) {
    const r = (val >>> 24) & 0xFF;
    const g = (val >>> 16) & 0xFF;
    const b = (val >>> +8) & 0xFF;
    const a = (val & 0xFF) / 255;
    return `rgb(${r} ${g} ${b} / ${a})`;
}

export function toRGBAInt(color, alpha = 255) {
    if ((alpha = validatePositiveSafeInteger(alpha, 'alpha', true)) > 255)
        throw RangeError('alpha must not exceed 255 (1 byte)');
    const object = /^#?([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i.exec(color);
    if (object) {
        const [, r, g, b] = object;
        return ((parseInt(`0x${r}`) << 24) | (parseInt(`0x${g}`) << 16) | (parseInt(`0x${b}`) << +8) | alpha) >>> 0;
    } else throw TypeError('invalid color hex format');
}

export class NamedObject {
    constructor(name) {
        new.target.attachNameTo(name, this);
    }

    static [Symbol.toStringTag] = 'NamedObject';

    static getNameOf(of) {
        return Object.prototype.toString.call(of).slice(8, -1);
    }

    static attachNameTo(name, to) {
        const value = `${name}`;
        return Object.defineProperty(to, Symbol.toStringTag, {value});
    }
}

const PathObjectName = new NamedObject('PathObject'),
    ShapeObjectName = new NamedObject('ShapeObject'),
    TagTypeName = new NamedObject('TagTypeName');

export class SVGBEncode extends NamedObject {
    #toEncode = [];
    #w;
    #h;

    constructor(w, h) {
        super('SVGBEncode');
        const x = +w, y = +h;
        if (!Number.isSafeInteger(x) || x < 0)
            throw RangeError('x is not a positive safe integer');
        if (!Number.isSafeInteger(y) || y < 0)
            throw RangeError('y is not a positive safe integer');
        this.#w = x;
        this.#h = y;
    }

    addPath(fill, svgPath, stroke = undefined, stroke_width = undefined) {
        const path = expandCommands(parsePath(svgPath), table);
        fill = toRGBAInt(fill);
        const pathObject = {
            __proto__: PathObjectName,
            fill, path,
        };
        if (stroke !== undefined && stroke_width !== undefined) {
            pathObject.stroke = toRGBAInt(stroke);
            if ((pathObject.stroke_width = validatePositiveSafeInteger(stroke_width, 'stroke_width', true)) > 255)
                throw RangeError('stroke_width must not exceed 255 (1 byte)');
        }
        this.#toEncode.push(pathObject);
    }

    #MakeBasicShapeObject(shapeType, fill, stroke, stroke_width) {
        if (fill !== undefined) fill = toRGBAInt(fill);
        const shapeObject = {
            __proto__: ShapeObjectName,
            fill, shapeType,
        }
        if (stroke !== undefined && stroke_width !== undefined) {
            shapeObject.stroke = toRGBAInt(stroke);
            if ((shapeObject.stroke_width = validatePositiveSafeInteger(stroke_width, 'stroke_width', true)) > 255)
                throw RangeError('stroke_width must not exceed 255 (1 byte)');
        }
        return shapeObject;
    }

    addRect(fill, x, y, w, h, stroke = undefined, stroke_width = undefined) {
        const shapeObject = this.#MakeBasicShapeObject('Rect', fill, stroke, stroke_width);
        Object.assign(shapeObject, {x, y, w, h});
        this.#toEncode.push(shapeObject);
    }

    addEllipse(fill, cx, cy, rx, ry, stroke = undefined, stroke_width = undefined) {
        const shapeObject = this.#MakeBasicShapeObject('Ellipse', fill, stroke, stroke_width);
        const [x, y, w, h] = [cx, cy, rx, ry];
        Object.assign(shapeObject, {x, y, w, h});
        this.#toEncode.push(shapeObject);
    }

    addCircle(fill, x, y, r, stroke = undefined, stroke_width = undefined) {
        const shapeObject = this.#MakeBasicShapeObject('Circle', fill, stroke, stroke_width);
        Object.assign(shapeObject, {x, y, r});
        this.#toEncode.push(shapeObject);
    }

    constructBinary(littleEndian = true) {
        const le = Boolean(littleEndian);
        const chunks = [];
        let totalLength = 0;

        // 1. Helper to push typed arrays to our collection
        const pushChunk = (arr) => {
            chunks.push(arr);
            return totalLength += arr.length;
        };

        // 2. Encode Header (Magic: SVGB, AssetCount, Width, Height)
        const headerBuffer = new ArrayBuffer(16);
        const headerView = new DataView(headerBuffer);
        headerView.setUint32(0, 0x53564742, le); // "SVGB"
        headerView.setUint16(4, this.#toEncode.length, le);
        headerView.setFloat32(8, this.#w, le);
        headerView.setFloat32(12, this.#h, le);
        pushChunk(new Uint8Array(headerBuffer));

        // 3. Encode Each Path
        for (const element of this.#toEncode) {
            if (element.path) {
                // We use a small temporary buffer for the path metadata
                // Max metadata size: type(1) + fill(4) + stroke_w(1) + stroke(4) + cmd_count(2) = 12 bytes

                let mOffset = 0;

                let type = 0b1, metaSize = 1 + 2; // It's a path
                if (element.fill !== undefined) {
                    metaSize += 4;
                    type |= 0b10;
                }
                if (element.stroke !== undefined) {
                    metaSize += 5;
                    type |= 0b100;
                }
                const metaBuffer = new ArrayBuffer(metaSize);
                const metaView = new DataView(metaBuffer);

                metaView.setUint8(mOffset++, type);

                if (element.fill !== undefined) {
                    metaView.setUint32(mOffset, element.fill, le);
                    mOffset += 4;
                }
                if (element.stroke !== undefined) {
                    metaView.setUint8(mOffset++, element.stroke_width);
                    metaView.setUint32(mOffset, element.stroke, le);
                    mOffset += 4;
                }

                metaView.setUint16(mOffset, element.path.length, le);
                mOffset += 2;

                // Push only the portion of metadata we actually used
                pushChunk(new Uint8Array(metaBuffer, 0, mOffset));

                // 4. Encode Path Commands
                /*for (const cmd of element.path) {
                    const cmdCount = cmd.values.length;
                    // 1 byte for Char + 4 bytes per Float
                    const pBuffer = new ArrayBuffer(1 + (cmdCount * 4));
                    const pView = new DataView(pBuffer);

                    pView.setUint8(0, cmd.type.charCodeAt(0));
                    for (let i = 0; i < cmdCount; i++) {
                        pView.setFloat32(1 + (i * 4), cmd.values[i], le);
                    }
                    pushChunk(new Uint8Array(pBuffer));
                }*/
                // 4. Encode Path Commands
                for (const cmd of element.path) {
                    const isArc = cmd.type.toLowerCase() === 'a';
                    const cmdBuffer = new ArrayBuffer(isArc ? 22 : 1 + (cmd.values.length * 4));
                    const pView = new DataView(cmdBuffer);

                    pView.setUint8(0, cmd.type.charCodeAt(0));

                    if (isArc) {
                        // rx, ry, x-axis-rotation (Floats)
                        pView.setFloat32(1, cmd.values[0], le);
                        pView.setFloat32(5, cmd.values[1], le);
                        pView.setFloat32(9, cmd.values[2], le);
                        // large-arc (bit 0), sweep (bit 1)
                        const flags = (cmd.values[3] ? 0b01 : 0) | (cmd.values[4] ? 0b10 : 0);
                        pView.setUint8(13, flags);
                        // x, y (Floats)
                        pView.setFloat32(14, cmd.values[5], le);
                        pView.setFloat32(18, cmd.values[6], le);
                    } else {
                        for (let i = 0; i < cmd.values.length; i++) {
                            pView.setFloat32(1 + (i * 4), cmd.values[i], le);
                        }
                    }
                    pushChunk(new Uint8Array(cmdBuffer));
                }
            } else if (element.shapeType) {
                let dv = null, tagType = null;
                // noinspection FallThroughInSwitchStatementJS
                switch (element.shapeType) {
                    case 'Ellipse':
                        tagType = 3;
                    case 'Rect': {
                        tagType ??= 1;
                        dv = new DataView(new ArrayBuffer(4 * 4));
                        dv.setFloat32(0, element.x, le);
                        dv.setFloat32(4, element.y, le);
                        dv.setFloat32(2 * 4, element.w, le);
                        dv.setFloat32(3 * 4, element.h, le);
                        break;
                    }
                    case 'Circle': {
                        tagType = 2;
                        dv = new DataView(new ArrayBuffer(3 * 4));
                        dv.setFloat32(0, element.x, le);
                        dv.setFloat32(4, element.y, le);
                        dv.setFloat32(2 * 4, element.r, le);
                        break;
                    }
                }
                if (dv && tagType) {
                    let metaSize = 2, type = 0b0;
                    if (element.fill !== undefined) {
                        metaSize += 4;
                        type |= 0b10;
                    }
                    if (element.stroke !== undefined) {
                        metaSize += 5;
                        type |= 0b100;
                    }

                    let mOffset = 0; // It's not a path
                    const metaView = new DataView(new ArrayBuffer(metaSize));
                    metaView.setUint8(mOffset++, type);
                    metaView.setUint8(mOffset++, tagType);
                    if (element.fill !== undefined) {
                        metaView.setUint32(mOffset, element.fill, le);
                        mOffset += 4;
                    }
                    if (element.stroke !== undefined) {
                        metaView.setUint8(mOffset++, element.stroke_width);
                        metaView.setUint32(mOffset, element.stroke, le);
                        mOffset += 4;
                    }
                    pushChunk(new Uint8Array(metaView.buffer));
                    pushChunk(new Uint8Array(dv.buffer));
                }
            }
        }

        // 5. Final Step: Single allocation and copy
        const finalResult = new Uint8Array(totalLength);
        let currentPos = 0;
        for (const chunk of chunks) {
            finalResult.set(chunk, currentPos);
            currentPos += chunk.length;
        }

        return finalResult.buffer;
    }
}

export function validatePositiveSafeInteger(n, name, coerce = false) {
    if (coerce) n = +n;
    if (!Number.isSafeInteger(n) || n < 0)
        throw RangeError(String(name) + ' is not a positive safe integer');
    return n;
}

const paramCount = table;

function parsePath(d) {
    const commands = d.match(/[a-zA-Z][^a-zA-Z]*/g);
    const result = [];

    for (let chunk of commands) {
        const type = chunk[0];
        const upperType = type.toUpperCase();

        const numbers = chunk
            .slice(1)
            .match(/-?\d*\.?\d+(?:e[-+]?\d+)?/gi)
            ?.map(Number) || [];

        const needed = paramCount[upperType];

        if (needed === 0) {
            result.push({type, values: []});
            continue;
        }

        // Handle repeated commands (e.g. "L 10 10 20 20")
        for (let i = 0; i < numbers.length; i += needed) {
            result.push({
                type,
                values: numbers.slice(i, i + needed)
            });
        }
    }

    return result;
}

function expandCommands(commands, table) {
    const result = [];

    for (const cmd of commands) {
        const {type, values} = cmd;
        const upper = type.toUpperCase();
        const expected = table[type];

        // Z has no params
        if (expected === 0) {
            result.push(cmd);
            continue;
        }

        // Special handling for M/m
        if (upper === 'M') {
            for (let i = 0; i < values.length; i += 2) {
                const chunk = values.slice(i, i + 2);
                if (chunk.length < 2) break;

                if (i === 0) {
                    result.push({type, values: chunk});
                } else {
                    // convert to L or l
                    const newType = type === 'M' ? 'L' : 'l';
                    result.push({type: newType, values: chunk});
                }
            }
            continue;
        }

        // Normal commands
        for (let i = 0; i < values.length; i += expected) {
            const chunk = values.slice(i, i + expected);
            if (chunk.length < expected) break;

            result.push({type, values: chunk});
        }
    }

    return result;
}
