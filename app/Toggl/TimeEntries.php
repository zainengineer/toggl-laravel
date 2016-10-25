<?php
namespace App\Toggl;

class TimeEntries
{
    /** @var  ApiHelper */
    protected $oApiHelper;

    public function __construct(ApiHelper $oHelper)
    {
        $this->oApiHelper = $oHelper;
    }

    public function getEntriesByProject()
    {
        $aTimeEntries = $this->oApiHelper->getTimeEntries();
        $aReturn = [];
        foreach ($aTimeEntries as $aTime) {
            if (!isset($aTime['description'])) {
                continue;
            }
            $vDescription = $aTime['description'];
            $aMeta = $this->getMetaInfo($vDescription);
            $vProjectName = $aMeta['project'];
            $iTicket = $aMeta['ticket'];
            $vDate = date('d-M-Y', strtotime($aTime['start']));
            $fDuration = round($aTime['duration'] / 60 / 60, 2);

            $aReturn[$vProjectName][$iTicket][$vDate][] = [
                'description' => $vDescription,
                'ticket'      => $iTicket,
                'project'     => $vProjectName,
                'duration'    => $fDuration,
                'date'        => $vDate,
                'actual_start'        => $aTime['start'],
            ];
        }
        $aReturn = $this->mergeNonProjects($aReturn);
        return $aReturn;
    }

    protected function mergeNonProjects($aEntries)
    {
        $aReturn = ['misc' => []];
        foreach ($aEntries as $vProject => $aProject) {
            $vProject = ($vProject == 'misc') ? 'misc_project' : $vProject;
            $aReturn[$vProject] = $aProject;
            if ((count($aProject) == 1) && (count(current($aProject)) == 1 )) {
                $vDate = key(current($aProject));
                $iTicket = key($aProject);
                if (!is_numeric($iTicket)) {
                    unset($aReturn[$vProject]);
                    //all misc/project time entries will be grouped already under it, only once
                    if (!isset($aReturn['misc'][$vProject])){
                        $aReturn['misc'][$vProject] = [];
                    }
                    if (!isset($aProject['misc'][$vProject][$vDate])){
                        $aReturn['misc'][$vProject][$vDate] = [];
                    }
                    $aReturn['misc'][$vProject][$vDate] = $aProject[$vProject][$vDate];
                }
            }
        }
        //put misc at the end
        if (isset($aReturn['misc'])) {
            $aMisc = $aReturn['misc'];
            unset($aReturn['misc']);
            $aReturn['misc'] = $aMisc;
        }
        return $aReturn;
    }

    protected function getMetaInfo($vDescription)
    {
        $aMeta = [];
        $aParts = explode(' ', $vDescription);
        $aMeta['ticket'] = $aMeta['project'] = strtolower($aParts[0]) ?: 'no_project';
        foreach ($aParts as $vPart) {
            if (strpos($vPart, '#') === 0) {
                $aMeta['ticket'] = (int)trim($vPart, '#');
                break;
            }
        }
        return $aMeta;
    }
}