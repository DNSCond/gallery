// window.href
"use strict";
Object.defineProperties(window, {
    href: {
        get() {
            return this.location.href;
        }, set(value) {
            this.location.href = value;
        }, enumerable: true, configurable: true,
    },
    origin: {
        get() {
            return this.location.origin;
        }, set(value) {
            this.location.origin = value;
        }, enumerable: true, configurable: true,
    },
    protocol: {
        get() {
            return this.location.protocol;
        }, set(value) {
            this.location.protocol = value;
        }, enumerable: true, configurable: true,
    },
    host: {
        get() {
            return this.location.host;
        }, set(value) {
            this.location.host = value;
        }, enumerable: true, configurable: true,
    },
    hostname: {
        get() {
            return this.location.hostname;
        }, set(value) {
            this.location.hostname = value;
        }, enumerable: true, configurable: true,
    },
    port: {
        get() {
            return this.location.port;
        }, set(value) {
            this.location.port = value;
        }, enumerable: true, configurable: true,
    },
    pathname: {
        get() {
            return this.location.pathname;
        }, set(value) {
            this.location.pathname = value;
        }, enumerable: true, configurable: true,
    },
    search: {
        get() {
            return this.location.search;
        }, set(value) {
            this.location.search = value;
        }, enumerable: true, configurable: true,
    },
    hash: {
        get() {
            return this.location.hash;
        }, set(value) {
            this.location.hash = value;
        }, enumerable: true, configurable: true,
    },
});
