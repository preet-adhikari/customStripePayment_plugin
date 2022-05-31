<?php

/**
 * @package CustomStripePaymentPlugin
 */

namespace Inc;



final class Init {

    //List the important classes here
    public static function get_services() {
        return[
            Pages\Admin::class,
            Base\Enqueue::class,
        ];

    }

    //Register the classes
    public static function register_services() {
        foreach (self::get_services() as $class){
            $service = self::instantiate( $class );
            if ( method_exists( $service , 'register' ) ) {
                $service->register();
            }
        }
    }

    private static function instantiate( $class ) {
        return new $class();
    }

}