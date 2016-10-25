<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Toggl;

class TogglController extends Controller
{
    public function test(Toggl\ApiHelper $oHelper, Request $oRequest)
    {
        $aTimeEntries = $oHelper->getTimeEntries();
        print_r($aTimeEntries);
    }
}