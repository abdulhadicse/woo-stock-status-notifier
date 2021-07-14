<?php

namespace WOO\STOCK\NOTIFIER;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * The admin class
 */
class Admin {
   /**
     * Initialize the class
     */
    function __construct() {
        $this->dispatch_actions();
        new Admin\Menu();
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions() {
        add_action( 'wp_ajax_nopriv_form_handle', [ wssn_stock_notifier()->stock,'wssn_stock_submit_email_form_handle' ] );
        add_action( 'wp_ajax_form_handle', [ wssn_stock_notifier()->stock,'wssn_stock_submit_email_form_handle' ] );

    }
}
