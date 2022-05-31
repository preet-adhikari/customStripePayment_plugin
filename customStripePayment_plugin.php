<?php
/**
 * @package CustomStripePaymentPlugin
*/

/*
 * Plugin Name: Custom Stripe Payment Plugin
 * Plugin URI: https://theodetotheman,wordpress,com/s
 * Description: Trying to create a Wordpress Plugin  that uses Stripe to compute Woocommerce orders
 * Author: Preet Adhikari
 * Author URI: https://theodetotheman,wordpress,com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: customStripePayment_plugin
 * */

//Check if plugin exists
defined( 'ABSPATH' ) or die( 'Not Allowed' );

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}


use Inc\Base\Activate;
use Inc\Base\Deactivate;
use Inc\Base\StripeCheckoutDescription;

function activatePlugin() {
    Activate::activate();
}

function deactivatePlugin() {
    Deactivate::deactivate();
}

register_activation_hook( __FILE__ , 'activatePlugin' );
register_deactivation_hook( __FILE__ , 'deactivatePlugin');

if ( ! in_array( 'woocommerce/woocommerce.php' , apply_filters( 'active_plugins' , get_option( 'active_plugins' ) ) ) ) {
    return;
}


add_action('plugins_loaded', 'custom_Stripe_payment_init' );

function custom_Stripe_payment_init() {

    if ( class_exists('WC_Payment_Gateway' ) ) {
        class WCStripePayment extends WC_Payment_Gateway
        {

            public function __construct()
            {
                $this->id = 'custom_stripe_payment';
//                $this->icon = apply_filters(
//                    'custom_stripe_icon', plugins_url('assets/static/icon.svg' , __FILE__)
//                );
                $this->has_fields = false;
                $this->method_title = __('Custom Stripe Payment', 'customStripePayment_plugin');
                $this->method_description = __('A custom Stripe payment plugin for Woocommerce.', 'customStripePayment_plugin');
                $this->title = __('Custom Stripe Payment', 'custom_stripe_payment');


                $this->name = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->instructions = $this->get_option('instructions', $this->description);
                $this->apiKey = $this->get_option( 'apiKey' );
                $this->init_form_fields();
                $this->init_settings();

                $this->supports[] = 'default_credit_card_form';


                //Save hook for settings
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
                $stripeCheckoutDescription = new StripeCheckoutDescription;
                $stripeCheckoutDescription->generateDescriptionFields();
                add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

            }

            public function init_form_fields()
            {
                $this->form_fields = [
                    'enabled' => [
                        'title' => __('Enable/Disable', 'customStripePayment_plugin'),
                        'type' => 'checkbox',
                        'label' => __('Enable Stripe Payment', 'customStripePayment_plugin'),
                        'default' => 'yes'
                    ],
                    'name' => array(
                        'title' => __('Title', 'customStripePayment_plugin'),
                        'type' => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'customStripePayment_plugin'),
                        'default' => __('Cheque Payment', 'customStripePayment_plugin'),
                        'desc_tip' => true,
                    ),

                    'description' => array(
                        'title' => __('Payments Gateway Description', 'customStripePayment_plugin'),
                        'type' => 'textarea',
                        'default' => __('Please remit your payment to the shop to allow for the delivery to be made', 'customStripePayment_plugin'),
                        'desc_tip' => true,
                        'description' => __('Add a new title for the Noob Payments Gateway that customers will see when they are in the checkout page.', 'customStripePayment_plugin')
                    ),

                    'apiKey' => array(
                        'title' => __('API key', 'customStripePayment_plugin'),
                        'type' => 'password',
                        'desc_tip' => true,
                        'description' => __('Set your API key for stripe', 'customStripePayment_plugin')
                    ),

                ];
            }

            public function process_payment($order_id)
            {
                global $woocommerce;
                $order = new WC_Order($order_id);

                //Mark as on-hold
//                $order->update_status('on-hold', __('Awaiting stripe payment', 'customStripePayment_plugin'));
                if ($order->get_total() > 0){
                   $t = $this->mainPayment( $order );
                }
                if ($t === 0){
                    $order->payment_complete();
                    //Remove cart
                    $woocommerce->cart->empty_cart();
                    return [
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    ];
                }






            }

            public function mainPayment( $order ){
                global $woocommerce;
                $total = intval( $order->get_total() );

                //Get values of card item
                $expiryMonth = (int) substr(WC()->checkout()->get_value('custom_stripe_payment-card-expiry') , 0, 2);
                $expiryYear = '20' . substr(WC()->checkout()->get_value('custom_stripe_payment-card-expiry') , 5, 2);
                $cardVal = WC()->checkout()->get_value('custom_stripe_payment-card-number');
                $cvc = WC()->checkout()->get_value('custom_stripe_payment-card-cvc');
                settype($expiryYear , 'int');
                settype($expiryMonth , 'int');

                $stripe = new \Stripe\StripeClient($this->apiKey);
                try {
                    $card = $stripe->tokens->create([
                        'card' => [
                            'number' => $cardVal,
                            'exp_month' => $expiryMonth,
                            'exp_year' => $expiryYear,
                            'cvc' => $cvc
                        ]
                    ]);

                } catch (\Stripe\Exception\CardException $e) {
                    $body = $e->getJsonBody();
                    $err = $body['error'];

                    wc_add_notice(  $err['message'] . "\n", 'error');
                }

                if (isset($card)){
                    $token = $card->id;
                    //Create a charge
                    try{
                        $customer = $stripe->charges->create([
                            'amount' =>  $total * 100 ,
                            'currency' => 'usd' ,
                            'source' => $token ,
                            'description' => 'Payment for the order' ,
                        ]);
                    } catch (\Stripe\Exception\CardException $e) {
                        $body = $e->getJsonBody();
                        $err = $body['error'];
                        wc_add_notice(  $err['message'] . "\n", 'error');
                    }

                    if (isset($customer)){
                        return 0;
                    }

                }

            }

            public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
                if ( $order && 'custom_stripe_payment' === $order->get_payment_method() ) {
                    $status = 'completed';
                }
                return $status;
            }


        }

    }

}

add_filter( 'woocommerce_payment_gateways' , 'add_to_woo_custom_Stripe_payment_gateway' );

function add_to_woo_custom_Stripe_payment_gateway( $gateways ) {
    $gateways[] = 'WCStripePayment';
    return $gateways;
}



if ( class_exists( 'Inc\\Init' ) ) {
    Inc\Init::register_services();
}





