<?php
namespace App\Toggl;

use Illuminate\Http\Request;

class ViewHelper
{
    public function getTimeLink($aSingleTimeEntry)
    {
        $vData = json_encode($aSingleTimeEntry,ENT_QUOTES, 'UTF-8');
        $vDataHtml = htmlspecialchars($vData);
        if (strpos($aSingleTimeEntry['ticket'], '-')) {
            ob_start();
            ?>
            <a class="btn btn-mini jira-send-button clip-board-trigger" data-clipboard-text="nothing yet"
               data-time-entry="<?php echo $vDataHtml; ?>">
                Jira <i class="fa fa-clock-o" aria-hidden="true"></i>
            </a>
            <?php

            $vLink = ob_get_clean();
        }
        else{
            $vLink = "<a class='post-data-send' href='javascript:void(0)' data-post='$vDataHtml'>send</a>";
        }
        return $vLink;
    }
}
