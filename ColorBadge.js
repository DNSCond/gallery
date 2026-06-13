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
            '<style>.outer{padding: 0.2em 2.2ch 0.2em 0.2ch}.inner{background-color:'+
            'var(--backColor,#ffffff);color:var(--textColor,#000000);padding:0.1em}' +
            '</style><span class=outer><span class=inner><slot></slot></span></span>';
    }

    attributeChangedCallback(name, oldValue, newValue, _xmlns) {
        if (/^#[a-f0-9]{6}$/i.test(newValue)) {
            this.shadowRoot.querySelector('.outer').style.backgroundColor = newValue;
        } else {
            this.shadowRoot.querySelector('.outer').style.removeProperty('background-color');
        }
    }
}

export class ColorBlock extends HTMLElement {
    static get observedAttributes() {
        return Array.of('color');
    }

    constructor(color = undefined) {
        super();
        if (color) this.setAttribute('color', color);
        // noinspection CssUnresolvedCustomProperty
        this.attachShadow({mode: 'open'}).innerHTML =
            '<style>:host{vertical-align:top}.outer{margin:0.25em;border:2px solid gray;display:inline-block;' +
            'width:40ch;height:5em;}.inner{background-color:var(--backColor,#ffffff);color:var(--textColor,#000000);' +
            'padding:0.1em}</style><div class=outer><span class=inner><slot></slot></span></div>';
    }

    attributeChangedCallback(name, oldValue, newValue, xmlns) {
        Reflect.apply(ColorBadge.prototype.attributeChangedCallback, this, [name, oldValue, newValue, xmlns]);
    }
}

customElements.define('color-badge', ColorBadge);
customElements.define('color-block', ColorBlock);
await Promise.all([
    'color-badge', 'color-block',
].map(elementName => customElements.whenDefined(elementName)));
