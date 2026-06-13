// JSONScript
export class JSONScript extends HTMLScriptElement {
    toJSON() {
        try {
            if (!/^application\/(.+?\+)?json$/i.test(this.type)) return null;
            else return JSON.parse(this.textContent);
        } catch (error) {
            console.error(error);
            return null;
        }
    }
}

customElements.define('json-script', JSONScript, {extends: 'script'});
