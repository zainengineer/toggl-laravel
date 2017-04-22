JiraApi = {};
JiraApi = {};
JiraApi.initialized = false;
JiraApi.baseUrl = '';
JiraApi.authKey = '';
JiraApi.sampleTicket = '';
JiraApi._ajaxConfig = {};
JiraApi.init = function (baseUrl, authKey, sampleTicket) {
    this.baseUrl = baseUrl;
    this.authKey = authKey;
    this.sampleTicket = sampleTicket;
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
            "Authorization": "Basic " + this.authKey
        };
    }
    return this._ajaxConfig;
};
ZJsTools.bindAllFunctions(JiraApi);