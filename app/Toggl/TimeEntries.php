<?php
namespace App\Toggl;
use Illuminate\Http\Request;

class TimeEntries
{
    /** @var  ApiHelper */
    protected $oApiHelper;
    /** @var  \App\Toggl\JiraHelper  */
    protected $oJiraHelper;
    /** @var  Request */
    protected $oRequest;
    /** \App\Http\Controllers\TogglController */
    protected $oTogglController;
    public function setTogglController(\App\Http\Controllers\TogglController $oToggleController)
    {
        $this->oTogglController = $oToggleController;
    }
    public function __construct(ApiHelper $oHelper
        , Request $oRequest)
    {
        $this->oApiHelper = $oHelper;
        $this->oRequest = $oRequest;
    }

    public function isTicket($vTicket)
    {
        return ((strpos($vTicket, '#') === 0
            || is_numeric($vTicket)
            || strpos($vTicket, '-')
        ));
    }
    public function getStartDate()
    {
        static $vStartDate;
        static $isRecursive;
        if ($isRecursive === true){
            return false;
        }
        if ($vStartDate){
            return $vStartDate;
        }
        $vStartDate = $this->oRequest->get('start_date');
        if (!$vStartDate &&  $this->oRequest->get('today')){
            $vStartDate = date('Y-m-d');
        }
        if ($vStartDate){
            $vStartDate = date('c',strtotime($vStartDate));
        }
        if (!$vStartDate &&
                $this->oTogglController){
            $isRecursive = true;
            $iStamp = $this->oTogglController->getClosestMondayStamp();
            $isRecursive = false;
            $vStartDate = date('c',$iStamp);
        }
        return $vStartDate;
    }
    public function getEndDate()
    {
        static $vEndDate;
        static $isRecursive;
        if ($isRecursive === true){
            return false;
        }
        if ($vEndDate){
            return $vEndDate;
        }
        $vEndDate= $this->oRequest->get('end_date');
        if ($vEndDate){
            $vEndDate = date('c',strtotime($vEndDate));
        }
        if (!$vEndDate){
            $isRecursive = true;
            $iStamp = $this->oTogglController->getClosestSundayStamp();
            $isRecursive = false;
            if ($iStamp){
                $vEndDate = date('c',$iStamp);
            }
        }
        return $vEndDate;
    }
    public function getTimeEntries($vStartDate,$vEndDate,$bDisableCache)
    {
        $bCacheStatus = $this->oApiHelper->getCacheStatus();
        if ($bCacheStatus && $bDisableCache){
            $this->oApiHelper->setCacheEnable(false);
        }
        $aTimeEntries =  $this->oApiHelper->getTimeEntries($vStartDate,$vEndDate);
        if ($bCacheStatus && $bDisableCache){
            $this->oApiHelper->setCacheEnable(true);
        }
        return $aTimeEntries;
    }
    public function fixColon($aTimeEntries)
    {
        $aMissingTicket = [];
        $aMissingColon = [];
        $timeAgo = new \Westsworld\TimeAgo();
        foreach ($aTimeEntries as $aEntry) {
            $vDescription = $aEntry['description'];
            $aParts = explode(" ",$vDescription);
            $vTicketNumber = $aParts[0];
            $vDate = $aEntry['start'];
            $iDiff = time() - strtotime($vDate);
            $fDays = $iDiff/ (24 * 60 * 60);
            $vWeekDay =date('D',strtotime($vDate));
            if ($fDays >6){
                $vAgo =  $timeAgo->inWords($vDate);
                $vDate = "$vWeekDay $vAgo";
            }
            //no ticket number yet
            if (!strpos($vTicketNumber,'-')){
                $aMissingTicket[] = $vDescription  . ' > ' . $vDate;
                continue;
            }
            if (!strpos($vTicketNumber,':')){
                $aMissingColon[] = $vDescription . ' > ' . $vDate;
                $aParts[0] = $vTicketNumber . ':';
                $aUpdatedTask = $aEntry;

                $aUpdatedTask['description'] = implode(' ', $aParts);
//                $this->oApiHelper->updateEntry($aUpdatedTask);
            }
        }
        if (!function_exists('d')){
            function d($var){
                var_dump($var);
            }
        }
        $aMissingColon? !d($aMissingColon) :!d('no missing colon');
        $aMissingColon = $aMissingColon ?:'no missing colon';
        $aMissingTicket = $aMissingTicket?:'no missing ticket number';
        !d($aMissingTicket);
        !d($aMissingColon);
//        $this->oApiHelper->setCacheEnable(false);
//        $this->getTimeEntries();
//        $this->oApiHelper->setCacheEnable(true);
    }
    public function getEntriesByProject()
    {
        $aTimeEntries = $this->oApiHelper->getTimeEntries($this->getStartDate(),$this->getEndDate());
        $aReturn = [];
        foreach ($aTimeEntries as $aTime) {
            if (!isset($aTime['description'])) {
                continue;
            }
            $vDescription = $aTime['description'];
            $aMeta = $this->getMetaInfo($vDescription);
            $vProjectName = $aMeta['project'];
            $iTicket = $aMeta['ticket'];
            $vDate = date('d-M-Y', strtotime($aTime['start']));
            $fSeconds = $aTime['duration'];
            if ($fSeconds < 0) {
                //task is running
                $fSeconds = (time() - strtotime($aTime['start']));
            }

            if (!isset($aTime['stop'])){
                continue;
            }

            $fDuration = $this->secondsToHours($fSeconds);
            $iStartStamp = strtotime($aTime['start']);
            $aRow = [
                'toggl-id'     => $aTime['id'],
                'toggl-guid'   => $aTime['guid'],
                'description'  => $vDescription,
                'ticket'       => $iTicket,
                'project'      => $vProjectName,
                'duration'     => $fDuration,
                'jira_time'    => $this->getJiraTime($fDuration, false),
                'date'         => $vDate,
                /**
                 * default time zone of the app is Adelaide
                 * config/app.php:67
                 */
                'actual_start' => date('c', $iStartStamp ),
                'unix_stamp' => $iStartStamp,
                'stop'         => date('c', strtotime($aTime['stop'])),
            ];
            $aRow['jira_start'] =$this->getJiraHelper()->jiraRestDateFormat($aRow['actual_start']);
            if (!empty($aMeta['jira_entry'])) {
                $aRow['jira_entry'] = $aMeta['jira_entry'];
            }
            $aReturn[$vProjectName][$iTicket][$vDate][] = $aRow;
        }
        $aReturn = $this->mergeNonProjects($aReturn);
        return $aReturn;
    }
    public function getProjects()
    {
        return $this->oApiHelper->getProjects();
    }
    protected function getJiraHelper (){
        if (!$this->oJiraHelper){
            $this->oJiraHelper = resolve('\App\Toggl\JiraHelper');
        }
        return $this->oJiraHelper;
    }

