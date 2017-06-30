<?php

namespace App\Toggl;
class ToolHelper
{
    protected $oRequest;
    public function __construct(\Illuminate\Http\Request $oRequest)
    {
        $this->oRequest = $oRequest;
    }
    public function cacheEnabled()
    {
        return $this->oRequest->has('enable_cache') ? $this->oRequest->get('enable_cache') : true;
    }
}