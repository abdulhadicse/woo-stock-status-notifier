<?php
/**
 * Woo Stock Status Notifier plugins where a product is back in stock this will notifies the subscribers by email.
 *
 * @package           Woo Stock Status Notifier
 * @author            Abdul Hadi <abdul.hadi.aust@gmail.com>
 * @copyright         2021 Abdul Hadi
 * @license           GPL-2.0-or-later
 *
 * @wordwppc_contributorsss-plugin
 * Plugin Name:       Woo Stock Status Notifier
 * Plugin URI:        https://github.com/abdulhadicse/woo-stock-status-notifier
 * Description:       This is a simple woocommerce plugin for notifier the subscriber by email when a product is back in the stock. 
 * Version:           1.0
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            Abdul Hadi
 * Author URI:        http://abdulhadi.info
 * Text Domain:       wssn
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Copyright (c) 2021 Abdul Hadi (email: abdul.hadi.aust@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for Wordwp_attendance_managerss
 * http://wordwppc_contributorsss.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

 // don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ .'/vendor/autoload.php';

if( ! class_exists( 'Woo_Stock_Status_Notifier' ) ) {
    /**
     * The main plugin class
     */
    final class Woo_Stock_Status_Notifier {
        /**
         * Holds various class instances
         *
         * @var array
         */
        private $container = [];

        /**
         * Plugin version
         *
         * @var string
         */
        const version = '1.0';

        /**
         * Class construcotr
         */
        private function __construct() {
            $this->define_constants();

            register_activation_hook( __FILE__, [ $this, 'activate' ] );
            register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
            add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
            add_filter( 'cron_schedules', [ $this, 'wssn_stock_mail_cron_interval' ] ); 
        }

        /**
         * Magic getter to bypass referencing objects
         *
         * @param string $prop
         *
         * @return Class Instance
         */
        public function __get( $prop ) {
            if ( array_key_exists( $prop, $this->container ) ) {
                return $this->container[ $prop ];
            }
        }

        /**
         * Instances all the classes
         *
         * @return void
         */
        public function init_classes() {
            $this->container['dashboard']       = new WOO\STOCK\NOTIFIER\Admin\Dashboard();
            $this->container['settings']        = new WOO\STOCK\NOTIFIER\Admin\Settings();
            $this->container['stock']           = new WOO\STOCK\NOTIFIER\Frontend\Stoke_Email_Template();
        }

        /**
         * Initializes a singleton instance
         *
         * @return \Woo_Stock_Status_Notifier
         */
        public static function init() {
            static $instance = false;

            if ( ! $instance ) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Initialize plugin for localization
         *
         * @return void
         */
        public function localization_setup() {
            load_plugin_textdomain( 'wssn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        /**
         * Initialize the plugin
         *
         * @return void
         */
        public function init_plugin() {
            // initialize the classes
            $this->init_classes();
            
            //load style
            new WOO\STOCK\NOTIFIER\Assets();
            
            if( is_admin() ) {
                new WOO\STOCK\NOTIFIER\Admin();
            } else {
                new WOO\STOCK\NOTIFIER\Frontend();
            }

            //wp corn
            add_action( 'wssn_stock_notifier_email_send', [ $this, 'run_corn' ] );
        }

        /**
         * Define the required plugin constants
         *
         * @return void
         */
        public function define_constants() {
            define( 'WSSN_STOCK_NOTIFIER_VERSION', self::version );
            define( 'WSSN_STOCK_NOTIFIER_FILE', __FILE__ );
            define( 'WSSN_STOCK_NOTIFIER_PATH', __DIR__ );
            define( 'WSSN_STOCK_NOTIFIER_URL', plugins_url( '', WSSN_STOCK_NOTIFIER_FILE ) );
            define( 'WSSN_STOCK_NOTIFIER_ASSETS', WSSN_STOCK_NOTIFIER_URL . '/assets' );
        }

        /**
         * Run cron
         */
        public function run_corn() {
            error_log( print_r( 'corn run', true ) );
            //get wait list product ids
            $product_ids = wssn_stock_waitlist_product_ids();
            
            if( is_array(  $product_ids ) ) {
                // get backin stock product ids
                $back_in_stock = wssn_get_product_stock_status( $product_ids );

                if( !empty(  $back_in_stock ) ) {
                    //get subscriber details
                    foreach( $back_in_stock as $id ) {
                        //get each subscriber
                        $subscriber = wssn_get_product_subscriber_details( $id );

                        if( is_array( $subscriber ) ) {
                            foreach( $subscriber as $key => $value ) {
                                // each user get mail only one time when the product is back in stock
                                if( empty( $value->mail_status ) ) {
                                    //get mail body
                                    $mail_body = wssn_stock_notifier_mail_body( $id );
                                    //mail subject
                                    $subject = __( 'Your waitlist product Back in Stock', 'wssn' );
                                    //send mail
                                    wp_mail( $value->subscriber_email, $subject,  $mail_body );

                                    //update mail status after send mail
                                    wssn_stock_subscriber_email_insert( [ 'id' => $value->id, 'mail_status' => 1 ] );
                                }
                            }
                        }   
                    }
                }
            }
        }

        /**
         * Corn schedule
         */
        public function wssn_stock_mail_cron_interval( $schedules ) { 
            $schedules['five_seconds'] = array(
                'interval' => 5,
                'display'  => esc_html__( 'Every Five Seconds' ), );
            return $schedules;
        }

        /**
         * Do stuff upon plugin activation
         *
         * @return void
         */
        public function activate() {
            $installer = new WOO\STOCK\NOTIFIER\Installer();
            $installer->do_install();
        }

        /**
         * Unschedule events plugin deactivation
         * 
         * @return void
         */
        public function deactivate() {
            $timestamp = wp_next_scheduled( 'wssn_stock_notifier_email_send' );
            wp_unschedule_event( $timestamp, 'wssn_stock_notifier_email_send' );
        }
    }
}



/**
 * Initializes the main plugin
 *
 * @return \Woo_Stock_Status_Notifier
 */
function wssn_stock_notifier() {
    return Woo_Stock_Status_Notifier::init();
}

// kick-off the plugin
wssn_stock_notifier();