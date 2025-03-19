<?php
/**
 * Plugin Name: Modern WooCommerce Cart & Checkout
 * Description: En moderne tilgang til WooCommerce cart og checkout oplevelsen
 * Version: 1.0.0
 * Author: JaxWeb
 */

// Sørg for direkte adgang er blokeret
if (!defined('ABSPATH')) {
    exit;
}

class Modern_WooCommerce_Cart_Checkout {
    
    public function __construct() {
        // Tilføj vores egne templates
        add_filter('woocommerce_locate_template', array($this, 'override_woocommerce_templates'), 10, 3);
        
        // Tilføj vores CSS og JS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Fjern standard WooCommerce styling på vores sider
        add_filter('woocommerce_enqueue_styles', array($this, 'dequeue_woocommerce_styles'));
        
        // AJAX handler til cart opdateringer
        add_action('wp_ajax_update_cart_item', array($this, 'update_cart_item_callback'));
        add_action('wp_ajax_nopriv_update_cart_item', array($this, 'update_cart_item_callback'));
    }
    
    public function override_woocommerce_templates($template, $template_name, $template_path) {
        // Kun override cart og checkout templates
        $our_templates = array(
            'cart/cart.php',
            'checkout/form-checkout.php',
            'checkout/review-order.php',
            'checkout/payment.php'
        );
        
        if (in_array($template_name, $our_templates)) {
            $plugin_path = plugin_dir_path(__FILE__) . 'templates/';
            $template_file = $plugin_path . $template_name;
            
            if (file_exists($template_file)) {
                return $template_file;
            }
        }
        
        return $template;
    }
    
    public function enqueue_scripts() {
        if (is_cart() || is_checkout()) {
            // Deregister standard jQuery og tilføj en nyere version
            wp_deregister_script('jquery');
            wp_register_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js', array(), '3.6.3', true);
            
            // Tilføj vores CSS
            wp_enqueue_style(
                'modern-wc-cart-checkout',
                plugin_dir_url(__FILE__) . 'assets/css/style.css',
                array(),
                '1.0.0'
            );
            
            // Tilføj vores JS
            wp_enqueue_script(
                'modern-wc-cart-checkout',
                plugin_dir_url(__FILE__) . 'assets/js/script.js',
                array('jquery'),
                '1.0.1',
                true
            );
            
            // Tilføj AJAX URL til JS
            wp_localize_script(
                'modern-wc-cart-checkout',
                'modern_wc_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('modern_wc_nonce')
                )
            );
        }
    }
    
    public function dequeue_woocommerce_styles($styles) {
        if (is_cart() || is_checkout()) {
            // Fjern standard WooCommerce styles på vores sider
            unset($styles['woocommerce-general']);
            unset($styles['woocommerce-layout']);
            unset($styles['woocommerce-smallscreen']);
        }
        return $styles;
    }
    
    public function update_cart_item_callback() {
    check_ajax_referer('modern_wc_nonce', 'security');

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($cart_item_key && $quantity >= 0) {
        // Allow 0 for removal, otherwise ensure minimum is 1
        $quantity = ($quantity === 0) ? 0 : max(1, $quantity);
        $result = WC()->cart->set_quantity($cart_item_key, $quantity, false);
        if ($result === false) {
            $response = array(
                'success' => false,
                'message' => 'Failed to update cart quantity'
            );
            wp_send_json($response);
        }

        // Update cart totals
        WC()->cart->calculate_totals();

        // Get the cart item
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        $_product = $cart_item['data'];

        // Get item subtotal
        $item_subtotal = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);

        // Get cart collaterals output
        ob_start();
        do_action('woocommerce_cart_collaterals');
        $collaterals_output = ob_get_clean();

        $response = array(
            'success' => true,
            'item_count' => WC()->cart->get_cart_contents_count(),
            'item_subtotal' => $item_subtotal,
            'cart_collaterals' => $collaterals_output
        );
    } else {
        $response = array(
            'success' => false,
            'message' => 'Ugyldig forespørgsel'
        );
    }

    wp_send_json($response);
}
}

// Start plugin
new Modern_WooCommerce_Cart_Checkout();