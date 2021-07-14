<?php

/**
 * Insert a new Subscriber
 *
 * @param  array  $args
 *
 * @return int|WP_Error
 */
function wssn_stock_subscriber_email_insert( $args = [] ) {
    global $wpdb;

    $defaults = [
        'subscriber_email' => '',
        'product_id'       => '',
        'mail_status'      => '',
        'created_at'       => '',
    ];

    $data = wp_parse_args( $args, $defaults );

    if ( isset( $data['id'] ) ) {

        $id = $data['id'];
        unset( $data['id'] );
        unset( $data['subscriber_email'] );
        unset( $data['product_id'] );
        unset( $data['created_at'] );

        $updated = $wpdb->update(
            $wpdb->prefix . 'wssn_stock_notifier',
            $data,
            [ 'id' => $id ],
            [
                '%d',
            ],
            [ '%d' ]
        );

        return $updated;

    } else {
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'wssn_stock_notifier',
            $data,
            [
                '%s',
                '%d',
                '%s',
                '%s'
            ]
        );

        if ( ! $inserted ) {
            return new \WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'wedevs-academy' ) );
        }

        return $wpdb->insert_id;
    }
}

/**
 * Search for duplicate email addresses
 *
 * @param  array $args
 * 
 * @return int 
 */
function wssn_stock_duplicate_email_check( $args = [] ) {
    global $wpdb;

    $defaults = [
        'subscriber_email' => '',
        'product_id'       => '',
        'mail_status'      => '',
        'created_at'       => '',
    ];

    $data = wp_parse_args( $args, $defaults );

    return (int) $wpdb->get_var( 
                $wpdb->prepare ( "SELECT COUNT( subscriber_email ) 
                                  FROM {$wpdb->prefix}wssn_stock_notifier
                                  WHERE product_id = %d
                                  AND subscriber_email = %s
                                  GROUP BY subscriber_email
                                  HAVING ( COUNT( subscriber_email ) > 0 )", 
                                  $data['product_id'], 
                                  $data['subscriber_email'] 
                                ) 
                            );
}

/**
 * Get Waitlist Product Ids
 *
 * @return array unique product id
 */
function wssn_stock_waitlist_product_ids() {
    global $wpdb;

    return $wpdb->get_col( "SELECT distinct product_id 
                                  FROM {$wpdb->prefix}wssn_stock_notifier" 
                                );
}

/**
 * Get Waitlist Product Status
 * back in stock or not
 *
 * @return array $back_in_stock
 */
function wssn_get_product_stock_status( $product_id )  {
    //store back in stock product ids
    $back_in_stock = [];

    foreach( $product_id as $id ) {
        //get product details by id
        $product = wc_get_product( $id );
        $stock_status = $product->get_stock_status();

        if(  "instock" === $stock_status  ) {
            array_push( $back_in_stock, $id );
        }
    }

    return $back_in_stock;   
}

/**
 * Get Product Subscriber details
 *
 * @return array
 */
function wssn_get_product_subscriber_details( $product_id )  {
    global $wpdb;
    return $wpdb->get_results( 
        $wpdb->prepare ( "SELECT id, subscriber_email, mail_status 
                          FROM {$wpdb->prefix}wssn_stock_notifier
                          WHERE product_id = %d", 
                          $product_id
                        ) 
                    );
}

/**
 * Mail Body
 *
 * @param  int $product_id
 * 
 * @return string
 */
function wssn_stock_notifier_mail_body( $product_id ) {
    //get product info
    $product = wc_get_product( $product_id );
    $product_name = $product->get_title();
    $cart_link = $product->add_to_cart_url();
    $blog_url = get_bloginfo('url');

    //mail body message
    $message = "Hi, <br />Thanks for your patience and finally the wait is over! <br /> Your Subscribed Product {$product_name} is now back in stock! We only have a limited amount of stock, and this email is not a guarantee you'll get one, so hurry to be one of the lucky shoppers who do <br /> Add this product {$product_name} directly to your cart <a href='{$blog_url}/{$cart_link}'>Add to Cart</a>";

    return $message;
}