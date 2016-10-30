<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
class ApiHelper
{
    protected $bEnableCache;
    /** @var static \AJT\Toggl\TogglClient */
    protected $oClient;
    /** @var \Illuminate\Http\Request \Illuminate\Http\Request */
    protected $oRequest;

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

    public function getTimeEntries()
    {
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(10);
        $vCacheKey = 'time_entries_' . $this->getKey();
        $aTimeList = Cache::get($vCacheKey);
        if (!$aTimeList || !$this->bEnableCache){
            $aTimeList = $this->oClient->getTimeEntries(array());
            Cache::put($vCacheKey,$aTimeList,20);
        }
        return $aTimeList;
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