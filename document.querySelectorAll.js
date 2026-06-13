// document.querySelectorAll
document.querySelectorAll('*').forEach(each => each.setAttribute('data-jsname', Object.prototype.toString.call(each).slice(8, -1)));
document.querySelectorAll('*').forEach(each => each.setAttribute('data-conname', each.constructor.name));
document.querySelectorAll('*').forEach(each => each.setAttribute('data-tagname', each.tagName));
// ---

class JSONicMap extends Map {
    toJSON() {
        return Object.fromEntries(this.entries());
    }

    toSorted(type, reversed = false) {
        type = type === 'value' ? 1 : 0;
        return new this.constructor(toSortedReversed(this.entries(), (le, ri) => {
            if (Object.is(le[type], ri[type]) || le[type] === ri[type]) return +0;
            if (le[type] < ri[type]) return -1;
            if (le[type] > ri[type]) return +1;
            else return +0;
        }, !!reversed));
    }

    [Symbol.toStringTag] = "JSONicMap";
}

function toSortedReversed(array, comparator, reversed = true) {
    const inverted = !!reversed;
    return Array.from(array).sort((le, ri) => (num => (1 - 2 * inverted) * +num)(comparator(le, ri)));
}

const pre = document.createElement('pre'), map = new JSONicMap;
const keys = Reflect.ownKeys(window).map(String).sort();
keys.forEach(key => map.set(key.charAt(0), (map.get(key.charAt(0)) ?? 0) + 1));
pre.textContent = JSON.stringify(map.toSorted('value', true), null, 2);
document.body.replaceChildren(pre);
document.head.replaceChildren();
