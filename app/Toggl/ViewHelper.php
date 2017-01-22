<?php
namespace App\Toggl;
use Illuminate\Http\Request;

class ViewHelper
{
    public function getTimeLink($aSingleTimeEntry)
    {
        $vData = json_encode($aSingleTimeEntry);
        $vDataHtml = htmlentities($vData);
        $aLink = "<a class='post-data-send' href='javascript:void(0)' data-post='$vDataHtml'>send</a>";
        return $aLink;
    }
}