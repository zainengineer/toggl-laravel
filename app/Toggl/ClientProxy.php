<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
class ClientProxy
{
    protected $bEnableCache;
    /** @var static \AJT\Toggl\TogglClient */
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
        $this->oClient = \AJT\Toggl\TogglClient::factory(['api_key' => $vTogglApiKey]);
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