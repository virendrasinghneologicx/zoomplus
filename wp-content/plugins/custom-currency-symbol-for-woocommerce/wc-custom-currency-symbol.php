<?php
/*
Plugin Name: Custom Currency Symbol for WooCommerce
Plugin URI: 
Description: Lets you set a custom symbol for the currently selected currency.
Author: Bill Robbins
Author URI: https://justabill.blog
WC tested up to: 3.6.4
WC requires at least: 3.6
Version: 1.0.0
Text Domain: wc-custom-currency-symbol
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


    function ijab_change_set_currency_symbol( $currency_symbol, $currency ) {

        $symbol = get_option( 'ijab_custom_currency_symbol' );

        if ( $symbol != '' ) {
            switch( $currency ) {
                case get_woocommerce_currency(): $currency_symbol = $symbol; break;
            }
        }

        return $currency_symbol;

    }
    add_filter('woocommerce_currency_symbol', 'ijab_change_set_currency_symbol', 10, 2);


    function ijab_custom_currency_symbol_setting( $settings ) {

        $updated_settings = array();

        foreach ( $settings as $section ) {

        if ( isset( $section['id'] ) && 'pricing_options' == $section['id'] &&
        isset( $section['type'] ) && 'sectionend' == $section['type'] ) {

        $updated_settings[] = array(
            'name'     => __( 'Custom Currency Symbol', 'wc-custom-currency-symbol' ),
            'desc_tip' => __( 'Enter a custom currency symbol here.  If empty, the default for the selected currency will be used instead.', 'wc-custom-currency-symbol' ),
            'id'       => 'ijab_custom_currency_symbol',
            'type'     => 'text',
            'css'      => 'width:50px;',
            'default'  => '',
            );
        }

        $updated_settings[] = $section;

        }

        return $updated_settings;

    }
    add_filter( 'woocommerce_general_settings', 'ijab_custom_currency_symbol_setting' );


    function ijab_custom_currency_symbol_language() {
        load_plugin_textdomain( 'wc-custom-currency-symbol', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }
    add_action( 'plugins_loaded', 'ijab_custom_currency_symbol_language' );