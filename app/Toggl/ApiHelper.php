<?php
namespace App\Toggl;

class ApiHelper
{
    /** @var static \AJT\Toggl\TogglClient */
    protected $oClient;

    public function __construct()
    {
        $vTogglApiKey = $_ENV['TOGGLE_API_KEY'];
        $this->oClient = \AJT\Toggl\TogglClient::factory(['api_key' => $vTogglApiKey]);
    }

    public function getTimeEntries()
    {
        $aTimeList = $this->oClient->getTimeEntries(array());
        return $aTimeList;
    }
}