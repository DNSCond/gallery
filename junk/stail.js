"use strict"; // https://gemini.google.com/app/9ae536046e5f3b1f
// export function modifiedStalinSortArray(array, compareFn = undefined) {
//     const length = Math.min(Math.max((function (n) {
//         n = +n;
//         if (Object.is(n, NaN) || n === 0) {
//             return 0;
//         } else return Math.trunc(n);
//     })(array.length), 0), 2 ** 53 - 1);
//     compareFn ??= function (left, right) {
//         const x = `${left}`;
//         const y = `${right}`;
//         if (x < y) return -1;
//         if (y < x) return +1;
//         return +0;
//     };
//     const list = Array.prototype.map.call(array, mapped => mapped), comparator = function (le, ri) {
//         if (le === undefined && ri === undefined) return +0;
//         else if (le === undefined) return +1;
//         else if (ri === undefined) return -1;
//         else {
//             const compareResult = +Reflect.apply(compareFn, undefined, [le, ri]);
//             if (Number.isNaN(compareResult)) return 0; else return compareResult;
//         }
//     };
//     const secondArray = Array();
//     while (true) {
//         // sort the array
//     }
// }
