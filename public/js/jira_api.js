JiraApi = {};
JiraApi.initialized = false;
JiraApi.baseUrl = '';
JiraApi.authKey = '';
JiraApi.sampleTicket = '';
JiraApi._ajaxConfig = {};
JiraApi.iframe = null;
JiraApi.lastUpdatedTicket = false;
JiraApi.lastProject = false;
JiraApi.configObject = {};
JiraApi.unResolvedTickets = 0;
JiraApi.batchInProcess = false;
JiraApi.init = function (baseUrl, authKey, sampleTicket,iframeId,iframeUrl) {
    this.initIframe();
    this.initialized = true;
};
JiraApi.initIframe = function()
{
    let $iframeContainer = $('#iframe-container');
    for (let prop in this.configObject) {
        if (this.configObject.hasOwnProperty(prop)){
            let configProject = this.configObject[prop];
            let iframeUrl = configProject.iframe_url;
            let projectPrefix = configProject.project_prefix;
            let iframeId = 'iframe_project_' + projectPrefix;
            $iframeContainer.append('<iframe style="display:none;height:60px" id="'+ iframeId + '"></iframe>');
            configProject.iframe = document.getElementById(iframeId);
            configProject.iframe.src = iframeUrl;
        }
    }

};

JiraApi.testTicket = function () {
    return this.getTicketInfo(this.sampleTicket);
};


       JiraApi.handleRequest = function (project,ticket,config,additional_meta){
    project = this.getProjectFromTicket(ticket, project);
    let message = JSON.stringify({config:config,
        meta:{project:project,ticket:ticket,additional_meta:additional_meta}});
    this.getIframe(project).contentWindow.postMessage(message,'*');
    // try {
    //     let output = await Promise.resolve(jQuery.when($.ajax(config)));
    //     // let output = await Promise.resolve(jQuery.when( $.ajax( "/js/common.js" ) ));
    //     console.log(output);
    // } catch (e) {
    //     console.log("Error caught");
    //     console.log(e);
    // }
};
JiraApi.getTicketInfo = function (project,ticketNumber) {
    if (!this.initialized){
        // reject('not initialized');
        throw 'not initialized';
    }
    let baseUrl = this.getBaseUrl(project) + '/issue/' + ticketNumber;
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    config.method = "GET";
    ZProjectTemplate.setProjectForTicket(project,ticketNumber);
    this.unResolvedTickets++;
    this.handleRequest(project,ticketNumber,config,{by_pass_cache:true});
};
JiraApi.deleteWorkLog = function (project,ticketNumber,workLogId) {
    if (!this.initialized){
        // reject('not initialized');
        throw 'not initialized';
    }
    let baseUrl = this.getBaseUrl(project) + '/issue/' + ticketNumber + '/worklog/' + workLogId;
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    config.method = "DELETE";
    ZProjectTemplate.setProjectForTicket(project,ticketNumber);
    this.handleRequest(project,ticketNumber,config);
};
JiraApi.processWorkLogPreferCached = function (project,ticket){
    let worklog = JiraCache.getWorkLog(project,ticket);
    if (worklog){
        worklog.cached = true;
        return ZProjectTemplate.updateTicket(false,ticket,project,worklog);
    }
    else{
        this.processWorkLog(project,ticket);
    }

};
JiraApi.processWorkLog = function (project,ticketNumber) {
    if (!this.initialized){
        // reject('not initialized');
        throw 'not initialized';
    }
    let baseUrl = this.getBaseUrl(project) + '/issue/' + ticketNumber + '/worklog';
    let config = this.getAjaxConfig();
    config.url = baseUrl;
    config.method = "GET";
    ZProjectTemplate.setProjectForTicket(project,ticketNumber);
    this.handleRequest(project,ticketNumber,config);
};

