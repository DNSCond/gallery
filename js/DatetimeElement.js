// Datetime
class TimeElement extends HTMLTimeElement {
    get timeAsDate() {
        const date = this.getAttribute('datetime');
        if (date === null) return null;
        return new Date(date);
    }

    set timeAsDate(value) {
        if (value === null) {
            this?.removeAttribute('datetime');
            return;
        }
        const d = new Date(value);
        this.setAttribute('datetime', d.toISOString());
    }
}

class ClockTime extends TimeElement {
    static observedAttributes = ['datetime', 'data-format'];

    connectedCallback() {
        this.updateTime();
    } // disconnectedCallback() {}

    attributeChangedCallback(_name, _oldValue, _newValue, _xmlns) {
        this.updateTime();
    }


    updateTime() {
        const d = this.timeAsDate;
        switch (this.getAttribute('data-format')?.toLowerCase()) {
            case 'isostring':
                this.textContent = d.toISOString();
                break;
            case 'timestring':
                this.textContent = d.toTimeString();
                break;
            case 'datestring':
                this.textContent = d.toDateString();
                break;
            case 'utcstring':
                this.textContent = d.toUTCString();
                break;
            case 'localestring':
                this.textContent = d.toLocaleString(undefined, {hour12: false});
                break;
            case 'tostring':
                this.textContent = d.toString();
                break;
            default:
                this.textContent = d.toString().slice(0, 24);
        }
    }
}

class RelativeTime extends TimeElement {
    static observedAttributes = ['datetime'];

    constructor() {
        super();
        this._timer = null;
    }

    connectedCallback() {
        this.updateTime();
        this.scheduleNextUpdate();
    }

    disconnectedCallback() {
        this.clearTimer();
    }

    updateTime() {
        this.textContent = this.getRelativeTime(this.timeAsDate);
    }

    clearTimer() {
        if (this._timer !== null) {
            clearTimeout(this._timer);
            this._timer = null;
        }
    }

    scheduleNextUpdate() {
        this.clearTimer();

        const date = this.timeAsDate;
        if (!date) return; // No valid datetime, no updates needed

        const absDiffInSeconds = Math.abs((Date.now() - date.getTime()) / 1000);

        // Determine the update interval based on the time difference
        let intervalMs;
        if (absDiffInSeconds >= 31536000) { // >1 year
            return; // No updates needed, relative time won't change soon
        } else if (absDiffInSeconds >= 2629746) {
            intervalMs = 24 * 60 * 60 * 1000;
        } else if (absDiffInSeconds >= 604800) {
            intervalMs = 60 * 60 * 1000;
        } else if (absDiffInSeconds >= 3600) {
            intervalMs = 60 * 1000;
        } else if (absDiffInSeconds >= 60) {
            intervalMs = 1000;
        } else {
            intervalMs = 100;
        }

        this._timer = setTimeout(() => {
            this.updateTime();
            this.scheduleNextUpdate();
        }, intervalMs);
    }

    getRelativeTime(date) {
        const now = Date.now();
        const diffInSeconds = Math.floor((now - +date) / 1000);
        const absDiff = Math.abs(diffInSeconds);

        // Helper function to format the output
        const format = function (value, unit) {
            if (diffInSeconds < 0) return `in ${value} ${unit}`;
            return `${value} ${unit} ago`;
        };

        // Years
        if (absDiff >= 31536000) {
            const years = Math.floor(absDiff / 31536000);
            return format(years, years === 1 ? 'year' : 'years');
        }
        // Months (~30.44 days)
        if (absDiff >= 2629746) {
            const months = Math.floor(absDiff / 2629746);
            return format(months, months === 1 ? 'month' : 'months');
        }
        // Weeks
        if (absDiff >= 604800) {
            const weeks = Math.floor(absDiff / 604800);
            return format(weeks, weeks === 1 ? 'week' : 'weeks');
        }
        // Days
        if (absDiff >= 86400) {
            const days = Math.floor(absDiff / 86400);
            return format(days, days === 1 ? 'day' : 'days');
        }
        // Hours
        if (absDiff >= 3600) {
            const hours = Math.floor(absDiff / 3600);
            return format(hours, hours === 1 ? 'hour' : 'hours');
        }
        // Minutes
        if (absDiff >= 60) {
            const minutes = Math.floor(absDiff / 60);
            return format(minutes, minutes === 1 ? 'minute' : 'minutes');
        }
        // sub Seconds
        if (absDiff < 1) return 'now';
        // Seconds
        return format(absDiff, absDiff === 1 ? 'second' : 'seconds');
    }
}

customElements.define('relative-time', RelativeTime, {extends: 'time'});
customElements.define('clock-time', ClockTime, {extends: 'time'});
