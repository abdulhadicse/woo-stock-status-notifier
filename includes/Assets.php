<?php

namespace WOO\STOCK\NOTIFIER;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * Assets handler class
 */
class Assets {
    /**
     * Class constructor
     */
    function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    /**
     * All available scripts
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'woo-stock-email-script' => [
                'src'     => WSSN_STOCK_NOTIFIER_ASSETS . '/js/woo-stock-email-script.js',
                'version' => rand(),
                'deps'    => [ 'jquery']
            ]
        ];
    }

    /**
     * All available styles
     *
     * @return array
     */
    public function get_styles() {
        return [
            'woo-stock-email-style' => [
                'src'     => WSSN_STOCK_NOTIFIER_ASSETS . '/css/woo-stock-email-style.css',
                'version' => rand()
            ],
        ];
    }

    /**
     * Register scripts and styles
     *
     * @return void
     */
    public function register_assets() {
        $scripts = $this->get_scripts();
        $styles  = $this->get_styles();

        foreach ( $scripts as $handle => $script ) {
            $deps = isset( $script['deps'] ) ? $script['deps'] : false;
            wp_register_script( $handle, $script['src'], $deps, $script['version'], true );
        }

        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;
            wp_register_style( $handle, $style['src'], $deps, $style['version'] );
        }

        wp_localize_script( 'woo-stock-email-script', 'wssn', [
            'url'   => admin_url( 'admin-ajax.php' ),
        ]  );
    }
}