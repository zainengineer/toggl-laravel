JiraApi = {};
JiraApi = {};
JiraApi.initialized = false;
JiraApi.baseUrl = '';
JiraApi.authKey = '';
JiraApi.sampleTicket = '';
JiraApi._ajaxConfig = {};
JiraApi.iframe = null;
JiraApi.init = function (baseUrl, authKey, sampleTicket,iframeId) {
    this.baseUrl = baseUrl;
    this.authKey = authKey;
    this.sampleTicket = sampleTicket;
    this.iframe = document.getElementById(iframeId);
    this.iframe.src = baseUrl;
    if (baseUrl && authKey && sampleTicket) {
        this.initialized = true;
    }
};
JiraApi.testTicket = function () {
    return this.getTicketInfo(this.sampleTicket);
};
JiraApi.getTicketInfo = async function (ticketNumber) {
    if (!this.initialized){
        // reject('not initialized');
        throw 'not initialized';
    }
    let baseUrl = this.getBaseUrl() + '/issue/' + ticketNumber;
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    let message = JSON.stringify({config:config});
    this.iframe.contentWindow.postMessage(message,'*');
    return ;
    try {
        let output = await Promise.resolve(jQuery.when($.ajax(config)));
        // let output = await Promise.resolve(jQuery.when( $.ajax( "/js/common.js" ) ));
        console.log(output);
    } catch (e) {
        console.log("Error caught");
        console.log(e);
    }
    // let output =$.when( $.ajax( "test.aspx" ) );
    // console.log(output);
};
JiraApi.getBaseUrl = function () {
    return this.baseUrl + '/rest/api/2';
};
JiraApi.getAjaxConfig = function () {
    if (this._ajaxConfig) {
        this._ajaxConfig.headers = {
            'Content-Type': 'application/json',
            // 'crossDomain': true,
            // withCredentials: true,
            // "Authorization": "Basic " + this.authKey,
            'dummy':1
        };
    }
    return this._ajaxConfig;
};
ZJsTools.bindAllFunctions(JiraApi);

window.addEventListener('message', function (event) {
    let url = event.origin;
    let hostname = (new URL(url)).hostname;
    let tld = hostname.split('.').pop();
    console.log(event.data);
    console.log(url);
}, false);
