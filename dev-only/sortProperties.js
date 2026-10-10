// sortProperties
export function sortProperties(array) {
    if (typeof array === 'undefined') return [];
    const regexp = /^\d+$/;
    return Array.from(array,
        index => typeof index === "string" || index === undefined
        || typeof index === "symbol" ? index : null).sort((le, ri) => {
        if (le === null && ri === null) return 0;
        if (le === null) return +1;
        if (ri === null) return -1;
        if (typeof le === typeof ri) {
            if (typeof le === "symbol") return 0;
            let leIsIndex = regexp.test(le) && le >= 0 && le <= 2 ** 32 - 2 && !le.startsWith(0),
                riIsIndex = regexp.test(ri) && ri >= 0 && ri <= 2 ** 32 - 2 && !ri.startsWith(0);
            if (leIsIndex && riIsIndex) return le - ri;
            else if (leIsIndex) return -1;
            else if (riIsIndex) return +1;

            if (le > ri) return +1;
            if (ri > le) return -1;
            return 0;
        }
        if (typeof ri === "string") return +1;
        return -1;
    });
}

const array = (sortProperties(['1', null, '2', '3', 'Date', undefined, , '4', 8, '5', '6', '7', '8', '0', [], 'toString', Symbol.asyncDispose, function () {
}, Symbol()].concat(Object.getOwnPropertyNames(navigator.__proto__), [Symbol.iterator, 'Temporal', 600n, , , Object(), '__proto__', [], '89', '45', '2',
        '67', Symbol.toStringTag], ['04', '05', '06', Symbol(Date())],
    [, , 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 5566, 43, 2, 2, 3, 45, , 6, 7, , 7, 88, 78,
        330, 331, 332, 333, 334, 335, 336, 337, 338, 339, 340, 341, 342, 343, 344, 345, 346, 347, 348, 349, 350, 351,
        352, 353, 354, 355, 356, 357, 358, 359, 360, 361, 362, 363, 364, 365, 366, 367, 368, 369, 370, 371, 372, 373,
        374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 384, 385, 386, 387, 388, 389, 390, 391, 392, 393, 394, 395,
        396, 397, 398, 399, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
        418, 419, 420].map(e => '0' + e)
)));
for (const arrayElement of array) console.log(arrayElement);
