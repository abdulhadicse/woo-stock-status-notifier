<?php

namespace WOO\STOCK\NOTIFIER\Admin;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * Dashboard class
 */
class Dashboard {
    /**
     * Run the installer
     *
     * @return void
     */
    public function dashboard_page() {
        //load template
        echo 'Hello dashboard';
    }
}