JiraApi.postTime = function(project,timeObject){
    if (!timeObject.jira_time){
        alert('no time entered for this ticket');
        console.error('no time provided');
        return ;
    }
    let jiraTicket = timeObject.ticket;
    let baseUrl = this.getBaseUrl(project) + '/issue/' + jiraTicket + '/worklog';
    this.lastUpdatedTicket = jiraTicket;
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
    ZProjectTemplate.setProjectForTicket(project,jiraTicket);
    this.handleRequest(project,jiraTicket,config);
};
JiraApi.getBaseUrl = function (project) {
    //ticket is sent instead of project name
    project = this.getProjectFromTicket(project, project);
    return this.configObject[project].base_url + '/rest/api/2';
};
JiraApi.getIframe = function (project) {
    return this.configObject[project].iframe;
};
/**
 * @param ticket
 * @param project
 * @returns {*}
 */
JiraApi.getProjectFromTicket = function(ticket,project){
    project = project.toLowerCase();
    ticket = ticket.toLowerCase();
    if (this.configObject.hasOwnProperty(project)){
        return project;
    }
    for (let i in this.configObject) {
        let obj = this.configObject[i];
        if (ticket.indexOf(obj.ticket_prefix.toLowerCase())===0){
            return obj.project_prefix;
        }
    }
    debugger;
    throw new Error('invalid project in data ' + project);
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
JiraApi.allRequestsProcessed = (noRepeat) =>{
    if (!JiraApi.batchInProcess){
        ProgressDetect.flagDuplicateHashes();
        ProgressDetect.trackEntered();
        if (!noRepeat) {
            window.setTimeout(JiraApi.allRequestsProcessed.bind(null, true), 700);
            window.setTimeout(JiraApi.allRequestsProcessed.bind(null, true), 2000);
        }
        return ;
    }

    JiraApi.unResolvedTickets--;
    if (JiraApi.unResolvedTickets < 0) {
        JiraApi.unResolvedTickets = 0;
    }
    if (!JiraApi.unResolvedTickets){
        console.log('processing requests');
        ProgressDetect.flagDuplicateHashes();
        ProgressDetect.trackEntered();
    }

};
JiraApi.processResponse = function(event){

    let output = ZJsTools.checkNested(event,'data.output',true) ?  event.data.output : {};
    let meta = ZJsTools.checkNested(event,'data.dataIn.meta',true) ?  event.data.dataIn.meta : {};
    let method = ZJsTools.checkNested(event,'data.dataIn.config.method',true) ?  event.data.dataIn.config.method : {};
    if (output && ZJsTools.checkNested(output,'fields.worklog.worklogs')){
        let ticketNumber = meta.ticket;
        let project = meta.project;
        JiraCache.saveTicket(project,ticketNumber,output);
        ZProjectTemplate.updateTicket(output,ticketNumber,project,null,meta);
        this.allRequestsProcessed();
    }
    else if(output && output.timeSpent){
        this.getTicketInfo(meta.project ,meta.ticket);
        alert('logged now ' + output.timeSpent);
    }
    else if (event.data.type == 'ready'){
        ZProjectTemplate.checkPointIncrement();
    }
    else if (output && output.worklogs){
        this.allRequestsProcessed();
        let worklogs = this.removeIncorrectWorkLog(output.worklogs);
        JiraCache.saveWorkLog(meta.project,meta.ticket,worklogs);
        ZProjectTemplate.updateTicket(false,meta.ticket,meta.project,worklogs);
    }
    else if (jQuery.isEmptyObject(output) && (method == 'DELETE')){
        this.allRequestsProcessed();
        this.getTicketInfo(meta.project ,meta.ticket);
        ZProjectTemplate.clickByPass();
    }
    else{
        debugger;
        console.log(event.data);
    }
};
JiraApi.removeIncorrectWorkLog = function(worklogs){
    let filteredWorkLogs = [];
    for (let i in worklogs) {
        let worklog = worklogs[i];
        let authorName = worklog.author.name;
        if (authorName =='zain') {
            filteredWorkLogs.push(worklog);
        }
    }
    return filteredWorkLogs;
};

window.addEventListener('message', function (event) {
    let url = event.origin;
    let hostname = (new URL(url)).hostname;
    let tld = hostname.split('.').pop();
    JiraApi.processResponse(event);
    console.log(event.data);
    console.log(url);
}, false);
