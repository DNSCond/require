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
        return /^application\/(.+?\+)?json$|^importmap$/i.test(this.type);
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
