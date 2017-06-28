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
ZProjectTemplate.updateTicket = function(worklogs,ticket){
    this.workLogsRegister();
    let context = {worklogs:worklogs};
    let html    = this._work_log_template(context);
    $('.work-log-container.' + ticket).html(html);
};