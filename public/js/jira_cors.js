/**
 * Jira Cross scripting
 * https://github.com/zainengineer/ForceCORS
 * (use for as it changes jira user angent and referer)
 *
 * example of json is cords_config.json
 *
 */
JiraCors = {};
JiraCors.initialized = false;
JiraCors.exampled = false;
JiraCors.init = function () {
    if (JiraCors.initalized = false) {
        return;
    }
    JiraCors.initialized = true;
    ZProjectTemplate.showAllTicketsOnce()
};
JiraCors.processMessage = async function (oData) {
    // JiraCors.processMessageTest();
    // return;

    // if (JiraCors.exampled){
    //     return ;
    // }
    // JiraCors.exampled = true;
    // if (oData.config.method) {
    //     jQuery.ajaxSettings.type = oData.config.method;
    // }
    delete oData.config.headers;
    oData.config.headers = {
        "X-Atlassian-Token":"no-check"
    };
    oData.config.contentType = 'application/json';
    oData.config.xhrFields = {
        withCredentials: true
    };
    oData.config.crossDomain = true;
    oData.config.processData = false;
    try{
        let output = await Promise.resolve(jQuery.when(jQuery.ajax(oData.config)));
        let sendData = {dataIn: oData, output: output};
        let event = {};
        event.data = sendData;
        JiraApi.processResponse(event);
    }
    catch (e){
        console.error(e);
        console.error(oData);
        alert("issue in posting data, remove quote etc from toggl comments")
    }

};