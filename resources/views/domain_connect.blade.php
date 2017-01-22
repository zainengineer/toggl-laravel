<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js"></script>
<iframe style="display:none" src="." id="iframe_message" style="height:60px"></iframe>
Connect Url: <input class="connect_url_input" type="text" name="connect_url" />
<input class="connect-submit" type="submit"/>
<script>
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
    });

</script>
<br/>
<br/>