    protected function mergeNonProjects($aEntries)
    {
        $aReturn = ['misc' => []];
        foreach ($aEntries as $vProject => $aProject) {
            $vProject = ($vProject == 'misc') ? 'misc_project' : $vProject;
            $aReturn[$vProject] = $aProject;
            if ((count($aProject) == 1) && (count(current($aProject)) == 1)) {
                $vDate = key(current($aProject));
                $vTicket = key($aProject);
                if (!$this->isTicket($vTicket)) {
                    unset($aReturn[$vProject]);
                    //all misc/project time entries will be grouped already under it, only once
                    if (!isset($aReturn['misc'][$vProject])) {
                        $aReturn['misc'][$vProject] = [];
                    }
                    if (!isset($aProject['misc'][$vProject][$vDate])) {
                        $aReturn['misc'][$vProject][$vDate] = [];
                    }
                    $aReturn['misc'][$vProject][$vDate] = $aProject[$vProject][$vDate];
                }
            }
        }
        //put misc at the end
        if (isset($aReturn['misc'])) {
            $aMisc = $aReturn['misc'];
            unset($aReturn['misc']);
            $aReturn['misc'] = $aMisc;
        }
        return $aReturn;
    }

    protected function getMetaInfo($vDescription)
    {
        $aMeta = [];
        $aParts = explode(' ', $vDescription);
        $aParts = array_map('trim', $aParts);
        $aParts = array_filter($aParts);
        //re-index keys
        $aKeys = range(0, count($aParts) - 1);
        $aParts = array_combine($aKeys, $aParts);
        $aMeta['ticket'] = $aMeta['project'] = strtolower($aParts[0]) ?: 'no_project';
        if (count($aParts) > 2){
            array_splice($aParts,2);
        }
        //remove # from ticket number
        foreach ($aParts as $vPart) {
            if ($this->isTicket($vPart)) {
                $aMeta['ticket'] = $vPart;
                break;
            }
        }
        $vDescription = trim($vDescription);
        $vProject = trim($aMeta['project']);
        if ($vProject) {
            //remove project prefix from ticket description
            if (stripos($vDescription, $vProject) === 0) {
                $aMeta['jira_entry'] = trim(substr($vDescription, strlen($vProject)));
            }
        }
        return $aMeta;
    }

    protected function secondsToHours($fSeconds)
    {
        return round($fSeconds / 60 / 60, 2);
    }

    public function getJiraTime($fHours, $bPadding  )
    {
        $iHour = floor($fHours);
        $vHour = $iHour ? $iHour . 'h' : '';
        $iMinutes = round(($fHours - $iHour) * 60, 0);
        $vMinute = $iMinutes ? $iMinutes . 'm' : '';
        if (!$vHour && $bPadding){
            if ($iMinutes < 10) {
                $vMinute = " $vMinute";
            }
            return "   $vMinute";
        }
        return trim("$vHour $vMinute");
    }
}
