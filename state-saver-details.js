// state-saver-details
export class StateSaverDetails extends HTMLDetailsElement {
    #opened = false;
    #url = null;

    static get observedAttributes() {
        return ['open', 'id'];
    }

    connectedCallback() {
        if (this.#opened) return;
        this.#opened = true;
        const url = this.setURL();
        if (url) {
            // noinspection JSCheckFunctionSignatures
            const isOpen = localStorage.getItem(url);
            if (isOpen === 'true') {
                // noinspection JSPrimitiveTypeWrapperUsage
                this.setAttribute('open', new String);
            } else if (isOpen === 'false') {
                this.removeAttribute('OPEN');
            }
        }
    }

    setURL() {
        if (this.hasAttribute('id')) {
            const url = URL.parse?.(this.ownerDocument.defaultView.location.href);
            if (url) {
                url.hash = this.getAttribute('id');
                return this.#url = url;
            }
        }
        return this.#url = null;
    }

    attributeChangedCallback(name, oldValue, newValue, _xmlns) {
        if (name === 'open') return this.setOpen(newValue !== null);
        if (name === 'id') return this.setURL();
    }

    setOpen(newValue) {
        if (this.#url) {
            localStorage.setItem(this.#url, Boolean(newValue).toString());
        }
    }
}

customElements.define('state-saver-details', StateSaverDetails, {extends: 'details'});
