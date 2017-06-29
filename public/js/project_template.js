ZProjectTemplate = {};
ZProjectTemplate._work_log_template = false;
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
ZProjectTemplate.updateTicket = function(ticketInfo,ticket){
    this.workLogsRegister();
    let worklogs = ticketInfo.fields.worklog.worklogs;
    let title = ticketInfo.fields.summary;
    let context = {worklogs:worklogs};
    let html    = this._work_log_template(context);
    $('.work-log-container.' + ticket).html(html);
    $('.ticket-title.' + ticket).html(ticket + ': ' + title);
};
ZProjectTemplate.updateTicketFromCache = function(ticket){
    let ticketCache = JiraCache.getTicket(ticket);
    if (ticketCache){
        this.updateTicket(ticketCache,ticket);
        return true;
    }
    return false;
};
ZProjectTemplate.updateTicketPreferCache = function (ticket){
    if (!this.updateTicketFromCache(ticket)){
        JiraApi.getTicketInfo(ticket);
    }
};
ZProjectTemplate.showAllTickets = function()
{
    jQuery('.work-log-container').each(function(index, el ){
        let ticket =  $(el).data( "ticket" );
        this.updateTicketPreferCache(ticket);
    }.bind(this));
};
