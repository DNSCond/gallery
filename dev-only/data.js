// Element.prototype.toJSON
Element.prototype.toJSON = function () {
    const r = Array.of(this.tagName, new Object);
    Object.defineProperty(r, Symbol.toStringTag, {
        enumerable: true, value: 'Element-JSON',
        configurable: false, writable: false,
    });
    Object.defineProperty(r[1], Symbol.toStringTag, {
        enumerable: true, value: 'Element-Attributes',
        configurable: false, writable: false,
    });
    for (let n of this.getAttributeNames()) {
        r[1][n] = this.getAttribute(n);
    }

    return Reflect.apply(Array.prototype.concat, r, this.childNodes);
};

Text.prototype.toJSON = Text.prototype.toString = function () {
    return this.data;
};

console.log(JSON.stringify(document.documentElement, null, 2));
