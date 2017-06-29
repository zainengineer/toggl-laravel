<?php
namespace App\Toggl;

use Illuminate\Http\Request;

class ViewHelper
{
    public function isJiraTicket($vTicket)
    {
        //ticket is just ticket number not full description
        return strpos($vTicket, '-');
    }
    public function getTimeLink($aSingleTimeEntry)
    {
        $vData = json_encode($aSingleTimeEntry);
        $vDataHtml = htmlspecialchars($vData, ENT_QUOTES);
        if ($this->isJiraTicket($aSingleTimeEntry['ticket'])) {
            ob_start();
            ?>
            <a class="btn btn-mini jira-send-button clip-board-trigger" data-clipboard-text="nothing yet"
               data-time-entry="<?php echo $vDataHtml; ?>" >
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
    public function getTicketHeader($vProject,$vTicket)
    {
        if (!$this->isJiraTicket($vTicket)){
            return "<pre>$vTicket\n</pre>";
        }
        ob_start();
        $vTicketEntity = htmlentities($vTicket);
        $vProjectEntity = htmlentities($vProject);
        ?>
        <span class="ticket-title <?php echo "$vTicketEntity $vProjectEntity"; ?>"><?php echo $vTicketEntity; ?></span>
        <span class="work-log-container <?php echo  "$vTicketEntity $vProjectEntity"; ?>" data-ticket="<?php echo $vTicketEntity; ?>" data-project = "<?php echo $vProjectEntity; ?>"></span>
        <?php
        $vTicketHeader = ob_get_clean();
        return $vTicketHeader;
    }
}
