<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;
use Illuminate\Pagination\Paginator;
use Symfony\Component\Yaml\Yaml;


class TogglController extends Controller
{
    protected $iDefaultMondayToleranceHours = 72;
    protected $oTimeHelper;
    protected $iCachedLastMonday;
    protected $oViewHelper;
    /**
     * @var Request $oRequest
     */
    protected $oRequest;
    public function __construct(Toggl\ViewHelper $oViewHelper)
    {
        $this->oViewHelper = $oViewHelper;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (!$this->oRequest){
            $this->oRequest = resolve('Illuminate\Http\Request');
        }
        return $this->oRequest;
    }

    /**
     * @return Toggl\TimeEntries
     */
    protected function getTimeEntriesHelper()
    {
        if (!$this->oTimeHelper) {
            $this->oTimeHelper = resolve('\App\Toggl\TimeEntries');
        }
        return $this->oTimeHelper;
    }
    protected function isPjax()
    {
        $oRequest = $this->getRequest();
        return $oRequest->hasHeader('X-PJAX');
    }

    public function lastWeek(Request $oRequest)
    {
        try {
            if (!$this->isPjax()){
                echo view('domain_connect')->render();
            }
            $oHelper = $this->getTimeEntriesHelper();
            $aTimeEntries = $oHelper->getEntriesByProject();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 403) {
                return redirect()->route('askApiToken');
            }
            throw $e;
        }
//        echo "<pre>";
//        print_r($aTimeEntries);
        echo $this->manageDisplayEntriesDisplay($aTimeEntries, $oHelper);
    }
    protected function manageDisplayEntriesDisplay($aPersonInfo, Toggl\TimeEntries $oHelper)
    {
//        $vContents .= "<a href='" . $this->replaceGetParametersUrl([]) . "'>my test links</a>";
        $vContents = $this->displayTimeEntries($aPersonInfo, $oHelper);
        if (!$this->isPjax()){
            $vContents = "<div id='pjax-container'>\n$vContents</div>";
        }
        return $vContents;
    }

    protected function getCacheToggleLink()
    {
        $aParam = [];
        $bEnableCache = empty($_GET['enable_cache']);
        $aParam['enable_cache'] = $bEnableCache ? 1 : 0;
        $vTitle = $bEnableCache ? "Enable Cache" : "Disable Cache";
        $vLink = $this->replaceGetParametersLink($aParam, $vTitle);
        return $vLink;

    }

    protected function replaceGetParametersLink($aParamValue, $vTitle)
    {
        $vUrl = $this->replaceGetParametersUrl($aParamValue);
        $vLink = "<a href='$vUrl'>$vTitle</a>";
        return $vLink;
    }

    protected function replaceGetParametersUrl($aParamValue)
    {
        $oRequest = $this->getRequest();
        $aParam = $_GET;
        foreach ($aParamValue as $vParamName => $vParamValue) {
            if (is_null($vParamValue)){
                unset($aParam[$vParamName]);
            }
            //ignore params like _pjax
            elseif (strpos($vParamName,'_')===0){
                unset($aParam[$vParamName]);
            }
            else{
                $aParam[$vParamName] = $vParamValue;
            }
        }
        $vUrl = http_build_query($aParam);
        //TODO: find a way to get request parameter from Laravel Objects
        $vUrl = $oRequest->getPathInfo() . "?$vUrl";
        return $vUrl;
    }

    protected function getPreviousWeekLink()
    {
        $iPreviousWeek = $this->getPreviousMondayStamp();
        $vPreviousWeek = date($this->getDateFormatForRequest(), $iPreviousWeek);
        $aParam = ['start_date' => $vPreviousWeek];
        $iEndDate = $iPreviousWeek + 7 * 24 * 60 * 60;
        $aParam['end_date'] = null;
        if (($iEndDate) < time()) {
            $aParam['end_date'] = date($this->getDateFormatForRequest(), $iEndDate);
        }
        return $this->replaceGetParametersLink($aParam, 'Previous Week');
    }

    protected function getNextWeekLink()
    {
        $iNextWeek = $this->getNextMondayStamp();
        if ($iNextWeek) {
            $vPreviousWeek = date($this->getDateFormatForRequest(), $iNextWeek);
            $aParam = ['start_date' => $vPreviousWeek];
            $iEndDate = $iNextWeek + 7 * 24 * 60 * 60;
            $aParam['end_date'] = null;
            if ($iEndDate < time()){
                $aParam['end_date'] = date($this->getDateFormatForRequest(), $iEndDate);
            }
            return $this->replaceGetParametersLink($aParam, 'Next Week');
        }
    }

    protected function showHeaderLink()
    {
        echo '<div class="domain-connect">';
        $aLinks = [
            $this->getCacheToggleLink(),
            $this->getPreviousWeekLink(),
            $this->getNextWeekLink(),
        ];
        $vLinks = implode("<br/>\n", $aLinks);
        echo $vLinks;
        echo '</div>';

    }

    protected function displayTimeEntries($aPersonInfo, Toggl\TimeEntries $oHelper)
    {
        ob_start();
        $this->showHeaderLink();
        $aDayGrandTotal = [];
        $fWeekGrandTotal = 0;
        $fClosestMonday = $this->getClosestMondayStamp();
        $fClosestSunday = $this->getClosestSundayStamp();
        ob_start();
        foreach ($aPersonInfo as $vProjectName => $aProjectInfo) {
            $bShowProject = true;
            foreach ($aProjectInfo as $ticket => $aTicketEntries) {
                $vTicket = $oHelper->isTicket($ticket) ? $ticket : 'No Ticket';
                $bShowTicket = true;
                foreach ($aTicketEntries as $vDate => $aTimeEntries) {
                    if (strtotime($vDate) < $fClosestMonday) {
                        continue;
                    }
                    if ($fClosestSunday && (strtotime($vDate) > $fClosestSunday)) {
                        continue;
                    }
                    if ($bShowProject) {
                        echo "<div>$vProjectName</div>";
                        $bShowProject = false;

                    }
                    if ($bShowTicket) {
                        echo $this->oViewHelper->getTicketHeader($vProjectName,$vTicket);
                        $bShowTicket = false;
                    }
                    if (!isset($aDayGrandTotal[$vDate])) {
                        $aDayGrandTotal[$vDate] = 0;
                    }
                    $fTicketTotal = 0;
                    $fDuration = 0;
                    echo "<div>$vDate</div>";
                    foreach ($aTimeEntries as $aSingleTimeEntry) {
                        $fDuration = $aSingleTimeEntry['duration'];
                        $fTicketTotal += $fDuration;
                        $aDayGrandTotal[$vDate] += $fDuration;
                        $fWeekGrandTotal += $fDuration;
                        $vJiraSingleTime = $oHelper->getJiraTime($fDuration,true);
                        echo "<pre>      $fDuration\t$vJiraSingleTime \t{$aSingleTimeEntry['description']} {$this->oViewHelper->getTimeLink($aSingleTimeEntry)} \n</pre>";
                    }
                    if (abs($fDuration - $fTicketTotal) > 0.0001) {
                        $vJiraTime = $oHelper->getJiraTime($fTicketTotal,true);
                        $vTimeLink = "";
                        if (!empty($aSingleTimeEntry)){
                            $aTicketSum  = $aSingleTimeEntry;
                            $aTicketSum['duration'] = $fTicketTotal;
                            $aTicketSum['jira_time'] = $oHelper->getJiraTime($fTicketTotal,false);
                            $vTimeLink = $this->oViewHelper->getTimeLink($aTicketSum);
                        }
                        echo "<pre>    $fTicketTotal\t$vJiraTime $vTimeLink \n</pre>";
                    }
                }
            }
        }
        ksort($aDayGrandTotal);
        $vContents = ob_get_clean();
        echo trim($vContents);
        echo "<pre>\n\n";
        foreach ($aDayGrandTotal as $vDate => $fDuration) {
            $vDuration = number_format($fDuration, 2);
            $vDateFormatted = date('D d-M', strtotime($vDate));
            echo "$vDateFormatted\t\t$vDuration\n";
        }
        echo "\nWeek GrandTotal\t\t" . $fWeekGrandTotal;
        return ob_get_clean();
    }

    public function getPreviousMondayStamp()
    {
        $iClosestMonday = $this->getClosestMondayStamp();
        return $iClosestMonday - (7 * 24 * 60 * 60);
    }

    public function getNextMondayStamp()
    {
        $iClosestMonday = $this->getClosestMondayStamp();
        $iNextMonday = $iClosestMonday + (7 * 24 * 60 * 60);
        if ($iNextMonday < time()) {
            return $iNextMonday;
        }
    }

    public function getClosestMondayStamp()
    {
        if ($this->iCachedLastMonday) {
            return $this->iCachedLastMonday;
        }
        $vStartDate = $this->getTimeEntriesHelper()->getStartDate();
        if ($vStartDate) {
            $this->iCachedLastMonday = strtotime($vStartDate);
            return $this->iCachedLastMonday;
        }
        $fLastMonday = strtotime('last Monday');
        $fDiff = abs($fLastMonday - time()) / 60 / 60;
        if ($fDiff < $this->getMondayTolerance()) {
            $fLastMonday = $fLastMonday - (7 * 24 * 60 * 60);
        }
        $this->iCachedLastMonday = $fLastMonday;
        return $fLastMonday;
    }

    public function getClosestSundayStamp()
    {
        $fMonday = $this->getClosestMondayStamp();
        $vMonday = date('c', $fMonday);
        $fTime = time();
        //$fTime = strtotime('11-Dec-2016 12:00 PM');
        $fMondayAgo = ($fTime - $fMonday) / (24 * 60 * 60);
        if ($fMondayAgo < (7 + $this->getSundayTolerance())) {
            return null;
        }
        $fReturn = $fMonday + (7 * 24 * 60 * 60) - 1;
        $vDate = date('c', $fReturn);
        return $fReturn;
    }

    public function entry(Toggl\ApiHelper $oHelper, Request $oRequest, \Illuminate\Http\Response $oResponse)
    {
        $vToggleApi = $oRequest->input('toggl_api') ?: $oRequest->cookie('toggl_api');
        $_ENV['TOGGL_API_KEY'] = $vToggleApi ?: @$_ENV['TOGGL_API_KEY'];
        if ($_ENV['TOGGL_API_KEY'] && $oHelper->isValidKey()) {
            $oCookie = cookie('toggl_api', $vToggleApi, time() + (86400 * 30 * 24));
            $oResponse->cookie($oCookie);
            return redirect()->route('lastWeekRoute', [
                'enable_cache'     => 1,
                'hours_tolerance'  => 72,
                'sunday_tolerance' => 1,
                'today'            => 0,
            ])->withCookie($oCookie);

        }
        return view('cookie')->render();
    }

    public function jiraPost(Toggl\JiraHelper $oHelper, Request $oRequest, \Illuminate\Http\Response $oResponse)
    {
        $aTicketInfo = $oRequest->input('timer') ;
        $vIssue = $aTicketInfo['ticket'];
        $vStartedAt = $aTicketInfo['actual_start'];
        $vDuration = $aTicketInfo['duration'];
//        $vComment = $aTicketInfo['comment'];
        $vComment = empty($aTicketInfo['jira_entry']) ?  $aTicketInfo['description'] : $aTicketInfo['jira_entry'];
        $oHelper->addTimeLog($vIssue, $vStartedAt,$vDuration,$vComment );
        return response()->json([
            'status' => 'updated',
        ]);

    }

    protected function getMondayTolerance()
    {
        return isset($_GET['hours_tolerance']) ? $_GET['hours_tolerance'] : $this->iDefaultMondayToleranceHours;
    }

    protected function getDateFormatForRequest()
    {
        return 'Y-m-d';
    }

    protected function getSundayTolerance()
    {
        return isset($_GET['sunday_tolerance']) ? $_GET['sunday_tolerance'] : $this->iDefaultMondayToleranceHours;
    }
    public function testActionZ()
    {
        $oHelper = $this->getTimeEntriesHelper();
        $t = $oHelper->getProjects();
//        $vYaml = file_get_contents(public_path() . '/test.yaml');
//        $value = Yaml::parse($vYaml);
//        dd($value);
    }
}