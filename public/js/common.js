ZJsTools = {};
ZJsTools.bindAllFunctions = function (object) {
    Object.getOwnPropertyNames(object).filter(function (p) {
        let objectFunction = object[p];
        if (typeof objectFunction === 'function'){
            object[p] = objectFunction.bind(object);
        }
    })
};
ZJsTools.checkNested = function(obj,structure){

    let args = structure.split(".");

    for (let i = 0; i < args.length; i++) {
        if (!obj || !obj.hasOwnProperty(args[i])) {
            return false;
        }
        obj = obj[args[i]];
    }
    return true;
};