// ShadowBoxedHover
class ShadowBoxedHover extends HTMLElement {
    connectedCallback() {
        this.classList.add('ShadowBoxedHover');
    }
}

customElements.define('shadowboxed-hover', ShadowBoxedHover, {extends: 'article'});
