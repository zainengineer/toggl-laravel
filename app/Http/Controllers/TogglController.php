<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;

class TogglController extends Controller
{
    public function test(Toggl\TimeEntries $oHelper, Request $oRequest)
    {
        $aTimeEntries = $oHelper->getEntriesByProject();
        echo "<pre>";
        print_r($aTimeEntries);
    }
}