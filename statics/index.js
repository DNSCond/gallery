// ShadowBoxedHover
class ShadowBoxedHover extends HTMLElement {
    connectedCallback() {
        this.classList.add('ShadowBoxedHover');
    }
}

customElements.define('shadowboxed-hover', ShadowBoxedHover, {extends: 'article'});

class ButtonHover extends HTMLButtonElement {
    connectedCallback() {
        this.classList.add('button-hover');
    }
}

customElements.define('button-hover', ButtonHover, {extends: 'button'});

class ButtonHoverA extends HTMLAnchorElement {
    connectedCallback() {
        this.classList.add('button-hover');
    }
}

customElements.define('button-hovera', ButtonHoverA, {extends: 'a'});
