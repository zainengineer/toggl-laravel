<?php
namespace App\Toggl;

class ViewHelper
{
    /** @var TimeEntries  */
    protected $oHelper;
    public function __construct(TimeEntries $oHelper)
    {
        $this->oHelper = $oHelper;
    }

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
            <a class="btn btn-mini jira-send-button"
               data-time-entry="<?php echo $vDataHtml; ?>" href="javascript:void(0)">Jira<i class="fa fa-clock-o" aria-hidden="true"></i></a>
            <?php

            $vLink = ob_get_clean();
        }
        else{
            $vLink = "<span class='link-container'><a class='post-data-send' href='javascript:void(0)' data-post='$vDataHtml'>send</a> {$this->getUpdateTask()}</span>";
        }
        return $vLink;
    }
    protected function getUpdateTask()
    {
        return '<a class="btn btn-mini update-task" href="javascript:void(0)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
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
    public function getTogglEntry($fDuration,$aSingleTimeEntry)
    {
        $vJiraSingleTime = $this->oHelper->getJiraTime($fDuration,true);
        return "<div class='toggl-entry row'>
                    <div class='col'>$fDuration</div>
                    <div class='col'>$vJiraSingleTime</div>
                    <div class='col-8'>{$aSingleTimeEntry['description']}</div>
                    <div class='col'>{$this->getTimeLink($aSingleTimeEntry)}</div>
                </div>";
    }
}
