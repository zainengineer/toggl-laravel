ZProjectTemplate = {};
ZProjectTemplate.projectTicketMap = {};
ZProjectTemplate._work_log_template = false;
ZProjectTemplate.logsDisplayedOnce = false;
ZProjectTemplate.checkLoadPoints = 0;
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
ZProjectTemplate.updateTicket = function(ticketInfo,ticket,project){
    this.workLogsRegister();
    let worklogs = ticketInfo.fields.worklog.worklogs;
    let title = ticketInfo.fields.summary;
    let context = {worklogs:worklogs};
    let html    = this._work_log_template(context);
    debugger;
    $('.work-log-container.' + project + '.' + ticket).html(html);
    $('.ticket-title.' + project + '.' + ticket).html(ticket + ': ' + title);
};
ZProjectTemplate.updateTicketFromCache = function(project,ticket){
    let ticketCache = JiraCache.getTicket(project,ticket);
    if (ticketCache){
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
    if (this.checkLoadPoints>1){
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
    jQuery('.work-log-container').each(function(index, el ){
        let ticket =  $(el).data( "ticket" );
        let project =  $(el).data( "project" );
        this.updateTicketPreferCache(project,ticket);
    }.bind(this));
};
