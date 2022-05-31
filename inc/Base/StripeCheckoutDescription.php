<?php

namespace Inc\Base;

class StripeCheckoutDescription extends BaseController
{
    public function stripe_description_fields( $description , $payment_id )
    {
        if ( 'custom_stripe_payment' !== $payment_id ) {
            return $description;
        }
        ob_start();
        echo '<img src="'.$this->pluginURL.'/assets/static/icon.svg">';

//        woocommerce_form_field( 'credit_card_number', [
//           'type' => 'number' ,
//           'label' => __( 'Credit Card number' , 'customStripePayment_plugin' ),
//            'class' => [ 'form-row' , 'form-row-wide' ] ,
//            'required' => true ,
//        ]);
//
//        woocommerce_form_field('card_expiry_date' , [
//           'type' => 'date',
//           'label' => __( 'Expiry Date' , 'customStripePayment_plugin' ),
//           'class' => [ 'form-row' , 'form-row-wide' ],
//            'required' => true ,
//        ]);
//        woocommerce_form_field('card_cvc' , [
//           'type' => 'number',
//           'label' => __( 'Expiry Date' , 'customStripePayment_plugin' ),
//           'class' => [ 'form-row' , 'form-row-wide' ],
//            'required' => true ,
//        ]);


        $description .= ob_get_clean();

        return $description;
//        require "$this->pluginPath/templates/testOperation.php";

    }

    public function stripe_description_fields_validation() {
        if ( empty( $_POST['custom_stripe_payment-card-number'] ) ){
            wc_add_notice( 'Please enter a number that is to be billed', 'error' );
        }
        if ( ! isset( $_POST['custom_stripe_payment-card-cvc'] ) ) {
            wc_add_notice( 'Please enter CVC', 'error' );
        }
        if ( ! isset( $_POST['custom_stripe_payment-card-expiry'] ) ) {
            wc_add_notice( 'Please enter card expiry date', 'error' );
        }

    }

    public function generateDescriptionFields()
    {
        //Generate description fields for Stripe Checkout
        add_filter( 'woocommerce_gateway_description', [ $this , 'stripe_description_fields' ] , 20, 2 );
        add_action( 'woocommerce_checkout_process' , [ $this , 'stripe_description_fields_validation' ] );
//        var_dump($_POST['custom_stripe_payment-card-expiry']);

        //Checkout description fields

    }




}