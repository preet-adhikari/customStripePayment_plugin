<?php


namespace Inc\Pages;

use Inc\Base\BaseController;

/**
 * @package CustomStripePaymentPlugin
 */



use Inc\Api\Callbacks\AdminCallbacks;
use \Inc\Api\SettingsApi;
//use \Inc\Base\BaseController;

class Admin extends BaseController
{
    public $settings;
    public $callbacks;

    public array $pages = [];
    public array $subPages = [];

    public function register(){
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();

        $this->setPages();

        $this->setSubPages();

        $this->settings->addPages( $this->pages )->withSubPage('Dashboard' )->addSubPages( $this->subPages )->register();
    }




    public function setPages() {
        $this->pages = [
            [
                'page_title' => 'Stripe Payment Plugin',
                'menu_title' => 'StripePayment',
                'capability' => 'manage_options',
                'menu_slug' => 'customStripePayment_plugin',
                'callback' => [ $this->callbacks , 'Dashboard' ],
                'icon_url' => 'dashicons-money-alt',
                'position' => 56
            ]
        ];
    }

    public function setSubPages() {
        $this->subPages = [
            [
                'parent_slug' => 'customStripePayment_plugin',
                'page_title' => 'Preferences',
                'menu_title' => 'Prefs',
                'capability' => 'manage_options',
                'menu_slug' => 'customStripePayment_prefs',
                'callback' => function() { echo "<h1>Preferences</h1>"; }
            ],
            [
                'parent_slug' => 'customStripePayment_plugin',
                'page_title' => 'Operation',
                'menu_title' => 'Operation',
                'capability' => 'manage_options',
                'menu_slug' => 'customStripePayment_Operation',
                'callback' => [ $this->callbacks , 'testOperation' ]
            ]
        ];
    }

}

