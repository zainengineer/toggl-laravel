<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
class ApiHelper
{
    /** @var static \AJT\Toggl\TogglClient */
    protected $oClient;

    public function __construct()
    {
        $vTogglApiKey = $this->getKey();
        $this->oClient = \AJT\Toggl\TogglClient::factory(['api_key' => $vTogglApiKey]);
    }

    public function getTimeEntries()
    {
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(10);
        $vCacheKey = 'time_entries_' . $this->getKey();
        $aTimeList = Cache::get($vCacheKey);
        if (!$aTimeList){
            $aTimeList = $this->oClient->getTimeEntries(array());
            Cache::put($vCacheKey,$aTimeList,20);
        }
        return $aTimeList;
    }
    protected function getKey()
    {
        return $_ENV['TOGGLE_API_KEY'];
    }
}