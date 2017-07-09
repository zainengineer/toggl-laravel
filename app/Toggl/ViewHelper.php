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

            $vLink = ob_get_clean() . " {$this->getRefreshTask($aSingleTimeEntry)}";
        }
        else{
            $vLink = "<span class='link-container'><a class='post-data-send' href='javascript:void(0)' data-post='$vDataHtml'>send</a> {$this->getRefreshTask($aSingleTimeEntry)}</span>";
        }
        return $vLink;
    }
    protected function getUpdateTask()
    {
        return '<a class="btn btn-mini update-task" href="javascript:void(0)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
    }
    protected function getRefreshTask($aSingleTimeEntry)
    {
        $vTicketEntity = htmlentities($aSingleTimeEntry['ticket']);
        $vProjectEntity = htmlentities($aSingleTimeEntry['project']);
        return "<a class='btn btn-mini refresh-task' href='javascript:void(0)'
data-ticket='$vTicketEntity' data-project = '$vProjectEntity'><i class='fa fa-refresh' aria-hidden='true'></i></a>";
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
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title ticket-title <?php echo "$vTicketEntity $vProjectEntity"; ?>"><?php echo $vTicketEntity; ?></span>
                <span class="work-log-container <?php echo  "$vTicketEntity $vProjectEntity"; ?>" data-ticket="<?php echo $vTicketEntity; ?>" data-project = "<?php echo $vProjectEntity; ?>"></span>
            </div>
        </div>
        <?php
        $vTicketHeader = ob_get_clean();
        return $vTicketHeader;
    }
    public function getTogglEntry($aSingleTimeEntry)
    {
        $fDuration = $aSingleTimeEntry['duration'];
//        $aSingleTimeEntry['unix_stamp'] = strtotime($aSingleTimeEntry[''])
        $vJiraSingleTime = $this->oHelper->getJiraTime($fDuration,true);
        return "<div class='toggl-entry row'>
                    <div class='col'>$fDuration</div>
                    <div class='col'>$vJiraSingleTime</div>
                    <div class='col-8'>{$aSingleTimeEntry['description']}</div>
                    <div class='col'>{$this->getTimeLink($aSingleTimeEntry)}</div>
                </div>";
    }

    public function getAllEntries($aDisplayEntries)
    {
        $output = '';
        foreach ($aDisplayEntries as $aProject => $aProjectInfo) {
            ;
            if (!($aTicketEntries = $aProjectInfo['tickets'])) {
                continue;
            }
            $output .= $aProjectInfo['meta']['project'] . "\n";
            foreach ($aTicketEntries as $vTicket => $aTicketInfo) {
                if (!($aDateEntries = $aTicketInfo['date_entries'])) {
                    continue;
                }
                $vTicketBlock = '';
                foreach ($aDateEntries as $aDateInfo)
                {
                    if (!($aTimeEntries = $aDateInfo['time_entries'])) {
                        continue;
                    }
                    $vTicketBlock .= $aDateInfo['meta']['date'];
                    foreach ($aTimeEntries as $vTimeEntry) {
                        $vTicketBlock.= $vTimeEntry;
                    }
                    if (isset($aDateInfo['meta']['total'])){
                        $vTicketBlock.= $aDateInfo['meta']['total'];
                    }
                }
                ob_start();
                echo $aTicketInfo['meta']['ticket'];
                ?>
                <div class="card blue-grey lighten-4">
                    <div class="card-content white-text">
<!--                        meta tag finished-->
                        <?php echo $vTicketBlock; ?>
                    </div>
                </div>
                <?php
                $vTicketBlock = ob_get_clean();
                $output .= $vTicketBlock;
            }
        }
        return $output;
    }
}
