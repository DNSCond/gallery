// ExoticClass
function ExoticClass(inheritor) {
    if (new.target) throw new TypeError;

    class Constructor extends inheritor {
        constructor(handler, ...rest) {
            super(...rest);
            return new Proxy(this, handler ?? {});
        }
    }

    return Constructor;
}

class Test extends ExoticClass(Array) {
    constructor() {
        super({
            ownKeys(t) {
                console.log('works');
                return Reflect.ownKeys(t);
            },
        });
        this.key = Date();
    }
}

console.log(Object.keys(new Test));
