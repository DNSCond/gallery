// noinspection JSFileReferences
import {JSONScript} from './JSONScript.js';

class AlternateSrc extends HTMLPictureElement {
    #alternatesInserted = false;

    connectedCallback() {
        if (this.#alternatesInserted) return;
        this.#alternatesInserted = true;
        const alternates = this.ownerDocument.querySelector(
            'script[type=\'image/vnd.alt-src+json\'][is=\'alt-srcset\']'
        )?.parse?.();
        if (alternates) {
            this.prepend(...alternates)
        }
    }
}

class AlternateSrcset extends JSONScript {
    isJSONMime() {
        return /^application\/vnd\.alternate-src\+json$/i.test(this.type);
    }
}

customElements.define('alternate-src', AlternateSrc, {extends: 'picture'});
customElements.define('alt-srcset', AlternateSrcset, {extends: 'picture'});
