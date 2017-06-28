/**
 * not in use
 * @type {{}}
 */
ZState = {};
ZState._types = {};
ZState._domOut = {};
ZState._lastState = {};
ZState.registerType = function (type, callBack) {
    if (!this._types.hasOwnProperty(type)) {
        this._types.type = callBack;
    }
};
ZState.registerDomOut = function (key, type, selector) {
    this._domOut.key = {type: type, selector: selector};
};
ZState.stateChanged = function (key, data) {
    let jsonData = JSON.stringify(data);
    let domOut = this._domOut[key];
    let $selector = $(domOut.selector);
    if (this.isStateChange(key, $selector, jsonData)) {
        let callBack = this._types[domOut.type];
        let html = callBack.call($selector, data);
        this.updateHtml($selector, html);
    }
};
ZState.updateHtml = function ($selector, html) {

};
ZState.isStateChange = function (key, $selector, jsonData) {
    let jsonHash = sha1(jsonData);
    if ($selector.data('zStateHash') != jsonHash) {
        $selector.data('zStateHash', jsonHash);
        return true;
    }
};

ZState.loadData = function (objectData) {

};