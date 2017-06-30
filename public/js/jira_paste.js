/**
 * got to console
 *  console on top left should say "top"
 *  from drop down select iframe_jira (so this code executes in that context
 *  paste in console
 *
 *  Alternatively can use https://chrome.google.com/webstore/detail/custom-javascript-for-web/poakhlngfciodnhlhhgnaaelnpjljija/related?hl=en
 *  open the iframe target path
 *  in custom js added below code
 *  and then open the original
 */
window.addEventListener('message', async function (event) {
    let url = event.origin;
    let hostname = (new URL(url)).hostname;
    let tld = hostname.split('.').pop();
    if ((tld == 'local') || (hostname == 'toggl-timesheet.herokuapp.com')) {
        console.log('received response:  ', event.data);
        let oData = JSON.parse(event.data);
        /**
         * to avoid ajax conflicts consider some thing like
         * https://github.com/pyrsmk/qwest
         * https://github.com/cferdinandi/atomic
         */
        let oldType = $.ajaxSettings.type ;
        if (oData.config.method){
            $.ajaxSettings.type = oData.config.method;
        }
        $.ajaxSettings.processData = false;
        oData.config.processData = false;
        let output = await Promise.resolve(jQuery.when($.ajax(oData.config)));
        $.ajaxSettings.type = oldType ;
        let sendData  = {dataIn:oData,output:output};
        window.parent.postMessage(sendData,'*');
    }
    else {
        console.log('ignoring response from ' + url);
    }

}, false);
if ((document.readyState == 'complete') || (document.readyState == 'interactive')){
    window.parent.postMessage({type:'ready'},'*');
}
else{
    document.addEventListener("DOMContentLoaded", function(){
        window.parent.postMessage({type:'ready'},'*');
    });
}
