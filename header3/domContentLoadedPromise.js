// domContentLoadedPromise

globalThis.domContentLoadedPromise = new Promise(function (resolve) {
    if (document.readyState !== 'loading') resolve(document); else {
        window.document.addEventListener('DOMContentLoaded', () => resolve(document), {once: true});
    }
});
