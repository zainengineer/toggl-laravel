<?php
    require_once resource_path() . '/views/handle-bar-template.php';
?>
<script>
<?php
require_once public_path() . '/js/load.js';
?>
    loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js'],'jQuery');
    loadjs.ready(['jQuery'],{
        success: function() {
            loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.js'],'pjax');
        }
    });
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/js-sha1/0.4.1/sha1.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/js-yaml/3.8.4/js-yaml.min.js'
    ],'misc');
    loadjs.ready('pjax', {
        success: function() {
            $.pjax.defaults.timeout = 0;
            $('.loading').hide();
            $(document).on('pjax:send', function() {
                $('.loading').show()
            });
            $(document).on('pjax:complete', function() {
                $('.loading').hide();
            });
            $().ready(function(){
                jQuery(document).pjax('.domain-connect a', '#pjax-container');
            });
        }
    });
    loadjs([
       'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.js'
    ],'ClipBoard');
    loadjs([
        '/js/common.js',
        '/js/jira_api.js',
        '/js/jira_cache.js',
        '/js/object_state.js',
        '/js/project_template.js'
    ],'localjs');
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js'
    ],'cookie');
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js',
    ],'free_load');
    loadjs.ready(['jQuery','ClipBoard','localjs','cookie','misc'],{
        success: function() {
            jQuery(function(){
                zClipBoardBind = new Clipboard('.clip-board-trigger');
            });

            ZJsTools.bindAllFunctions(DomainConnect);
            ZJsTools.bindAllFunctions(JiraConnect);
            ZJsTools.bindAllFunctions(JiraApi);
            ZJsTools.bindAllFunctions(ZProjectTemplate);
            jQuery(function(){
                DomainConnect.bindElements();
                JiraConnect.bindElements();
                ZProjectTemplate.showAllTickets();
            });
        }
    });
</script>

<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.css">

<iframe style="display:none;height:60px"  id="iframe_message" ></iframe>
<iframe style="display:none;height:60px"  id="iframe_jira" ></iframe>
Connect Url: <input class="connect_url_input" type="text" name="connect_url" />
<input class="connect-submit" type="submit"/>
<br/>
Jira Config: <textarea id="jira_config_json" style="width: 300px; height: 120px"

                       name="jira_config"></textarea>
<input class="jira-config-submit" value="save jira config" type="submit"/>
<br/>

<button class="clip-board-trigger" data-clipboard-text="<?php echo htmlentities(file_get_contents(public_path() . '/js/jira_paste.js')); ?>">
    <i class="fa fa-clipboard" aria-hidden="true"></i> Copy js to clipboard
</button>

<br/>
<input class="jira-test-config" value="test jira config" type="submit"/>

<i class="fa fa-spinner fa-spin loading" aria-hidden="true"></i>
{{--<br/>--}}
{{--<br/>--}}
{{--Needs cross header requests enabled--}}
{{--<a href="https://chrome.google.com/webstore/detail/allow-control-allow-origi/nlfbmbojpeacfghkpbjhddihlkkiljbi/related?utm_source=chrome-app-launcher-info-dialog">cross origin extension</a>--}}
<script>

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
        $(document).on('click','.post-data-send',this.sendData);

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


    JiraConnect = {};
    JiraConnect.cookieName = 'jira_config';
    JiraConnect.setConfig = function(value){
        //            Cookies.set(this.cookieName, vJson, { expires: 365 });
        try {
            JSON.parse(value);
            localStorage.setItem(this.cookieName,value);
        } catch (e) {
            alert('value is not properly formatted json');
        }
    };
    JiraConnect.getConfig = function(){
        //        var vJson= Cookies.get(this.cookieName);
        var vJson =  localStorage.getItem(this.cookieName);
        if (JSON.parse(vJson)){
            return vJson;
        }
    };

    JiraConnect.bindElements = function() {
        if (this.binded){
            return;
        }
        this.binded = true;
        this.$jira_config =  jQuery('#jira_config_json');
        $('.jira-config-submit').click(this.saveConfig);
        $('.jira-test-config').click(JiraApi.testTicket);
        $(document).on('click','.jira-send-button',this.sendData);
        if (!this.reInit()){
            this.$jira_config.val(JSON.stringify({
                'base_url': "enter_base_url (no slash at the end)",
                'auth_key': "can_skip",
                'sample_ticket': "CM-56",
                'iframe_url': "enter_iframe_url like /secure/Dashboard.jspa",
            },null, 4));
        }
    };
    JiraConnect.reInit = function(){
        var config = this.getConfig();
        if (!this.$jira_config.val()){
            this.$jira_config.val(config);
        }
        if (!config){
            return false;
        }
        var oConfig = JSON.parse(config);
        JiraApi.init(oConfig.base_url,oConfig.auth_key,oConfig.sample_ticket,'iframe_jira',oConfig.iframe_url);
        return true;
    };
    JiraConnect.saveConfig = function(){
        var vJson = this.$jira_config.val();
        this.setConfig(vJson);
    };
    JiraConnect.sendData = function(event){
        let oTimeEntry = jQuery(event.target).data('timeEntry');
        //clicked innert <i>
        if (!oTimeEntry){
            oTimeEntry = jQuery(event.target).parent().data('timeEntry');
        }
        JiraApi.postTime(oTimeEntry);
        console.log(oTimeEntry);
//        var ajaxRequest = {
//            method: "POST",
//            url:  "/jira",
//            data: {timer: oTimeEntry}
//        };
//        var jqxhr = $.ajax( ajaxRequest )
//        .done(function() {
//            alert( "success" );
//        })
//        .fail(function() {
//            alert( "error" );
//        })
//        .always(function() {
////            alert( "complete" );
//        });
    };


</script>
<br/>
<br/>
</div>