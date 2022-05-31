<?php

/**
 * @package CustomStripePaymentPlugin
 */

namespace Inc\Base;

class SettingLinks extends BaseController
{
    public function register() {
        add_filter( "plugin_action_links_$this->pluginBaseName" , [$this , 'settings_link'] );
    }

    public function settings_link ( $links )
    {
        $settings_link = '<a href="admin.php?page=customStripePayment_plugin">Settings</a>';
        array_push( $links, $settings_link);
        return $links;
    }
}
