<?php

namespace BlackPanda\AnsibleMasterApi;

use Illuminate\Support\Facades\Facade;

class AnsibleMasterFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'AnsibleMaster';
    }

}
