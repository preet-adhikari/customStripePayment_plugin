<?php

namespace Inc\Base;

class BaseController
{
    //This is a controller to declare the global variables necessary
    protected string $pluginPath;
    protected string $pluginURL;
    protected  string $pluginBaseName;

    public function __construct() {
        $this->pluginPath = plugin_dir_path( dirname( __FILE__ , 2 ) );
        $this->pluginURL = plugin_dir_url( dirname( __FILE__  , 2 ) );
        $this->pluginBaseName = plugin_basename( dirname( __FILE__ , 3 ) ) . '/customStripePayment_plugin.php';
    }

}