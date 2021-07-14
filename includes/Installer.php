<?php

namespace WOO\STOCK\NOTIFIER;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * Installer class
 */
class Installer {
    /**
     * Run the installer
     *
     * @return void
     */
    public function do_install() {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Add time and version on DB
     */
    public function add_version() {
        $installed = get_option( 'wssn_stock_notifier_installed' );

        if ( ! $installed ) {
            update_option( 'wssn_stock_notifier_installed', time() );
        }

        update_option( 'wssn_stock_notifier_version', WSSN_STOCK_NOTIFIER_VERSION );

        //wp corn 
        if ( ! wp_next_scheduled( 'wssn_stock_notifier_email_send' ) ) {
            wp_schedule_event( time(), 'five_seconds', 'wssn_stock_notifier_email_send' );
            error_log( print_r( 'cron create', true ) );
        }
    }

    /**
     * Create database tables
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $schema = "CREATE TABLE {$wpdb->prefix}wssn_stock_notifier (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            subscriber_email varchar(100) NOT NULL default '',
            product_id bigint(20) NOT NULL,
            mail_status varchar(10) NOT NULL default '',
            created_at datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (id)
          ) $charset_collate;";
          
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $schema );
    }
}