import { setDatetime, RelativeTime } from "./RelativeTimeChecker.js";
import { Datetime_global, validateTimezone } from "./Datetime_global.js";
export class TimeElementBuiltin extends HTMLTimeElement {
    connectedCallback() {
    }
    /**
     * sets the `datetime` and possibly `timezone` attribute to the new timestamp of the param.
     * @param newValue a Date, Temporal.ZonedDateTime, Datetime_global, string, or number.
     */
    set date(newValue) {
        setDatetime(newValue, this);
    }
    getTimeHTMLElement() {
        return this;
    }
    /**
     * a Date representing the `datetime` attribute or null.
     */
    get date() {
        const date = this.getAttribute('datetime');
        if (date === null)
            return null;
        return new Date(date);
    }
    /**
     * gets the `timezone` attribute of this element.
     */
    get timezone() {
        const timezone = this.getAttribute('data-timezone');
        if (timezone === 'local')
            return Datetime_global.hostLocalTimezone();
        return timezone;
    }
    /**
     * sets the `timezone` attribute to the new timezone of the param.
     */
    set timezone(newValue) {
        if (newValue === null) {
            this.removeAttribute('data-timezone');
        }
        else if (newValue === undefined) {
            throw new TypeError('undefined is not a timezone');
        }
        else if (newValue === 'local') {
            this.setAttribute('data-timezone', Datetime_global.hostLocalTimezone());
        }
        else {
            // if the timezone is invalid an error is thrown, do not catch it, It's for the one doing the assignment.
            validateTimezone(newValue, true);
            this.setAttribute('data-timezone', newValue);
        }
    }
    /**
     * gets a `Datetime_global` representing the `datetime` attribute or null. throws when the `timezone` is invalid.
     */
    get datetime_global() {
        const datetime = this.getAttribute('datetime');
        const timezone = this.getAttribute('data-timezone');
        if (datetime === null)
            return null;
        if (timezone === 'local') {
            return new Datetime_global(datetime, Datetime_global.hostLocalTimezone());
        }
        else {
            return new Datetime_global(datetime, timezone ?? 'UTC');
        }
    }
    /**
     * gets a `Temporal.ZonedDateTime` representing the `datetime` attribute or null. throws when the `timezone` is invalid.
     */
    get zonedDateTime() {
        return this.datetime_global?.toTemporalZonedDateTime() ?? null;
    }
}
/**
 * for inheritance only
 */
export class TimeElementFormatter extends TimeElementBuiltin {
    /**
     * for internal use only. call when updating textContent
     * @param nativeDate
     * @param timezone
     * @param defaultFormatted
     */
    requestCustomFormat(nativeDate, timezone, defaultFormatted) {
        const detail = {
            date: nativeDate ?? new Date(NaN), timezone,
            formattedValue: defaultFormatted, // Let them override this property
        }, event = new CustomEvent('format-datetime', {
            bubbles: true, cancelable: true, composed: true, detail,
        }), isPrevented = !this.dispatchEvent(event);
        // If prevented, we return whatever the listener set on detail.formattedValue
        return isPrevented ? detail.formattedValue : defaultFormatted;
    }
}
/**
 * A custom HTML element that displays a formatted absolute time.
 *
 * @element clock-time
 * @attr {string} datetime - The date/time string to display.
 * @attr {string} format - The output format string (default: 'Y-m-d H:i:s'). Passed to Datetime_global's format method.
 * @attr {string} timezone - The timezone to use for formatting (default: 'UTC').
 *
 * Example usage:
 *   <clock-time datetime="2025-06-12T12:00:00Z" format="Y-m-d H:i" timezone="UTC"></clock-time>
 */
export class ClockTimeBuiltin extends TimeElementFormatter {
    /**
     * Returns the list of attributes to observe for changes.
     * @returns {string[]}
     */
    static get observedAttributes() {
        return ['datetime', 'data-format', 'data-timezone'];
    }
    /**
     * Called when the element is inserted into the DOM.
     * Triggers an initial update of the displayed time.
     * @returns {void}
     */
    connectedCallback() {
        super.connectedCallback();
        this.updateTime();
    }
    /**
     * Called when an observed attribute changes.
     * @param {string} _name - The name of the attribute.
     * @param {string|null} _oldValue - The old value of the attribute.
     * @param {string|null} _newValue - The new value of the attribute.
     * @returns {void}
     */
    attributeChangedCallback(_name, _oldValue, _newValue) {
        this.updateTime();
    }
    /**
     * Updates the displayed time based on current attributes.
     * Handles invalid dates gracefully by displaying an error or the raw date string.
     * @returns {void}
     */
    updateTime() {
        const format = this.getAttribute('data-format') ?? Datetime_global.FORMAT_DATETIME_GLOBALV3;
        this.getTimeHTMLElement().textContent = this.requestCustomFormat(this.date, this.timezone ?? 'UTC', this.datetime_global?.format(format) ?? 'Invalid Date');
    }
}
/**
 * A custom HTML element that displays a human-readable relative time.
 *
 * @element relative-time
 * @attr {string} datetime - The date/time string to compare to the current time.
 *
 * Example usage:
 *   <relative-time datetime="2025-06-12T12:00:00Z"></relative-time>
 */
export class RelativeTimeBuiltin extends TimeElementFormatter {
    /**
     * @private
     * @type {null|number}
     */
    _timer = null;
    innerTimeElement;
    /**
     * Returns the list of attributes to observe for changes.
     * @returns {string[]}
     */
    static get observedAttributes() {
        return ['datetime', 'precision'];
    }
    /**
     * Called when the element is inserted into the DOM.
     * Triggers an initial update and starts a timer to refresh the relative time every second.
     * @returns {void}
     */
    connectedCallback() {
        super.connectedCallback();
        this.updateTime();
        this.scheduleNextUpdate();
    }
    /**
     * Called when the element is removed from the DOM.
     * Clears the update timer.
     * @returns {void}
     */
    disconnectedCallback() {
        this.clearTimer();
    }
    /**
     * Called when an observed attribute changes.
     * @param _name - The name of the attribute.
     * @param _oldValue
     * @param _newValue
     * @returns {void}
     */
    attributeChangedCallback(_name, _oldValue, _newValue) {
        this.updateTime();
        this.scheduleNextUpdate();
    }
    clearTimer() {
        if (this._timer !== null) {
            clearTimeout(this._timer);
            this._timer = null;
        }
    }
    getDuration() {
        const datetime_global = this.datetime_global, timezone = datetime_global?.getTimezoneId(), now = (new Datetime_global(Datetime_global.now(), timezone)).toTemporalZonedDateTime(), zdt = datetime_global?.toTemporalZonedDateTime();
        if (zdt === undefined)
            return null;
        return zdt.until(now, {
            largestUnit: 'years', smallestUnit: 'seconds',
        });
    }
    updateTime() {
        Reflect.apply(RelativeTime.prototype.updateTime, this, Array());
    }
    scheduleNextUpdate() {
        // @ts-expect-error
        Reflect.apply(RelativeTime.prototype.scheduleNextUpdate, this, Array());
    }
    /**
     * Converts a Date object into a human-readable relative time string.
     * @param {Date} date - The date to compare to now.
     * @returns {string} A relative time string (e.g., "2 minutes ago", "in 3 weeks", "now").
     */
    getRelativeTime(date) {
        return Reflect.apply(RelativeTime.prototype.getRelativeTime, this, [date]);
    }
}
const extendsTime = { extends: 'time' };
customElements.define('clock-time-builtin', ClockTimeBuiltin, extendsTime);
customElements.define('relative-time-builtin', RelativeTimeBuiltin, extendsTime);
