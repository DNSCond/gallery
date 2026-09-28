// // Element.prototype
// export class LengthedElement {
//     #proxyHandler = {
//         defineProperty(t, p, d) {
//             const own = Reflect.getOwnPropertyDescriptor(t, p);
//             const extensible=Reflect.isExtensible(this);
//             return true;
//         },
//     };
//
//     constructor(element) {
//         if (!(element instanceof HTMLElement)) throw new TypeError;
//         return new Proxy(this, this.#proxyHandler);
//     }
// }
