/**
 * Modern WooCommerce Cart & Checkout JavaScript
 */

(function($) {
    'use strict';
    
    // Vent på at dokumentet er indlæst
    $(document).ready(function() {
        // Lav en debounce-funktion til at begrænse antallet af AJAX-kald
        function debounce(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
        
        // Opdater kurv via AJAX
        function refreshCart() {
            $.ajax({
            type: 'POST',
            url: modern_wc_ajax.ajax_url,
            data: {
                action: 'refresh_cart',
                security: modern_wc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                $('.modern-cart-wrapper').html(response.cart_html);
                
                // Opdater header kurv indikator hvis den findes
                if (response.item_count && $('.cart-count').length > 0) {
                    $('.cart-count').text(response.item_count);
                }
                } else {
                console.error('Fejl ved opdatering af kurv:', response.message);
                }
            },
            error: function(error) {
                console.error('AJAX-fejl:', error);
            }
            });
        }
        function updateCartItem(itemKey, quantity) {
            $.ajax({
                type: 'POST',
                url: modern_wc_ajax.ajax_url,
                data: {
                    action: 'update_cart_item',
                    security: modern_wc_ajax.nonce,
                    cart_item_key: itemKey,
                    quantity: quantity
                },
                beforeSend: function() {
                    // Vis loading
                    $('.modern-cart-wrapper').addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        // Opdater subtotal for produktet
                        var $item = $('.modern-cart-item[data-item-key="' + itemKey + '"]');
                        
                        // Hvis mængden er 0, fjern varen
                        if (quantity === 0) {
                            $item.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Hvis der ikke er flere produkter, vis tom kurv besked
                                if ($('.modern-cart-item').length === 0) {
                                    $('.modern-cart-item-list').html('<div class="modern-cart-empty"><p>Din kurv er tom.</p><a href="' + woocommerce_params.shop_url + '" class="modern-button">Tilbage til butikken</a></div>');
                                    $('.modern-cart-sidebar').hide();
                                }
                            });
                        } else if (response.item_subtotal) {
                            // Opdater produktets subtotal
                            $item.find('.modern-cart-item-subtotal').html(response.item_subtotal);
                        }
                        
                        // Opdater sidepanel og totaler
                        if (response.cart_total) {
                            $('.modern-cart-totals').html(response.cart_total);
                        }
                        
                        // Opdater subtotal og moms hvis de findes i svaret
                        if (response.cart_subtotal) {
                            $('.modern-cart-subtotal-value').html(response.cart_subtotal);
                        }
                        
                        if (response.cart_tax) {
                            $('.modern-cart-tax-value').html(response.cart_tax);
                        }
                        
                        // Opdater header kurv indikator hvis den findes
                        if (response.item_count && $('.cart-count').length > 0) {
                            $('.cart-count').text(response.item_count);
                        }
                    } else {
                        console.error('Fejl ved opdatering af kurv:', response.message);
                    }
                },
                error: function(error) {
                    console.error('AJAX-fejl:', error);
                },
                complete: function() {
                    // Fjern loading
                    $('.modern-cart-wrapper').removeClass('loading');
                }
            });
        }
        
        // Event handler til quantity knapper
        $('.modern-cart-wrapper').on('click', '.modern-quantity-btn', function(e) {
            e.preventDefault();
            
            var $input = $(this).siblings('.modern-quantity-input');
            var itemKey = $input.data('item-key');
            var currentValue = parseInt($input.val(), 10);
            var newValue;
            
            if ($(this).data('action') === 'increase') {
                newValue = currentValue + 1;
            } else {
                newValue = currentValue - 1;
                if (newValue < 0) newValue = 0;
            }
            
            $input.val(newValue);
            
            // Debounce update function
            var debouncedUpdate = debounce(function() {
                updateCartItem(itemKey, newValue);
            }, 500);
            
            debouncedUpdate();
        });
        
        // Event handler til direkte input i quantity felt
        $('.modern-cart-wrapper').on('change', '.modern-quantity-input', function() {
            var itemKey = $(this).data('item-key');
            var newValue = parseInt($(this).val(), 10);
            
            if (isNaN(newValue) || newValue < 0) {
                newValue = 0;
                $(this).val(0);
            }
            
            updateCartItem(itemKey, newValue);
        });
        
        // Checkout-specifik JS
        if ($('.modern-checkout-form').length > 0) {
            // Skift mellem leveringsadresse og faktureringsadresse
            $('#ship-to-different-address-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.shipping_address').slideDown();
                } else {
                    $('.shipping_address').slideUp();
                }
            });
            
            // Skjul leveringsadresse som standard hvis ikke checked
            if (!$('#ship-to-different-address-checkbox').is(':checked')) {
                $('.shipping_address').hide();
            }
            
            // Opdater ordresummering når betalingsmetode ændres
            $('body').on('change', 'input[name="payment_method"]', function() {
                // WooCommerce trigger til at opdatere checkout
                $(document.body).trigger('update_checkout');
            });
        }
        
        // Tilføj loading animation
        $('<style>.loading { position: relative; opacity: 0.6; pointer-events: none; transition: opacity 0.3s; } .loading:after { content: ""; position: absolute; top: 50%; left: 50%; width: 30px; height: 30px; margin: -15px 0 0 -15px; border: 3px solid rgba(0,0,0,0.1); border-top-color: var(--mwc-primary); border-radius: 50%; animation: modern-wc-loader-rotation 0.8s infinite linear; } @keyframes modern-wc-loader-rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }</style>').appendTo('head');
        
        // Tilpas standard WooCommerce checkout-validering
        if (typeof wc_checkout_params !== 'undefined') {
            // Tilføj en mere moderne validerings-stil
            $(document.body).on('checkout_error', function() {
                $('html, body').animate({
                    scrollTop: $('.woocommerce-error').offset().top - 100
                }, 500);
            });
            
            // Opdater betaling-sektion når leveringsmetode ændres
            $(document.body).on('updated_checkout', function() {
                // Animer til betalingssektion hvis bruger er nået til leveringsmetode
                if ($('input[name^="shipping_method"]:checked').length) {
                    $('html, body').animate({
                        scrollTop: $('.modern-payment-section').offset().top - 100
                    }, 500);
                }
            });
        }
    });
    
})(jQuery);