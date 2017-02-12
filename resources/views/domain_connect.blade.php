<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.js"></script>

<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.css">

<iframe style="display:none"  id="iframe_message" style="height:60px"></iframe>
Connect Url: <input class="connect_url_input" type="text" name="connect_url" />
<input class="connect-submit" type="submit"/>
<br/>
Jira Config: <textarea id="jira_config_json" style="width: 300px; height: 120px"

                       name="jira_config"></textarea>
<input class="jira-config-submit" value="save jira config" type="submit"/>
<script>


    $(function(){
        zClipBoardBind = new Clipboard('.clip-board-trigger');
    });
    ZJsTools = {};
    ZJsTools.bindAllFunctions = function (object) {
        Object.getOwnPropertyNames(object).filter(function (p) {
            var objectFunction = object[p];
            if (typeof objectFunction === 'function'){
                object[p] = objectFunction.bind(object);
            }
        })
    };
    DomainConnect = {};
    DomainConnect.cookieName = 'connect_url';

    DomainConnect.bindElements = function (){

        this.JInput =  $('.connect_url_input');
        $('.connect-submit').click (this.getInput);
        this.iframe = document.getElementById('iframe_message');
        var valueInCookie = Cookies.get(this.cookieName);
        if (valueInCookie){
            this.JInput.val(valueInCookie);
            this.sourceConnect(valueInCookie);
        }
        $('.post-data-send').click(this.sendData);

        this.binded = true;
    };

    DomainConnect.sendData = function(event){
        var data = jQuery(event.target).data('post');
        this.sendMessage(data);
    };
    DomainConnect.getInput = function (){
        if (!this.binded){
            throw new Error('not binded yet');
        }
        var urlGiven = this.JInput.val();
        if (urlGiven){
            Cookies.set(this.cookieName, urlGiven, { expires: 365  * 5});
            this.sourceConnect(urlGiven);
        }
    };

    DomainConnect.sourceConnect = function (remoteUrl){
        this.iframe.src = remoteUrl;
    };
    DomainConnect.getMessageWindowObject = function(){
        return this.iframe.contentWindow ;
    };
    DomainConnect.sendMessage = function (message) {
        var win = this.getMessageWindowObject();
        win.postMessage(message,'*');
    };

    ZJsTools.bindAllFunctions(DomainConnect);

    $(function(){
        DomainConnect.bindElements();
        JiraConnect.bindElements();
    });

    JiraConnect = {};
    JiraConnect.cookieName = 'jira_config';
    JiraConnect.bindElements = function() {
        if (this.binded){
            return;
        }
        this.binded = true;
        this.$jira_config =  jQuery('#jira_config_json');
        $('.jira-config-submit').click(this.saveConfig);
        $('.jira-send-button').click(this.sendData);
        var config;
        if (config = this.getConfig()){
            this.$jira_config.val(config)
        }
        else{
            this.$jira_config.val(JSON.stringify({
                'base_url': "enter_base_url",
                'auth_key': "enter_auth"
            },null, 4));
        }
    };
    JiraConnect.saveConfig = function(){
        var vJson = this.$jira_config.val();
        var oJson = JSON.parse(vJson);
        if (oJson){
            Cookies.set(this.cookieName, vJson, { expires: 365 });
        }
    };
    JiraConnect.getConfig = function () {
        var vJson= Cookies.get(this.cookieName);
        if (vJson && (JSON.parse(vJson))){
            return vJson;
        }
    };
    JiraConnect.sendData = function(event){
        var oTimeEntry = jQuery(event.target).data('timeEntry')
        //clicked innert <i>
        if (!oTimeEntry){
            oTimeEntry = jQuery(event.target).parent().data('timeEntry')
        }
        var vTimeEntry;
        var ajaxRequest = {
            method: "POST",
            url:  "/jira",
            data: {timer: oTimeEntry}
        };
        var jqxhr = $.ajax( ajaxRequest )
        .done(function() {
            alert( "success" );
        })
        .fail(function() {
            alert( "error" );
        })
        .always(function() {
//            alert( "complete" );
        });
    };
    ZJsTools.bindAllFunctions(JiraConnect);

</script>
<br/>
<br/>