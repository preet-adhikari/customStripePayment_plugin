<?php
/**
 * @package CustomStripePaymentPlugin
 */

namespace Inc\Base;

use Inc\Base\BaseController;

class Enqueue extends BaseController
{
    public function register()
    {
        add_action( 'admin_enqueue_scripts', [ $this , 'enqueue'] );
    }

    public function enqueue()
    {
        wp_enqueue_style( 'mainPluginStyle', $this->pluginURL . '/assets/mainStyle.css', __FILE__ );
        wp_enqueue_script( 'mainPluginScript', $this->pluginURL . '/assets/mainScript.js', __FILE__ );
    }
}