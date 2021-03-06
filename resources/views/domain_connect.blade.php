<?php
    require_once resource_path() . '/views/handle-bar-template.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/css/materialize.min.css">
<script>
<?php
require_once public_path() . '/js/load.js';
?>
//    loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js'],'jQuery');
    loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js'],'jQuery');
    loadjs.ready(['jQuery'],{
        success: function() {
            loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.js'],'pjax');
            loadjs(['https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js'],'materialize');

        }
    });
//    loadjs(['https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.js'],'pjax');
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/js-sha1/0.4.1/sha1.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/js-yaml/3.8.4/js-yaml.min.js'
    ],'misc');
    loadjs.ready('pjax', {
        success: function() {
            $.pjax.defaults.timeout = 0;
            $('.loading').hide();
            $(document).on('pjax:send', function() {
                window.pjaxOffSetScrollTop = (document.documentElement.scrollTop || document.body.scrollTop);
                $('.loading').show()
            });
            $(document).on('pjax:complete', function(xhr,textStatus,options) {
                $('.loading').hide();
                ZProjectTemplate.showAllTickets();
                if (window.pjaxOffSetScrollTop){
                    $('html, body').animate({
                        scrollTop: pjaxOffSetScrollTop + 'px'
                    }, 'fast');
                    window.pjaxOffSetScrollTo = 0;
                }
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
        '/js/common.js?hash=z_hash',
        '/js/jira_api.js?hash=z_hash',
        '/js/jira_cors.js?hash=z_hash',
        '/js/jira_cache.js?hash=z_hash',
        '/js/object_state.js?hash=z_hash',
        '/js/project_template.js?hash=z_hash',
        '/js/progress_detect.js?hash=z_hash',
    ],'localjs');
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js'
    ],'cookie');
    loadjs([
        'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js',
    ],'free_load');
    loadjs.ready(['pjax','ClipBoard','localjs','cookie','misc'],{
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
                ZProjectTemplate.checkPointIncrement();
            });
        }
    });
</script>

<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css'>
<link rel="stylesheet" href="/css/zapp.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">

<iframe style="display:none;height:60px"  id="iframe_message" ></iframe>
<div id="iframe-container"></div>
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
<div style="display:none">
    <div id="popUp" title="Popup">
        <p>
            example of popup
        </p>
    </div>
</div>
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
        $(document).on('click','.update-task',this.updateTask);
        $(document).on('click','.refresh-task',this.refreshTask);

        this.binded = true;
    };
    DomainConnect.refreshTask = async (event) => {
        let target = $(event.target);
        if ((target).prop("tagName").toLowerCase() == 'i'){
            target = target.parent();
        }
        let ticket = target.data('ticket');
        let project = target.data('project');
        ZProjectTemplate.clickByPass();
        JiraApi.batchInProcess = false;
        JiraApi.getTicketInfo(project ,ticket);
    };
    DomainConnect.updateTask = async function(event){
//        window.pjaxOffSetScrollTo = $(event.target).offset();
//        window.pjaxOffSetScrollTop = (document.documentElement.scrollTop || document.body.scrollTop);
        ZProjectTemplate.clickByPass();
        window.setTimeout(() => {
            alert('update task only supported by premium');
        }, 1);
        return ;
        let data = jQuery(event.target).closest('.link-container').find('.post-data-send').data('post');
        //        let promised = await Promise.resolve(jQuery.when(DomainConnect.promiseConfirm('testing')));
        data.newDescription = window.prompt('description',data.description);
        let ajaxConfig = {
            url: '/togglUpdate',
            method: "POST",
            data: {togglData:data}
        };
        ajaxConfig.headers = {
            'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
        };
        let output = await Promise.resolve(jQuery.when($.ajax(ajaxConfig)));
        ZProjectTemplate.clickByPass();
        console.log(output);
    };
    DomainConnect.promiseConfirm = function (customMessage){
            let dfd = new jQuery.Deferred();
            $("#popUp").html(customMessage);
            $("#popUp").dialog({
                resizable: false,
                height: 240,
                modal: true,
                buttons: {
                    "OK": function () {
                        $(this).dialog("close");
                        dfd.resolve();
                    },
                    Cancel: function () {
                        $(this).dialog("close");
                        dfd.reject();
                    }
                }
            });
            return dfd.promise();
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
             if (!jsyaml.load(value)){
                 throw new Error('no value set');
             }
            localStorage.setItem(this.cookieName,value);
            Cookies.set(this.cookieName);
        } catch (e) {
            alert('value is not properly formatted json');
        }
    };
    JiraConnect.getConfig = function(){
        //        var vJson= Cookies.get(this.cookieName);
        let vYaml =  localStorage.getItem(this.cookieName);
        if (jsyaml.safeLoad(vYaml)){
            return vYaml;
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
            this.$jira_config.val(jsyaml.safeDump({
                project1:{
                'project_prefix': "proj1",
                'ticket_prefix': "PJ-",
                'base_url': "enter_base_url (no slash at the end)",
                'auth_key': "can_skip",
                'sample_ticket': "PJ-56",
                'iframe_url': "enter_iframe_url like /secure/Dashboard.jspa",
                }
            }));
        }
    };
    JiraConnect.reInit = function(){
        let config = this.getConfig();
        if (!this.$jira_config.val()){
            this.$jira_config.val(config);
        }
        if (!config){
            return false;
        }
        JiraApi.configObject = jsyaml.load(config);
        JiraApi.init();
        return true;
    };
    JiraConnect.saveConfig = function(){
        var vJson = this.$jira_config.val();
        this.setConfig(vJson);
    };
    JiraConnect.sendData = function(event){
        JiraApi.batchInProcess = false;
        let oTimeEntry = jQuery(event.target).data('timeEntry');

        //clicked innert <i>
        if (!oTimeEntry){
            oTimeEntry = jQuery(event.target).parent().data('timeEntry');
        }
        let project = oTimeEntry.project;
        JiraApi.postTime(project,oTimeEntry);
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
