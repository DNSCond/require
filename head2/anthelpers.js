// https://github.com/DNSCond/antHelpers
export class CustomError extends Error {
    detail;
    constructor(message, detail) {
        super(message);
        this.detail = detail;
        this.name = new.target?.name ?? 'CustomError';
    }
    get [Symbol.toStringTag]() {
        return this.name;
    }
    static [Symbol.toStringTag] = "CustomError";
}

export const EXMAScript = Object.freeze({
    __proto__: {
        [Symbol.toStringTag]: 'EXMAScriptInternals',
    }, toIntegerOrInfinity(n) {
        n = +n;
        if (Object.is(n, NaN) || n === 0) {
            return 0;
        }
        else
            return Math.trunc(n);
    }, OrdinaryToPrimitive(mixed, hint = "number") {
        if (!["string", "number"].includes(hint))
            throw new TypeError('incorrect hint');
        if (!isObject(mixed))
            return mixed;
        const methodNames = hint === "string" ? ["toString", "valueOf"] : ["valueOf", "toString"];
        for (let methodName of methodNames) {
            if (methodName in mixed) {
                if (typeof mixed[methodName] === "function") {
                    const primitive = mixed[methodName]();
                    if (!isObject(primitive))
                        return primitive;
                }
            }
        }
        throw new TypeError('could not convert to Primitive');
    }, toPrimitive(value, hint) {
        if (!["string", "number", "default"].includes(hint))
            throw new TypeError('incorrect hint');
        let primitive;
        if (value === null)
            return "null";
        if (typeof value === "object" || typeof value === "function") {
            if (Symbol.toPrimitive in value && typeof value[Symbol.toPrimitive] === "function") {
                primitive = value[Symbol.toPrimitive](hint);
                if (isObject(primitive))
                    throw new TypeError('could not convert to primitive');
            }
            else {
                primitive = EXMAScript.OrdinaryToPrimitive(value, "number");
            }
        }
        else
            primitive = value;
        return primitive;
    }, toPropertyKey(value) {
        const primitive = EXMAScript.toPrimitive(value, "string");
        if (typeof primitive === "symbol")
            return primitive;
        return String(primitive);
    }, toNumeric(value) {
        // Handle object conversion
        value = EXMAScript.toPrimitive(value, "number");
        if (typeof value === 'bigint')
            return value;
        else
            return +value;
    }, isNaN(nan) {
        return isNaN(nan);
    }, webBuiltins: Object.freeze({
        [Symbol.toStringTag]: 'WebInternals',
        TokenList: class TokenList {
            #tokens;
            constructor(array) {
                this.#tokens = new Set(Array.from(array, s => `${s}`));
            }
            [Symbol.toStringTag] = 'TokenList';
            toString() {
                return Array.prototype.join.call(Array.from(this.#tokens.keys()), ' ');
            }
            add(...tokens) {
                Array.from(tokens).forEach(s => this.#tokens.add(this._validateToken(s)));
            }
            contains(token) {
                return this.#tokens.has(token);
            }
            remove(...tokens) {
                Array.from(tokens).forEach(s => this.#tokens.delete(this._validateToken(s)));
            }
            toggle(token, force = undefined) {
                token = this._validateToken(token);
                if (this.contains(token)) {
                    if (force === false || force === undefined) {
                        this.remove(token);
                        return false;
                    }
                    if (force) {
                        this.add(token);
                        return true;
                    }
                }
            }
            _validateToken(token) {
                token = `${token}`;
                if (token === '') {
                    throw new EXMAScript.webBuiltins.SyntaxError('token is empty');
                }
                else if (/\s+/.test(token)) {
                    throw new EXMAScript.webBuiltins.InvalidCharacterError('token is contains whitespace');
                }
                return token;
            }
            replace(oldToken, newToken) {
                oldToken = this._validateToken(oldToken);
                newToken = this._validateToken(newToken);
                if (this.contains(oldToken)) {
                    this.remove(oldToken);
                    this.remove(newToken);
                    return true;
                }
                return false;
            }
            get value() {
                return this.toString();
            }
            get length() {
                return this.#tokens.size;
            }
        },
        SyntaxError: class extends CustomError {
            constructor(m) {
                super(m, '"SyntaxError" DOMException');
            }
        },
        InvalidCharacterError: class extends CustomError {
            constructor(m) {
                super(m, '"InvalidCharacterError" DOMException');
            }
        },
    }),
});
export function isObject(value) {
    if (value === null)
        return false;
    return (typeof value === "object" || typeof value === "function");
}
