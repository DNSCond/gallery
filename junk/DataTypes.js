// ---
export class TypedDataElement extends HTMLDataElement {
    parse() {
        return this.toJSON();
    }
}

export class StringDataElement extends TypedDataElement {
    toJSON() {
        return this.value;
    }
}

export class NumberDataElement extends TypedDataElement {
    toJSON() {
        return +this.value;
    }
}

export class BigIntDataElement extends TypedDataElement {
    toJSON() {
        return BigInt(this.value);
    }
}

export class DateTimeElement extends HTMLTimeElement {
    toJSON() {
        return new Date(this.dateTime);
    }

    parse() {
        return this.toJSON();
    }
}

export class DurationElement extends HTMLTimeElement {
    toJSON() {
        let matched = false, weeks = Number(),
            days = Number(), hours = Number(),
            minutes = Number(), seconds = Number();
        {
            const regexp = /^\s*(?:(\d+)w)?\s*(?:(\d+)d)?\s*(?:(\d+)h)?\s*(?:(\d+)m)?\s*(?:(\d+)s)?\s*$/.exec(this.dateTime);
            if (regexp) {
                [, weeks, days, hours, minutes, seconds] = regexp;
                matched = true;
            }
        }
        {
            const regexp = /^P(?:(\d+)W)?(?:(\d+)D)?(?:T?(?:(\d+)H)?\s*(?:(\d+)M)?\s*(?:(\d+)S)?)?$/.exec(this.dateTime);
            if (regexp) {
                [, weeks, days, hours, minutes, seconds] = regexp;
                matched = true;
            }
        }
        if (matched) {
            [weeks, days, hours, minutes, seconds] = [weeks, days, hours, minutes, seconds].map(m => +m);
            if (typeof globalThis.Temporal?.Duration?.from === 'function') {
                return globalThis.Temporal?.Duration?.from({weeks, days, hours, minutes, seconds});
            } else return {weeks, days, hours, minutes, seconds};
        }
        return null;
    }

    parse() {
        return this.toJSON();
    }
}

export class BinaryDataElement extends TypedDataElement {
    toJSON() {
        return Uint8Array.fromBase64(this.value);
    }
}

export class ArrayDataElement extends HTMLElement {
    toJSON() {
        return Array.from(this.children, x => x.parse?.());
    }
}

const options = {extends: 'data'};
customElements.define('number-data', NumberDataElement, options);
customElements.define('bigint-data', BigIntDataElement, options);
customElements.define('string-data', StringDataElement, options);
customElements.define('data-data', DateTimeElement, {extends: 'time'});
customElements.define('duration-data', DurationElement, {extends: 'time'});
customElements.define('binary-data', BinaryDataElement, options);
customElements.define('array-data', ArrayDataElement);