<?php
namespace App\Toggl;

use Illuminate\Http\Request;

class ViewHelper
{
    public function getTimeLink($aSingleTimeEntry)
    {
        $vData = json_encode($aSingleTimeEntry);
        $vDataHtml = htmlspecialchars($vData, ENT_QUOTES);
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
    public function getTicketHeader($vTicket)
    {
        ob_start();
        echo $vTicket . "\n";
        ?>
        <div class="work-log-container <?php echo  htmlentities($vTicket) ; ?>"></div>
        <?php
        $vTicketHeader = ob_get_clean();
        return $vTicketHeader;
    }
}
