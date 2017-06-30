ZProjectTemplate = {};
ZProjectTemplate.projectTicketMap = {};
ZProjectTemplate._work_log_template = false;
ZProjectTemplate.logsDisplayedOnce = false;
ZProjectTemplate.checkLoadPoints = 0;
ZProjectTemplate.jsonMeta = {};
ZProjectTemplate.registerTypes = function()
{
    this.workLogsRegister();
};
ZProjectTemplate.workLogsRegister = function()
{
    if(!this._work_log_template){
        let source = $('#work-log-entries').html();
        this._work_log_template = Handlebars.compile(source);
    }
    // ZState.registerType('work-log',)
};
ZProjectTemplate.callBack = function (key,$selector,data){
    // this.regi
};
ZProjectTemplate.updateTicket = function(ticketInfo,ticket,project,worklogsGiven){
    if (project =='aligent'){
        debugger;
    }
    this.workLogsRegister();
    let worklogs;
    let processed = false;
    worklogs = worklogsGiven ? worklogsGiven : ticketInfo.fields.worklog.worklogs;
    if (worklogs.length > 19){
        if (!worklogsGiven){
            processed = JiraApi.processWorkLogPreferCached(project,ticket);
            if (processed){
                return;
            }
        }
    }
    worklogs = this.filterWorkLog(worklogs);
    let context = {worklogs:worklogs};
    let html    = this._work_log_template(context);
    $('.work-log-container.' + project + '.' + ticket).html(html);

    if (ticketInfo){
        let title = ticketInfo.fields.summary;
        $('.ticket-title.' + project + '.' + ticket).html(ticket + ': ' + title);
    }
    return true;
};
ZProjectTemplate.filterWorkLog = function (worklogs) {
    let startDate = new Date(this.jsonMeta.start_date);
    let endDate = new Date(this.jsonMeta.end_date);
    let filteredWorkLogs = [];
    for (let i in worklogs) {
        let worklog = worklogs[i];
        let logDate = new Date(worklog.started);
        if ((!this.jsonMeta.start_date || (logDate >= startDate))
            && (!this.jsonMeta.end_date || (logDate <= endDate))) {
            filteredWorkLogs.push(worklog);
        }
    }
    return filteredWorkLogs;
};

ZProjectTemplate.updateTicketFromCache = function(project,ticket){
    let ticketCache = JiraCache.getTicket(project,ticket);
    if (ticketCache){
        ticketCache.cached = true;
        this.updateTicket(ticketCache,ticket,project);
        return true;
    }
    return false;
};
ZProjectTemplate.updateTicketPreferCache = function (project,ticket){
    if (!this.updateTicketFromCache(project,ticket)){
        JiraApi.getTicketInfo(project,ticket);
    }
};
ZProjectTemplate.getProject = function (ticket){
    return this.projectTicketMap[ticket];
};
ZProjectTemplate.setProjectForTicket = function (project, ticket){
    if (this.projectTicketMap.hasOwnProperty(ticket)){
        if (this.projectTicketMap[ticket] != project){
            throw new Error('cannot map ' + ticket + ' to ' + project + ' already mapped with ' + this.projectTicketMap[ticket]);
        }
    }
    else{
        this.projectTicketMap[ticket] = project;
    }
};
ZProjectTemplate.checkPointIncrement = function(){
    this.checkLoadPoints++;
    let projectCount = Object.keys(JiraApi.configObject).length;
    if (this.checkLoadPoints>projectCount){
        this.showAllTicketsOnce();
    }
};

ZProjectTemplate.showAllTicketsOnce = function(){
    if (!this.logsDisplayedOnce){
        this.showAllTickets();
        this.logsDisplayedOnce = true;
    }
};
ZProjectTemplate.showAllTickets = function()
{
    this.workLogsRegister();
    this.jsonMeta = JSON.parse($('#injected-json').val());
    jQuery('.work-log-container').each(function(index, el ){
        let ticket =  $(el).data( "ticket" );
        let project =  $(el).data( "project" );
        this.updateTicketPreferCache(project,ticket);
    }.bind(this));
};
