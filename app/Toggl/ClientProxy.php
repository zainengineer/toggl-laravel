<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
class ClientProxy
{
    /**
     *
     */
    protected $bEnableCache;
    protected $oLastCommand;
    /**
     * @mixing \MorningTrain\TogglApi\TogglApi
     * @var \MorningTrain\TogglApi\TogglApi
     */
    protected $oClient;

    public function __construct(\Illuminate\Http\Request $oRequest)
    {
        $this->bEnableCache = isset($_GET['enable_cache']) ? $_GET['enable_cache'] : true;
        $this->oRequest = $oRequest;
        $this->resetClient();
    }
    Public function resetClient()
    {
        $vTogglApiKey = $this->getKey();

        $this->oClient = new \MorningTrain\TogglApi\TogglApi($vTogglApiKey);

//        $this->oClient->getEventDispatcher()->addListener('client.command.create', function (\Guzzle\Common\Event $e) {
//
//            $this->oLastCommand = $e['command'];
//        });

    }
    /**
     *
     * @param $methodName
     * @param $args
     *
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        $vCacheKey = $this->getCacheKey($methodName,$args);
        if ($this->bEnableCache && $vCacheKey){
            $cachedValue =  Cache::get($vCacheKey);
            if ($cachedValue){
                return $cachedValue;
            }
        }
        $return = call_user_func_array(array($this->oClient, $methodName), $args);
//        $oRequest = $this->oLastCommand->getRequest();
//        require_once app_path() . '/../vendor/namshi/cuzzle/src/Namshi/Guzzle/Formatter/CurlShellFormatter.php';
//        $vCommand =  (new CurlShellFormatter())->format($oRequest);
        //this is a dummy line so breakpoint can be placed on this line

        $vRequest = (new \Namshi\Cuzzle\Formatter\CurlFormatter())->format($this->oClient->oLastRequest, []);

        if ($vCacheKey){
            Cache::put($vCacheKey,$return,20);
        }
        return $return;
    }
    protected function getCacheKey($methodName,$args)
    {
        $vMethodType = substr($methodName,0,3);
        if ($vMethodType == 'get') {
            $vCacheKey = implode('-',[$this->getKey(),$methodName,@json_encode($args)]);
            return sha1($vCacheKey);
        }
    }
    public function isValidKey()
    {
        try {
            $this->resetClient();
            $this->oClient->getClients();
        } catch(\Exception $e){
            return false;
        }
        return true;
    }
    protected function getKey()
    {
        return $this->oRequest->cookie('toggl_api') ? : @$_ENV['TOGGL_API_KEY'];
    }
}