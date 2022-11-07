<?php

namespace BlackPanda\AnsibleMasterApi;

use Illuminate\Support\ServiceProvider;

class AnsibleMasterServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . 'config/ansiblemaster.php' => config_path('ansiblemaster.php'),
        ]);
    }
    /**
     * @return void
     */
    public function register()
    {
        /*
         * register facade
         */
        $this->app->bind('AnsibleMaster',function (){
            return new AnsibleMaster();
        });
    }

}
