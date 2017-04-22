window.addEventListener('message', async function (event) {
    let url = event.origin;
    let hostname = (new URL(url)).hostname;
    let tld = hostname.split('.').pop();
    if ((tld == 'local') || (hostname == 'toggl-timesheet.herokuapp.com')) {
        console.log('received response:  ', event.data);
        let oData = JSON.parse(event.data);
        let oldType = $.ajaxSettings.type ;
        if (oData.config.method){
            $.ajaxSettings.type = oData.config.method;
        }
        $.ajaxSettings.processData = false;
        oData.config.processData = false;
        let output = await Promise.resolve(jQuery.when($.ajax(oData.config)));
        $.ajaxSettings.type = oldType ;
        window.parent.postMessage(output,'*');
    }
    else {
        console.log('ignoring response from ' + url);
    }

}, false);
