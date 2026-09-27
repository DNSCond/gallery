// Element.prototype
export class LengthedElement{
    #proxyHandler = {

    };

    constructor(element) {
        if (!(element instanceof HTMLElement)) throw new TypeError;
        return new Proxy(this, this.#proxyHandler);
    }
}
