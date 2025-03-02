<?php
/**
 * Cart Page Template
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<div class="modern-cart-wrapper">
    <form class="modern-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php do_action('woocommerce_before_cart_table'); ?>
        
        <div class="modern-cart-content">
            <div class="modern-cart-items">
                <h2><?php esc_html_e('Din kurv', 'woocommerce'); ?></h2>
                
                <?php if (WC()->cart->is_empty()) : ?>
                    <div class="modern-cart-empty">
                        <p><?php esc_html_e('Din kurv er tom.', 'woocommerce'); ?></p>
                        <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="modern-button">
                            <?php esc_html_e('Tilbage til butikken', 'woocommerce'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="modern-cart-item-list">
                        <?php do_action('woocommerce_before_cart_contents'); ?>
                        <div class="modern-cart-item modern-cart-header"">
                            <div class="modern-cart-item-image"></div>
                            <div class="modern-cart-item-details">
                                <div class="modern-cart-item-name"><?php esc_html_e('Vare', 'woocommerce'); ?></div>
                                <div class="modern-cart-item-price"><?php esc_html_e('Pris', 'woocommerce'); ?></div>
                                <div class="modern-cart-item-quantity"><?php esc_html_e('Antal', 'woocommerce'); ?></div>
                                <div class="modern-cart-item-subtotal"><?php esc_html_e('Subtotal', 'woocommerce'); ?></div>
                                <div class="modern-cart-item-remove"></div>
                            </div>
                        </div>
                        <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                            
                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                ?>
                                <div class="modern-cart-item" data-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <div class="modern-cart-item-image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                        
                                        if (!$product_permalink) {
                                            echo $thumbnail;
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                        }
                                        ?>
                                    </div>
                                    <div class="modern-cart-item-details">
                                        <div class="modern-cart-item-name" data-label="Produkt:">
                                            <div class="product-title">
                                                <?php
                                                // Vis kun produktnavnet uden variationer
                                                $product_name = $_product->get_title(); // Brug get_title() i stedet for get_name()
                                                if (!$product_permalink) {
                                                    echo wp_kses_post($product_name);
                                                } else {
                                                    printf('<a href="%s">%s</a>', esc_url($product_permalink), esc_html($product_name));
                                                }
                                                ?>
                                            </div>

                                            <?php if (!empty($cart_item['variation'])): ?>
                                                <div class="product-variations">
                                                    <?php
                                                    foreach ($cart_item['variation'] as $variation_name => $variation_value) {
                                                        // Skip if custom_length exists in cart_item data
                                                        if (isset($cart_item['custom_length'])) {
                                                            if (isset($cart_item['custom_height'])) {
                                                                printf('<div class="variation-item">%s: %s mm x %s mm</div>', 
                                                                esc_html('Mål'), 
                                                                esc_html($cart_item['custom_length']),
                                                                esc_html($cart_item['custom_height'])
                                                            );
                                                            } else {
                                                                printf('<div class="variation-item">%s: %s mm</div>', 
                                                                    esc_html('Længde'), 
                                                                    esc_html($cart_item['custom_length'])
                                                                );
                                                            }
                                                            break;
                                                        }
                                                        
                                                        // Fjern 'attribute_' og 'pa_' præfikser
                                                        $clean_name = str_replace(['attribute_', 'pa_'], '', $variation_name);
                                                        // Konverter snake_case til læsbar tekst
                                                        $clean_name = ucfirst(str_replace('_', ' ', $clean_name));
                                                        
                                                        // Rens værdien
                                                        $clean_value = str_replace(['-mm', '-'], [' mm', ' '], $variation_value);
                                                        
                                                        printf('<div class="variation-item">%s: %s</div>', 
                                                            esc_html($clean_name), 
                                                            esc_html($clean_value)
                                                        );
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php
                                            do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
                                            
                                            // Meta data
                                            echo wc_get_formatted_cart_item_data($cart_item);
                                            
                                            // Backorder notification
                                            if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', 
                                                    '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', 
                                                    $product_id
                                                ));
                                            }
                                            ?>
                                        </div>
                                        <div class="modern-cart-item-price" data-label="Pris:">
                                            <?php
                                                echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                                            ?>
                                        </div>
                                        <div class="modern-cart-item-quantity" data-label="Antal:">
                                            <button type="button" class="modern-quantity-btn minus" data-action="decrease">-</button>
                                            <input 
                                                type="number" 
                                                class="modern-quantity-input" 
                                                value="<?php echo esc_attr($cart_item['quantity']); ?>" 
                                                min="0" 
                                                step="1"
                                                data-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" 
                                            />
                                            <button type="button" class="modern-quantity-btn plus" data-action="increase">+</button>
                                        </div>
                                        <div class="modern-cart-item-subtotal" data-label="Subtotal:">
                                            <?php
                                                echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
                                            ?>
                                        </div>
                                        <div class="modern-cart-item-remove">
                                            <?php
                                                echo apply_filters(
                                                    'woocommerce_cart_item_remove_link',
                                                    sprintf(
                                                        '<a href="%s" class="modern-remove-btn" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></a>',
                                                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                        esc_html__('Fjern denne vare', 'woocommerce'),
                                                        esc_attr($product_id),
                                                        esc_attr($_product->get_sku())
                                                    ),
                                                    $cart_item_key
                                                );
                                            ?>
                                        </div>
                                    </div>
                                </div>                                
                                <?php
                            }
                        }
                        ?>
                        
                        <?php do_action('woocommerce_cart_contents'); ?>
                    </div>
                    
                    <div class="modern-cart-actions">
                        <button type="submit" class="modern-button update-cart" name="update_cart" value="<?php esc_attr_e('Opdater kurv', 'woocommerce'); ?>"><?php esc_html_e('Opdater kurv', 'woocommerce'); ?></button>
                        
                        <?php do_action('woocommerce_cart_actions'); ?>
                        
                        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                    </div>
                    
                    <?php do_action('woocommerce_after_cart_contents'); ?>
                <?php endif; ?>
            </div>
            
            <?php if (!WC()->cart->is_empty()) : ?>
                <div class="modern-cart-sidebar">
                    <div class="modern-cart-totals">
                        <?php do_action('woocommerce_before_cart_collaterals'); ?>
                        
                        <?php
                            /**
                             * Cart collaterals hook.
                             *
                             * @hooked woocommerce_cross_sell_display
                             * @hooked woocommerce_cart_totals - 10
                             */
                            do_action('woocommerce_cart_collaterals');
                        ?>
                        
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php do_action('woocommerce_after_cart_table'); ?>
    </form>
    
    <?php do_action('woocommerce_after_cart'); ?>
</div>