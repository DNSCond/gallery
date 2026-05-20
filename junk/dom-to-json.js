// Element.prototype.toJSON;
Element.prototype.toJSON = function () {
    // noinspection EqualityComparisonWithCoercionJS
    return {
        tagName: this.tagName ?? null,
        jsName: Object.prototype.toString.call(this).slice(8, -1),
        attributes: Object.fromEntries(Array.from(this.attributes ?? Array(), attr => [attr.name, attr.value])),
        children: Array.from(this.childNodes ?? Array()),
        shadowRoot: this.shadowRoot == undefined ? undefined : Array.from(this.shadowRoot?.childNodes ?? Array()),
    };
};

Document.prototype.toJSON = DocumentFragment.prototype.toJSON = Element.prototype.toJSON;
HTMLTemplateElement.prototype.toJSON = function () {
    return {
        tagName: this.tagName ?? null,
        jsName: Object.prototype.toString.call(this).slice(8, -1),
        attributes: Object.fromEntries(Array.from(this.attributes ?? Array(), attr => [attr.name, attr.value])),
        content: this.content,
    };
};

// Text.prototype.toJSON = function () {return this.data;};
Comment.prototype.toJSON = Text.prototype.toJSON = function () {
    return {jsName: Object.prototype.toString.call(this).slice(8, -1), text: this.data};
};

DocumentType.prototype.toJSON = function () {
    return {jsName: Object.prototype.toString.call(this).slice(8, -1)};
};

function toJSONDocument() {
    const pre = document.createElement('pre');
    pre.textContent = JSON.stringify(document, null, 2);
    document.body.replaceChildren(pre);
    document.head.replaceChildren();
}

toJSONDocument();
Object.defineProperty(Document.prototype, 'description', {
    get() {
        return this?.querySelector("meta[name=description]")?.content;
    },
    set(value) {
        const doc = this?.querySelector("meta[name=description]");
        if (doc) doc.content = value;
    },
});
