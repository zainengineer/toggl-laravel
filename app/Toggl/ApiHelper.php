<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
class ApiHelper
{
    protected $bEnableCache;
    /** @var  ClientProxy */
    protected $oClientProxy;
    /** @var \Illuminate\Http\Request */
    protected $oRequest;

    public function __construct(ClientProxy $oClientProxy)
    {
        $this->oClientProxy = $oClientProxy;
    }
    Public function resetClient()
    {
        $vTogglApiKey = $this->getKey();
        $this->oClient = \AJT\Toggl\TogglClient::factory(['api_key' => $vTogglApiKey]);
    }
    public function getProjects()
    {

    }

    public function getTimeEntries($vStartDate, $vEndDate)
    {
        $aParam = [];
        if ($vStartDate){
            $aParam['start_date'] = $vStartDate;
        }
        if ($vEndDate){
            $aParam['end_date'] = $vEndDate;
        }
        $aTimeList = $this->oClientProxy->getTimeEntries($aParam);
        return $aTimeList;
    }
    public function isValidKey()
    {
        return $this->oClientProxy->isValidKey();
    }
}