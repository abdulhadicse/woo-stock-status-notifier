<?php

namespace WOO\STOCK\NOTIFIER;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * The forntend class
 */
class Frontend {
   /**
     * Initialize the class
     */
    function __construct() {
        $this->dispatch_actions();
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions() {
        add_action( 'woocommerce_single_product_summary', [ wssn_stock_notifier()->stock, 'wssn_stock_waitlist_template' ], 30 );
    }
}
