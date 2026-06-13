// custombuiltins.js
export class CustomTimeBuiltin extends HTMLTimeElement {
    asDate() {
        return new Date(this.getAttribute("datetime") ?? NaN);
    }

    static get observedAttributes() {
        return ['data-dtformat', 'datetime'];
    }

    attributeChangedCallback(_name, _oldValue, _newValue) {
        const dt = this.asDate();
        switch (this.getAttribute("data-dtformat")?.toLowerCase()) {
            case "tolocalestring":
                this.textContent = dt.toLocaleString();
                break;
            case "todatestring":
                this.textContent = dt.toDateString();
                break;
            case "tolocaledatestring":
                this.textContent = dt.toLocaleDateString();
                break;
            case "totimestring":
                this.textContent = dt.toTimeString();
                break;
            case "tolocaletimestring":
                this.textContent = dt.toLocaleTimeString();
                break;
            case "toutcstring":
            case "togmtstring":
                this.textContent = dt.toUTCString();
                break;
            case "toisostring":
            case "torfc3339string":
                this.textContent = dt.toJSON() ?? 'Invalid Date';
                break;
            case "todatetimestring":
                this.textContent = dt.toString().replace(/\x20GMT.+/, '');
                break;
            default: // case "tostring":
                this.textContent = dt.toString();
                break;
        }
    }

    static updateAll(newFormat, document_ = document) {
        document_.querySelectorAll('time[is=time-custombuiltin]').forEach(each => {
            each.setAttribute('data-dtformat', newFormat);
        });
    }
}

export class CustomFormBuiltin extends HTMLFormElement {
    connectedCallback() {
        this.removeAttribute('hidden');
    }
}

customElements.define('time-custombuiltin', CustomTimeBuiltin, {extends: 'time'});
customElements.define('form-custombuiltin', CustomFormBuiltin, {extends: 'form'});
