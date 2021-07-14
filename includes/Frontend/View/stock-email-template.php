<?php
/*
 * This template can be Shown email input field
 *
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit;
}

?>

<div>
    <form id="contact" action="" method="post" >
        <h2> <?php esc_html_e( 'Join Waitlist', 'wssn' ) ?> </h2>
        <p> <?php esc_html_e( 'We will inform you when the product arrives in stock. Please leave your valid email address below.', 'wssn' ) ?> </p>

        <input placeholder="Email Address" name="email" id="email" type="email" required >
        <?php wp_nonce_field( 'wssn-stock-notifier' ); ?>
        <input type="hidden" name="product_id" value="<?php echo wc_get_product()->get_id(); ?>" >
        <input type="hidden" name="action" value="form_handle" >

        <button type="submit" class=""> <?php esc_html_e( 'Email Me', 'wssn' ) ?> </button>
        <br>
        <span id="messgae"></span>
    </form>
</div>


