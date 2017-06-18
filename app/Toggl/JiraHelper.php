<?php

namespace App\Toggl;
use Illuminate\Support\Facades\Cache;
use Guzzle\Http\Client;
use Guzzle\Http\Message;
class JiraHelper
{
    protected $vBaseUrl;
    protected $vAuthKey;
    /** @var  Client */
    protected $oClient;
    /** @var  TimeEntries */
    protected $oTimeHelper;
    public function __construct(\Illuminate\Http\Request $oRequest, TimeEntries $oTimeHelper)
    {
        $this->oTimeHelper = $oTimeHelper;
        //move logic to java script
        if (empty($_COOKIE['jira_config'])){
            return;
        }
        $vJiraConfig = $_COOKIE['jira_config'];
        $aJsonConfig = json_decode($vJiraConfig,true);
        $this->vBaseUrl = $aJsonConfig['base_url'];
        $this->vAuthKey = $aJsonConfig['auth_key'];
        if (!$this->vBaseUrl || !$this->vAuthKey){
            throw (new \Exception('Need Jira base url and authentication before can continue'));
        }
        $this->vBaseUrl = rtrim($this->vBaseUrl, "/") . '/';

        // Create a client and provide a base URL
        $this->oClient = new \GuzzleHttp\Client(['base_uri' => $this->vBaseUrl . 'rest/api/2']);

    }
    public function addTimeLog($vIssue, $vStartedAt, $vTime, $vComment)
    {
        $aData = [
            'comment'   => $vComment,
            'started'   => $this->jiraRestDateFormat($vStartedAt),
            'timeSpent' => $this->oTimeHelper->getJiraTime($vTime, false),
        ];
        $vData = json_encode($aData,JSON_PRETTY_PRINT);
        $oRequest = $this->oClient->post("issue/$vIssue/worklog",null, $vData);
        $this->addCommonHeaders($oRequest);
        $oRequest->send();

    }
    public function jiraRestDateFormat($vTime)
    {
        $vFormatted = date(DATE_ISO8601, strtotime($vTime));
        if (!strpos($vFormatted,'.')){
            /**
             * Jira is not pure compatible with ISO8601, it won't work until you have decimal place in date
             * Otherwise it results in an Internal Server Error (which is strange)
             *
             * Also note that PHP recommends using DATE_ATOM for full compatibility with
             * ISO8601 but it is even worse (basically it uses 00 time zone)
             *
             * Sample: 2017-04-18T10:19:41.0+0930
             */
            $vFormatted = str_replace("+", ".0+", $vFormatted);
        }
        return $vFormatted;

    }
    protected function addCommonHeaders(Message\RequestInterface $oRequest)
    {
        $oRequest->addHeader('Authorization', 'Basic ' . $this->vAuthKey);
        $oRequest->addHeader('content-type','application/json');
    }
}