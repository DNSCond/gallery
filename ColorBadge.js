// ColorBadge
export class ColorBadge extends HTMLElement {
    static get observedAttributes() {
        return Array.of('color');
    }

    constructor(color = undefined) {
        super();
        if (color) this.setAttribute('color', color);
        // noinspection CssUnresolvedCustomProperty
        this.attachShadow({mode: 'open'}).innerHTML =
            '<span style="padding: 0.2em 2.2ch 0.2em 0.2em"><span style=background-color:var(--back' +
            'Color,#ffffff);color:var(--textColor,#000000);padding:0.1em><slot></slot></span></span>';
    }

    attributeChangedCallback(_, __, newValue) {
        if (/^#[a-f0-9]{6}$/i.test(newValue)) {
            this.shadowRoot.querySelector('span').style.backgroundColor = newValue;
        } else {
            this.shadowRoot.querySelector('span').style.removeProperty('background-color');
        }
    }
}

customElements.define('color-badge', ColorBadge);
await customElements.whenDefined('color-badge');
