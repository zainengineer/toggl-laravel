ZJsTools = {};
ZJsTools.bindAllFunctions = function (object) {
    Object.getOwnPropertyNames(object).filter(function (p) {
        var objectFunction = object[p];
        if (typeof objectFunction === 'function'){
            object[p] = objectFunction.bind(object);
        }
    })
};