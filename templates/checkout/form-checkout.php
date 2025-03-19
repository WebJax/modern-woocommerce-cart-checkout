<?php
/**
 * Checkout Form
 */

defined('ABSPATH') || exit;

// Vis checkout meddelelser
do_action('woocommerce_before_checkout_form', $checkout);

// Hvis checkout registrering er aktiveret, men brugeren er ikke logget ind
if ($checkout->is_registration_enabled() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Du skal være logget ind for at handle.', 'woocommerce')));
    return;
}

// Hvis brugeren ikke er logget ind og ikke tillader gæsteordrer
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Du skal være logget ind for at handle.', 'woocommerce')));
    return;
}

?>

<form name="checkout" method="post" class="modern-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    <div class="modern-checkout-wrapper">
        <?php if ($checkout->get_checkout_fields()) : ?>
            <div class="modern-checkout-form-container">
                <h3><?php esc_html_e('Leveringsoplysninger', 'woocommerce'); ?></h3>
                
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                
                <div class="modern-customer-details">
                    <div class="modern-billing-details">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>
                    
                    <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                        <div class="modern-shipping-details">
                            <?php do_action('woocommerce_checkout_shipping'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                </div>
            </div>
            
            <div class="modern-checkout-sidebar">
                <div class="modern-checkout-summary">
                    <h3><?php esc_html_e('Ordreoversigt', 'woocommerce'); ?></h3>
                    
                    <div class="modern-order-summary">
                        <div class="modern-order-items">
                            <?php 
                            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                
                                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                    ?>
                                    <div class="modern-order-item">
                                        <div class="modern-order-item-image">
                                            <?php echo $_product->get_image(array(64, 64)); ?>
                                            <span class="modern-order-item-quantity"><?php echo $cart_item['quantity']; ?></span>
                                        </div>
                                        <div class="modern-order-item-details">
                                            <div class="modern-order-item-name"><?php echo $_product->get_title(); ?></div>
                                            <?php
                                            // Display variation data
                                            if ($_product->is_type('variation')) {
                                                echo wc_get_formatted_variation($_product);
                                            }
                                            // Display all cart item data
                                            if ($cart_item_data = wc_get_formatted_cart_item_data($cart_item)) {
                                                echo $cart_item_data;
                                            }
                                            ?>
                                        </div>
                                        <div class="modern-order-item-price">
                                            <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="modern-order-totals">
                            <div class="modern-order-subtotal">
                                <span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                                <span><?php wc_cart_totals_subtotal_html(); ?></span>
                            </div>
                            
                            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                                <div class="modern-order-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                                    <span><?php wc_cart_totals_coupon_label($coupon); ?></span>
                                    <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                                <div class="modern-order-shipping">
                                    <span class="morden-order-shipping-title"><?php esc_html_e('Forsendelse', 'woocommerce'); ?></span>
									<?php wc_cart_totals_shipping_html(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                                <div class="modern-order-fee fee">
                                    <span><?php echo esc_html($fee->name); ?></span>
                                    <span><?php wc_cart_totals_fee_html($fee); ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                                <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                                    <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                                        <div class="modern-order-tax tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                                            <span><?php echo esc_html($tax->label); ?></span>
                                            <span><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="modern-order-tax tax-total">
                                        <span><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
                                        <span><?php wc_cart_totals_taxes_total_html(); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="modern-order-total">
                                <span><?php esc_html_e('Total', 'woocommerce'); ?></span>
                                <span><?php wc_cart_totals_order_total_html(); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modern-payment-section">
                    <h3><?php esc_html_e('Betaling', 'woocommerce'); ?></h3>
                    <div id="payment" class="modern-payment-methods">
                        <?php do_action('woocommerce_checkout_before_payment'); ?>
                        <?php if (WC()->cart->needs_payment()) : ?>
                            <ul class="modern-payment-method-list wc_payment_methods payment_methods methods">
                                <?php
                                $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
                                if (!empty($available_gateways)) {
                                    foreach ($available_gateways as $gateway) {
                                        wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
                                    }
                                } else {
                                    echo '<li class="modern-payment-notice">' . apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__('Beklager, det ser ud til at der ikke er nogen tilgængelige betalingsmetoder. Kontakt os venligst hvis du har brug for hjælp.', 'woocommerce') : esc_html__('Indtast venligst dine adresseoplysninger for at se tilgængelige betalingsmetoder.', 'woocommerce')) . '</li>';
                                }
                                ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div class="modern-place-order">
                        <div class="form-row place-order">
                            <noscript>
                                <?php
                                /* translators: $1 and $2 open and close a link (to different page) */
                                printf(esc_html__('Desværre, din browser understøtter ikke JavaScript. For at gennemføre din ordre, skal du %1$s gå til den alternative side%2$s.', 'woocommerce'), '<a href="' . esc_url(add_query_arg('wc-ajax', 'update_order_review', wc_get_checkout_url())) . '">', '</a>');
                                ?>
                            </noscript>

                            <?php wc_get_template('checkout/terms.php'); ?>

                            <?php do_action('woocommerce_review_order_before_submit'); ?>

                            <?php 
                            if ( ! isset( $order_button_text ) ) {
                                $order_button_text = __( 'Place order', 'woocommerce' );
                            }
                            echo apply_filters('woocommerce_order_button_html', '<button type="submit" class="modern-button order-button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); ?>

                            <?php do_action('woocommerce_review_order_after_submit'); ?>

                            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>