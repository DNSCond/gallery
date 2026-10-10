// data-way
export function JSONHT(replacerFallback = undefined) {
    return function (_, v) {
        if (Document.prototype.isPrototypeOf?.(v)) {
            v = v.documentElement;
        }
        if (Element.prototype.isPrototypeOf?.(v)) {
            const attributes = Object.fromEntries(Array.from(v.attributes, ({name, value}) => [name, value]));
            const element = {tagName: v.tagName, attributes, children: Array.from(v.childNodes)};
            if (HTMLTemplateElement.prototype.isPrototypeOf?.(v)) {
                element.templateContent = v.content;
            }
            if (['AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'LINK', 'META',
                'PARAM', 'SOURCE', 'TRACK', 'WBR', 'TEMPLATE', 'SCRIPT'].includes(element.tagName)) {
                delete element.children;
            }
            if (element.tagName === 'SCRIPT') {
                const isJSON = /^application\/json$|^.+\/.+\+json$|^importmap$/i.test(v.type);
                element.content = v.textContent;
                if (isJSON) try {
                    element.json = JSON.parse(v.textContent);
                } catch {
                    element.json = null;
                }
            }
            return element;
        } else if (Text.prototype.isPrototypeOf?.(v)) {
            return v.data;
        } else if (typeof replacerFallback === 'function') {
            return Reflect.apply(replacerFallback, this, arguments);
        }
        return v;
    }
}
