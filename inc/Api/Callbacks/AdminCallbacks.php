<?php

namespace Inc\Api\Callbacks;

use Automattic\WooCommerce\Client;
use Inc\Base\BaseController;
use Stripe\StripeClient;

class AdminCallbacks extends BaseController
{
    public function Dashboard() {
        require_once "$this->pluginPath/templates/main_dashboard.php";
    }

    public function testOperation() {
        $stripe = new StripeClient(
            'sk_test_51JQUhrJ20uYlh3SGK8zCvucvZI3Ia2ZiPOikbZxU0MOODwvRyAdvaGbSqEmQF6ou5o2DpVyhPGEE9DXOWpNIKhWO007pCPxCHv'
        );
        $customer = $stripe->charges->create([
           'amount' => 2000 ,
           'currency' => 'usd' ,
           'source' => 'tok_visa' ,
           'description' => 'My Test Charge for the Stripe API' ,
        ]);
        echo $customer->getLastResponse()->headers["Request-Id"];

//        var_dump($customer);

    }
}