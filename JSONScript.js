// JSONScript
export class JSONScript extends HTMLScriptElement {
    toJSON() {
        try {
            if (!this.isJSONMime()) return null;
            else return JSON.parse(this.textContent);
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    isJSONMime() {
        return /^application\/(.+?\+)?json$/i.test(this.type);
    }

    parse() {
        return this.toJSON();
    }

    setJSON(value) {
        if (!this.isJSONMime()) return false;
        return Reflect.set(this, 'textContent', JSON.stringify(value));
    }

    getJSON() {
        return this.toJSON();
    }
}

customElements.define('json-script', JSONScript, {extends: 'script'});

export class OutputScript extends JSONScript {
    connectedCallback() {
        console.log(JSON.stringify(this, null, 2));
    }
}

customElements.define('output-script', OutputScript, {extends: 'script'});

// export class TemplateScript extends JSONScript {
//     #called = false;
//
//     connectedCallback() {
//         if (this.#called) return;
//         this.#called = true;
//         const data = this.mkcontent(super.toJSON());
//         const template = this.ownerDocument.createElement('template');
//         if (data) template.content.append(data);
//         this.insertAdjacentElement('afterend', template);
//     }
//
//     mkcontent(json) {
//         if (typeof json === 'string') return json;
//         if (json === null) throw TypeError('Mustnt be null');
//         if (typeof json !== 'object') throw TypeError('Must be an object');
//         if (typeof json.tagName !== 'string') throw TypeError('json.tagName Must be a string');
//         if (['SCRIPT', 'STYLE', 'LINK', 'TITLE', 'META', 'BODY', 'HEAD', 'HTML', 'TEMPLATE', 'IFRAME'].includes(json.tagName.toUpperCase())) return null;
//         const element = this.ownerDocument.createElement(json.tagName, json.is ? {is: json.is} : undefined);
//         for (const [attribute, value] of Object.entries(json.attributes ?? {})) element.setAttribute(attribute, value);
//         element.append(...Array.from(json.children ?? {}, child => this.mkcontent(child)).filter(m => m));
//         return element;
//     }
// }
//
// customElements.define('template-script', TemplateScript, {extends: 'script'});

// export class HighlighterScript extends JSONScript {
// connectedCallback() {const json = super.toJSON();}}
// customElements.define('highlighter-script', HighlighterScript, {extends: 'script'});
