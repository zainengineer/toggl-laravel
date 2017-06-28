JiraApi = {};
JiraApi.initialized = false;
JiraApi.baseUrl = '';
JiraApi.authKey = '';
JiraApi.sampleTicket = '';
JiraApi._ajaxConfig = {};
JiraApi.iframe = null;
JiraApi.lastUpdatedTicket = false;
JiraApi.init = function (baseUrl, authKey, sampleTicket,iframeId,iframeUrl) {
    this.baseUrl = baseUrl;
    this.authKey = authKey;
    this.sampleTicket = sampleTicket;
    this.iframe = document.getElementById(iframeId);
    this.iframe.src = iframeUrl;
    if (baseUrl && authKey && sampleTicket) {
        this.initialized = true;
    }
};
JiraApi.testTicket = function () {
    return this.getTicketInfo(this.sampleTicket);
};
JiraApi.handleRequest = function (config){
    let message = JSON.stringify({config:config});
    this.iframe.contentWindow.postMessage(message,'*');
    // try {
    //     let output = await Promise.resolve(jQuery.when($.ajax(config)));
    //     // let output = await Promise.resolve(jQuery.when( $.ajax( "/js/common.js" ) ));
    //     console.log(output);
    // } catch (e) {
    //     console.log("Error caught");
    //     console.log(e);
    // }
};
JiraApi.getTicketInfo = function (ticketNumber) {
    if (!this.initialized){
        // reject('not initialized');
        throw 'not initialized';
    }
    let baseUrl = this.getBaseUrl() + '/issue/' + ticketNumber;
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    config.method = "GET";
    this.handleRequest(config);
};
JiraApi.postTime = function(timeObject){
    let jiraTicket = timeObject.ticket;
    let baseUrl = this.getBaseUrl() + '/issue/' + jiraTicket + '/worklog';
    this.lastUpdatedTicket = jiraTicket;
    debugger;
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    config.method = "POST";
    config.data = {
        comment: timeObject.jira_entry,
        started: timeObject.jira_start,
        timeSpent: timeObject.jira_time
    };
    /**
     * TODO: investigate why stringify is needed here
     * for some reason jquery ajax in jira creates it into object again
     */
    config.data = JSON.stringify(config.data);
    this.handleRequest(config);
};
JiraApi.getBaseUrl = function () {
    return this.baseUrl + '/rest/api/2';
};
// JiraApi.getJiraTimeForLog = function(fHours, bPadding){
//     let iHour = fHours.floor();
//     let vHour = iHour ? iHour + 'h' : '';
//     let iMinutes = Math.round((fHours - iHour) * 60);
//     let vMinute = iMinutes ? iMinutes + 'm' : '';
//     if (!vHour && bPadding){
//         if (iMinutes < 10) {
//             vMinute = " " + vMinute;
//         }
//         return "   " + vMinute;
//     }
//     return String.trim(vHour +" " + vMinute);
// };
// JiraApi.getJiraDateStartOfLog = function (date){
//     let dtDate = new Date(date);
//     //2017-04-22T20:29:00.804Z
//     let vDate = dtDate.toISOString();
//     //needed format 2017-04-18T10:19:41.0+0930
//
// };
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
JiraApi.processResponse = function(event){
    if (ZJsTools.checkNested(event.data,'fields.worklog.worklogs')){
        JiraCache.saveTicket(event.data);
        ZProjectTemplate.updateTicket(event.data.fields.worklog.worklogs,event.data.key)
    }
    else if(event.data.timeSpent){
        debugger;
        this.getTicketInfo(this.lastUpdatedTicket);
        alert('logged now ' + event.data.timeSpent);
    }
};

window.addEventListener('message', function (event) {
    let url = event.origin;
    let hostname = (new URL(url)).hostname;
    let tld = hostname.split('.').pop();
    JiraApi.processResponse(event);
    console.log(event.data);
    console.log(url);
}, false);
