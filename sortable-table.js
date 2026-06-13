// data-prevent-sorting='' prevent sorting.
// aria-sort='' sort direction (either 'ascending' or 'descending')
// data-string-insensitive='' either 'upper' or 'uppercase' for uppercase or anything else for lowercase.
// dont specify for exact case.
// data-sort-type='' either 'datetime' for Dates (use the time element in td) 'string' or 'number'
// data-sort-value='' the value to be sorted.
export class SortableTable extends HTMLTableElement {
    #abortController;
    #busy = false;

    connectedCallback() {
        this.#abortController?.abort();
        const {signal} = this.#abortController = new AbortController;
        this.querySelectorAll('thead>tr>th').forEach(each => {
            if (each.hasAttribute('data-prevent-sorting')) return;
            each.addEventListener('click', () => {
                this._sortTable(each);
            }, {signal});
        });
    }

    get [Symbol.toStringTag]() {
        return this.constructor.name;
    }

    disconnectedCallback() {
        this.#abortController?.abort();
        this.#abortController = undefined;
    }

    _sortTable(th) {
        if (this.#busy) return;
        this.#busy = true;
        if (th.hasAttribute('aria-sort')) {
            if (th.getAttribute('aria-sort') === 'ascending')
                th.setAttribute('aria-sort', 'descending');
            else th.setAttribute('aria-sort', 'ascending');
        } else th.setAttribute('aria-sort', 'ascending');
        this.querySelectorAll('thead>tr>th').forEach(each =>
            each !== th ? each.removeAttribute('aria-sort') : null);
        const inverted = Boolean(th.getAttribute('aria-sort') === 'descending'),
            index = th.cellIndex, stringinsensitive =
                th.getAttribute('data-string-insensitive')?.toUpperCase(),
            rows = Array.from(this.querySelectorAll('tbody>tr'),
                row => ({
                    row, cell: row.cells[index], sortValue: undefined,
                    toend: row.cells[index]?.hasAttribute('data-toend'),
                }));
        if (index < 0) return;
        let sortType = th.dataset.sortType, string = Number(),
            numeric = Number(), datetime = Number();
        for (const row of rows) {
            if (row.toend) continue;
            const sortValue = this.getSortValue(row.cell);
            if (sortValue instanceof Date) {
                row.sortValue = sortValue;
                datetime += 1;
                continue;
            }
            const value = +(sortValue);
            if (!Number.isNaN(value) && sortValue.length) {
                row.sortValue = +value;
                numeric += 1;
                continue;
            }
            string += 1;
            if (stringinsensitive === "UPPER" || stringinsensitive === "UPPERCASE") {
                row.sortValue = String(sortValue).toUpperCase();
            } else if (typeof stringinsensitive === "string") {
                row.sortValue = String(sortValue).toLowerCase();
            } else {
                row.sortValue = String(sortValue);
            }
        }
        if (sortType === undefined) {
            if (string > numeric && string > datetime) sortType = 'string';
            else if (numeric > string && numeric > datetime) sortType = 'number';
            else if (datetime > string && datetime > numeric) sortType = 'datetime';
            else sortType = 'string';
        }
        const sortedRows = rows.sort((le, ri) => (num => (1 - 2 * inverted) * +num)(((a, b) => {
            if (a === null && b === null) return +0;
            if (a === null) return +1;
            if (b === null) return -1;
            // ---
            if (a.toend && b.toend) return +0;
            if (a.toend) return +1;
            if (b.toend) return -1;
            // ---
            let tempA, tempB;
            switch (sortType) {
                case "datetime":
                    tempA = new Date(a.sortValue);
                    tempB = new Date(b.sortValue);
                // noinspection FallThroughInSwitchStatementJS (this is intentional)
                case "number":
                    tempA = +(tempA ?? a.sortValue);
                    tempB = +(tempB ?? b.sortValue);
                    if (isNaN(tempA) && isNaN(tempB)) return +0;
                    if (isNaN(tempA)) return +1;
                    if (isNaN(tempB)) return -1;
                    return tempA - tempB;
                default:
                    tempA = a.sortValue instanceof Date ? a.sortValue.toISOString() : String(a.sortValue);
                    tempB = b.sortValue instanceof Date ? b.sortValue.toISOString() : String(b.sortValue);
                    if (tempA < tempB) return -1;
                    if (tempB < tempA) return +1;
                    return +0;
            }
        })(le, ri))).map(({row}) => row);
        // noinspection JSCheckFunctionSignatures
        this.querySelector('tbody').replaceChildren(...sortedRows);
        this.#busy = false;
    }

    getSortValue(td) {
        if (!td) return '';
        const time = td.querySelector('&>time');
        if (time) return new Date(time.dateTime);
        const data = td.querySelector('&>data');
        if (data) return data.value;
        return td.getAttribute(
            'data-sort-value'
        ) ?? td.innerText.trim();
    }
}

customElements.define('sortable-table', SortableTable, {extends: 'table'});

export function inplaceMap(array, func, self) {
    array = Object.prototype.valueOf.call(array);
    const toIntegerOrInfinity = function (n) {
        n = +n;
        if (Object.is(n, NaN) || n === 0) {
            return 0;
        } else return Math.trunc(n);
    };
    let until = toIntegerOrInfinity(array.length);
    if (until < 0) until = 0; else if (until > 2 ** 53 - 1) until = 2 ** 53 - 1;
    for (let i = 0; i < until; i++) {
        if (Reflect.has(array, i))
            array[i] = Reflect.apply(func, self,
                Array.of(array[i], i, array));
    }
    return array;
}
