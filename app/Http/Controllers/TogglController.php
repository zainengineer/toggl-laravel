<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;

class TogglController extends Controller
{
    protected $iMondayToleranceHours = 72;

    public function lastWeek(Toggl\TimeEntries $oHelper, Request $oRequest)
    {
        $aTimeEntries = $oHelper->getEntriesByProject();
//        echo "<pre>";
//        print_r($aTimeEntries);
        $this->displayTimeEntries($aTimeEntries);
    }

    protected function displayTimeEntries($aPersonInfo)
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
                $vTicket = is_numeric($ticket) ? $ticket : 'No Ticket';
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
            echo "$vDate\t\t$fDuration\n";
        }
        echo "\nWeek GrandTotal\t\t" . $fWeekGrandTotal;
    }

    public function getClosestMonday()
    {
        $fLastMonday = strtotime('last Monday');
        $fDiff = abs($fLastMonday - time()) / 60 / 60;
        if ($fDiff < $this->iMondayToleranceHours) {
            $fLastMonday = $fLastMonday - (7 * 24 * 60 * 60);
        }
        return date('Y-m-d', $fLastMonday);
    }
}