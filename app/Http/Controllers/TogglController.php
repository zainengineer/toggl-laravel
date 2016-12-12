<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;
use Illuminate\Pagination\Paginator;

class TogglController extends Controller
{
    protected $iDefaultMondayToleranceHours = 72;

    /**
     * @return Request
     */
    protected function getRequest()
    {
        /** @var Request $oRequest */
        $oRequest = resolve('Illuminate\Http\Request');
        return $oRequest;
    }

    public function lastWeek(Toggl\TimeEntries $oHelper, Request $oRequest)
    {
        try {
            $aTimeEntries = $oHelper->getEntriesByProject();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 403) {
                return redirect()->route('askApiToken');
            }
            throw $e;
        }
//        echo "<pre>";
//        print_r($aTimeEntries);
        $this->displayTimeEntries($aTimeEntries, $oHelper);
    }

    protected function topLinks()
    {

    }

    protected function getCacheToggleLink()
    {
        $oRequest = $this->getRequest();
        //TODO: find a way to get request parameter from Laravel Objects
        $aParam = $_GET;
        $bEnableCache = empty($aParam['enable_cache']);
        $aParam['enable_cache'] = $bEnableCache ? 1 : 0;
        $vUrl = http_build_query($aParam);
        $vUrl = $oRequest->getPathInfo() . "?$vUrl";
        $vTitle = $bEnableCache ? "Enable Cache" : "Disable Cache";
        $vLink = "<a href='$vUrl'>$vTitle</a>";
        return $vLink;

    }

    protected function displayTimeEntries($aPersonInfo, Toggl\TimeEntries $oHelper)
    {
        $vLink = $this->getCacheToggleLink();
        echo "$vLink<br/>\n";
        $aDayGrandTotal = [];
        $fWeekGrandTotal = 0;
        $fClosestMonday = $this->getClosestMondayStamp();
        $fClosestSunday = $this->getClosestSundayStamp();
        echo "<pre>\n";
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
                        echo "$vProjectName\n";
                        $bShowProject = false;

                    }
                    if ($bShowTicket) {
                        echo "  $vTicket\n";
                        $bShowTicket = false;
                    }
                    if (!isset($aDayGrandTotal[$vDate])) {
                        $aDayGrandTotal[$vDate] = 0;
                    }
                    $fTicketTotal = 0;
                    $fDuration = 0;
                    echo "\n    $vDate\n";
                    foreach ($aTimeEntries as $aSingleTimeEntry) {
                        $fDuration = $aSingleTimeEntry['duration'];
                        $fTicketTotal += $fDuration;
                        $aDayGrandTotal[$vDate] += $fDuration;
                        $fWeekGrandTotal += $fDuration;
                        $vHours = ($fDuration >= 1) ? floor($fDuration) . '' : '0';
                        $vMinute = round(($fDuration - floor($fDuration)) * 60, 0);
                        $vMinute = str_pad($vMinute, 2, "0", STR_PAD_LEFT);
                        echo "      $fDuration\t{$vHours}:{$vMinute}\t{$aSingleTimeEntry['description']} \n";
                    }
                    if (abs($fDuration - $fTicketTotal) > 0.0001) {
                        $vJiraTime = $oHelper->getJiraTime($fTicketTotal);
                        echo "    $fTicketTotal\t$vJiraTime\n";
                    }
                    echo "\n";
                }
                echo "\n";
            }
            echo "\n";
        }
        ksort($aDayGrandTotal);
        $vContents = ob_get_clean();
        echo trim($vContents);
        echo "\n\n";
        foreach ($aDayGrandTotal as $vDate => $fDuration) {
            $vDuration = number_format($fDuration, 2);
            echo "$vDate\t\t$vDuration\n";
        }
        echo "\nWeek GrandTotal\t\t" . $fWeekGrandTotal;
    }

    public function getClosestMondayStamp()
    {
        if (!empty($_GET['today'])) {
            return date('Y-m-d');
        }
        $fLastMonday = strtotime('last Monday');
        $fDiff = abs($fLastMonday - time()) / 60 / 60;
        if ($fDiff < $this->getMondayTolerance()) {
            $fLastMonday = $fLastMonday - (7 * 24 * 60 * 60);
        }
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
        $fReturn =$fMonday + (7 * 24 * 60 * 60) -1;
        $vDate = date('c',$fReturn);
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

    protected function getMondayTolerance()
    {
        return isset($_GET['hours_tolerance']) ? $_GET['hours_tolerance'] : $this->iDefaultMondayToleranceHours;
    }

    protected function getSundayTolerance()
    {
        return isset($_GET['sunday_tolerance']) ? $_GET['sunday_tolerance'] : $this->iDefaultMondayToleranceHours;
    }
}