// ShadowBoxedHover
class ShadowBoxedHover extends HTMLElement {
    connectedCallback() {
        this.classList.add('ShadowBoxedHover');
    }
}

customElements.define('shadowboxed-hover', ShadowBoxedHover, {extends: 'article'});

class ShowOnload extends HTMLTemplateElement {
    #emptied = false;

    connectedCallback() {
        if (this.#emptied) return;
        this.#emptied = true;
        while (this.content.firstElementChild) {
            this.before(this.content.firstElementChild);
        }
    }
}

customElements.define('show-onload', ShowOnload, {extends: 'template'});
