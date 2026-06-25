// ImageContainerElement
class ImageContainerElement extends HTMLElement {
    static #observedAttributes = Object.freeze([
        'img-src', 'img-alt', 'img-width', 'img-height',
        'img-fetchpriority', 'img-loading', 'img-style']);
    #sourceMap = new Map;
    #abortController;

    static get observedAttributes() {
        return ImageContainerElement.#observedAttributes;
    }

    connectedCallback() {
        this.#abortController?.abort();
        this.#abortController = new AbortController;
        const picture = this.pictureTag?.(), image = picture?.querySelector('img');
        if (picture && image) this.querySelectorAll('source[is=\'source-clone\']').forEach(each => {
            const element = each.cloneSourceData?.();
            if (element) {
                image.insertAdjacentElement('beforebegin', element);
                this.#sourceMap.set(each, element);
            }
        });
    }

    disconnectedCallback() {
        this.#abortController?.abort();
    }

    pictureTag() {
        throw ReferenceError('ImageContainerElement must be subclassed.');
    }

    attributeChangedCallback(name, _oldValue, newValue) {
        if (ImageContainerElement.#observedAttributes.includes(name)) {
            const pictureTag = this.pictureTag(), imageTag = pictureTag?.querySelector('img');
            if (newValue === null) {
                imageTag?.removeAttribute(name.slice(4));
            } else {
                imageTag?.setAttribute(name.slice(4), newValue);
            }
        }
    }

    removeSource(source) {
        this.#sourceMap.get(source)?.remove();
        this.#sourceMap.delete(source);
    }
}

class SourceClone extends HTMLSourceElement {
    isEnhanced = true;
    #parentElement = null;

    static get observedAttributes() {
        return ['srcset', 'type', 'media', 'sizes'];
    }

    connectedCallback() {
        this.#parentElement = this.parentElement;
    }

    disconnectedCallback() {
        this.#parentElement?.removeSource?.(this);
        this.#parentElement = null;
    }

    getSourceData() {
        return {srcset: this.srcset, type: this.type, media: this.media, sizes: this.sizes};
    }

    cloneSourceData() {
        let element;
        if (this.isEnhanced) element = new SourceClone;
        else element = document.createElement('source');
        for (const entry of Object.entries(this.getSourceData())) {
            element[entry[0]] = entry[1];
        }
        return element;
    }

    attributeChangedCallback(name, oldValue, newValue) {
        const customEvent = new CustomEvent('attribute-changed-event', {
            detail: Object.freeze({name, oldValue, newValue}),
            cancelable: false, composed: true, bubbles: true,
        });
        this.dispatchEvent(customEvent);
    }
}

customElements.define('source-clone', SourceClone, {extends: 'source'});

const mamTree = document.getElementById('MAMTree'), mamNode = document.getElementById('MAMNode');

class MAMTree extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({mode: "open"}).append(mamTree.content.cloneNode(true));
    }
}

class MAMNode extends ImageContainerElement {
    #htmlPicture;

    constructor() {
        super();
        const img = this.ownerDocument.createElement('img'),
            picture = this.ownerDocument.createElement('picture');
        (this.#htmlPicture = picture).append(img);
        this.attachShadow({mode: "open"}).append(picture, mamNode.content.cloneNode(true));
    }

    pictureTag() {
        return this.#htmlPicture;
    }
}

customElements.define('mam-tree', MAMTree);
customElements.define('mam-node', MAMNode);
