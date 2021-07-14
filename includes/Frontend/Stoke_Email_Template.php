<?php

namespace WOO\STOCK\NOTIFIER\Frontend;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * Stock Email Template class
 */
class Stoke_Email_Template {
    /**
     * Load Email Template
     *
     * @return void
     */
    public function wssn_stock_waitlist_template() {
        global $product;
        $stock_status = $product->get_stock_status();

        error_log( print_r( $stock_status, true ) );
        
        if( "outofstock" === $stock_status ) {
            wp_enqueue_script( 'woo-stock-email-script' );
            include __DIR__ . '/View/stock-email-template.php';
        }
    }

    /**
     * Handle the form
     *
     * @return void
     */
    public function wssn_stock_submit_email_form_handle() {
        //get form data
        $nonce      = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
        $email      = isset( $_REQUEST['email'] ) ? sanitize_text_field( $_REQUEST['email'] ) : '';
        $product_id = isset( $_REQUEST['product_id'] ) ? sanitize_text_field( $_REQUEST['product_id'] ) : '';

        //verify nonce
        if ( ! wp_verify_nonce( $nonce, 'wssn-stock-notifier' ) ) {
            wp_send_json_success( array( 'message' => __( 'Are You Cheating?', 'wssn' ) ) );
            return false;
        }
        
        //verify email
        if( empty( $email ) ) {
            wp_send_json_success( array( 'message' => __( 'Please Enter a email address', 'wssn' ) ) );
            return false;
        }
        
        //verify valid product id
        if( empty( $product_id ) ) {
            return false;
        }

        //user data
        $args = [
            'subscriber_email' => $email,
            'product_id'       => $product_id,
            'mail_status'      => '',
            'created_at'       => date("Y-m-d h:i:s")
        ];

        //Search for duplicate email addresses
        $result = wssn_stock_duplicate_email_check( $args );

        //if found duplicate
        if( ! empty( $result ) ) {
            // duplicate error message
            wp_send_json_success( array( 'message' => __( 'Thanks! You already join waitlist.', 'wssn' ) ) );
            return;
        }

        //insert new subscriber email
        $insert_id = wssn_stock_subscriber_email_insert( $args );

        if ( is_wp_error( $insert_id ) ) {
            // error message
            wp_send_json_success( array( 'message' => __( 'Someting went worng!', 'wssn' ) ) );
            return;
        }

        //success messgae after insert data
        wp_send_json_success( array( 'message' => __( 'You have successfully subscribed, we will inform you when this product back in stock', 'wssn' ) ) );
    }
}

