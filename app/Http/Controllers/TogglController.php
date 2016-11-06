<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;

class TogglController extends Controller
{
    protected $iDefaultMondayToleranceHours = 72;

    public function lastWeek(Toggl\TimeEntries $oHelper, Request $oRequest)
    {
        try {
            $aTimeEntries = $oHelper->getEntriesByProject();
        }catch (\Guzzle\Http\Exception\BadResponseException $e){
            if ($e->getResponse()->getStatusCode() == 403){
                return redirect()->route('askApiToken');
            }
            throw $e;
        }
//        echo "<pre>";
//        print_r($aTimeEntries);
        $this->displayTimeEntries($aTimeEntries, $oHelper);
    }

    protected function displayTimeEntries($aPersonInfo, Toggl\TimeEntries $oHelper)
    {
        $aDayGrandTotal = [];
        $fWeekGrandTotal = 0;
        $vClosestMonday = $this->getClosestMonday();
        $fClosestMonday = strtotime($vClosestMonday);
        echo "<pre>\n";
        ob_start();
        foreach ($aPersonInfo as $vProjectName => $aProjectInfo) {
            $bShowProject = true;
            foreach ($aProjectInfo as $ticket => $aTicketEntries) {
                $vTicket =  $oHelper->isTicket($ticket) ? $ticket : 'No Ticket';
                $bShowTicket = true;
                foreach ($aTicketEntries as $vDate => $aTimeEntries) {
                    if (strtotime($vDate) < $fClosestMonday) {
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
                        $vHours = ($fDuration >=1) ? floor($fDuration) . ''   : '0';
                        $vMinute = round(($fDuration - floor($fDuration)) * 60,0);
                        $vMinute = str_pad($vMinute, 2, "0", STR_PAD_LEFT);
                        echo "      $fDuration\t{$vHours}:{$vMinute}\t{$aSingleTimeEntry['description']} \n";
                    }
                    if (abs($fDuration - $fTicketTotal) > 0.0001) {
                        echo "    $fTicketTotal\n";
                    }
                    echo "\n";
                }
                echo "\n";
            }
            echo "\n";
        }
        $vContents = ob_get_clean();
        echo trim($vContents);
        echo "\n\n";
        foreach ($aDayGrandTotal as $vDate => $fDuration) {
            $vDuration = number_format($fDuration,2);
            echo "$vDate\t\t$vDuration\n";
        }
        echo "\nWeek GrandTotal\t\t" . $fWeekGrandTotal;
    }

    public function getClosestMonday()
    {
        if (!empty($_GET['today'])){
            return date('Y-m-d');
        }
        $fLastMonday = strtotime('last Monday');
        $fDiff = abs($fLastMonday - time()) / 60 / 60;
        if ($fDiff < $this->getMondayTolerance()) {
            $fLastMonday = $fLastMonday - (7 * 24 * 60 * 60);
        }
        return date('Y-m-d', $fLastMonday);
    }
    public function entry(Toggl\ApiHelper $oHelper, Request $oRequest,\Illuminate\Http\Response $oResponse)
    {
        $vToggleApi = $oRequest->input('toggl_api')? : $oRequest->cookie('toggl_api');
        $_ENV['TOGGL_API_KEY'] = $vToggleApi ? : @$_ENV['TOGGL_API_KEY'];
        if ($_ENV['TOGGL_API_KEY'] && $oHelper->isValidKey()){
            $oCookie = cookie('toggl_api', $vToggleApi, time() + (86400 * 30 * 24));
            $oResponse->cookie($oCookie);
            return redirect()->route('lastWeekRoute',[
                'enable_cache' => 1,
                'hours_tolerance' => 72,
                'today' => 0,
            ])->withCookie($oCookie);

        }
        return view('cookie')->render();
    }
    protected function getMondayTolerance()
    {
        return isset($_GET['hours_tolerance']) ? $_GET['hours_tolerance'] : $this->iDefaultMondayToleranceHours;
    }
}