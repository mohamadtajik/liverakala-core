<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ۱. شورت‌کد صفحه سبد خرید با طراحی مدرن برای حالت خالی
 */
add_shortcode( 'liverakala_custom_cart', 'liverakala_render_custom_cart' );

add_action( 'wp_enqueue_scripts', 'liverakala_cart_checkout_assets' );
function liverakala_cart_checkout_assets() {
    if ( is_cart() || is_checkout() ) {
   
        $css_path = LIVERAKALA_PATH . 'assets/modern-checkout.css';
        if ( file_exists( $css_path ) ) {
            wp_enqueue_style( 'liverakala-modern-checkout', LIVERAKALA_URL . 'assets/modern-checkout.css', array(), filemtime( $css_path ) );
        }

        $js_path = LIVERAKALA_PATH . 'assets/modern-checkout.js';
        if ( file_exists( $js_path ) ) {
            wp_enqueue_script( 'liverakala-modern-checkout', LIVERAKALA_URL . 'assets/modern-checkout.js', array( 'jquery' ), filemtime( $js_path ), true );
        }
    }
}

function liverakala_render_custom_cart() {
    if ( WC()->cart->is_empty() ) {
        ob_start();
        ?>
        <div class="liverakala-empty-container">
            <div class="liverakala-empty-card">
                <div class="empty-icon-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <h3 class="empty-title">سبد خرید شما در حال حاضر خالی است!</h3>
                <p class="empty-desc">هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. از محصولات شگفت‌انگیز ما دیدن کنید و خرید خود را آغاز نمایید.</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="empty-shop-btn">
                    <span>مشاهده و خرید محصولات</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    ob_start();
    ?>
    <div class="liverakala-custom-cart-wrapper">
        <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
            
            <div class="cart-collaterals">
                <div class="cart_totals">
                    <table class="shop_table shop_table_responsive" cellspacing="0">
                        <tbody>
                            <tr class="cart-subtotal">
                                <th>جمع جزء</th>
                                <td data-title="جمع جزء"><?php wc_cart_totals_subtotal_html(); ?></td>
                            </tr>

                            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                    <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                                    <td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                                <tr class="fee">
                                    <th><?php echo esc_html( $fee->name ); ?></th>
                                    <td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                                <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                                    <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                                        <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                            <th><?php echo esc_html( $tax->label ); ?></th>
                                            <td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="tax-total">
                                        <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                                        <td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

                            <tr class="order-total">
                                <th>مبلغ کل</th>
                                <td data-title="مبلغ کل"><?php wc_cart_totals_order_total_html(); ?></td>
                            </tr>

                            <?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
                        </tbody>
                    </table>

                    <div class="wc-proceed-to-checkout">
                        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward">ثبت سفارش</a>
                    </div>
                </div>
            </div>

            <div class="liverakala-cart-items">
                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        
                        $line_total   = WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
                        $thumbnail    = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                        $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                        ?>
                        <div class="liverakala-cart-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                            <div class="item-thumb"><?php echo $thumbnail; ?></div>
                            <div class="item-info">
                                <h4 class="item-title">
                                    <?php
                                    if ( empty( $product_permalink ) ) {
                                        echo $product_name;
                                    } else {
                                        echo sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_name );
                                    }
                                    ?>
                                </h4>
                            </div>
                            <div class="item-quantity">
                                <?php
                                if ( $_product->is_sold_individually() ) {
                                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                } else {
                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $_product->get_max_purchase_quantity(),
                                            'min_value'    => '1',
                                            'product_name' => $_product->get_name(),
                                        ),
                                        $_product,
                                        false
                                    );
                                }
                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                ?>
                            </div>
                            <div class="item-subtotal"><?php echo $line_total; ?></div>
                            <div class="item-remove">
                                <?php
                                $trash_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                                echo sprintf(
                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                    esc_html__( 'Remove this item', 'woocommerce' ),
                                    esc_attr( $product_id ),
                                    esc_attr( $cart_item_key ),
                                    esc_attr( $_product->get_sku() ),
                                    $trash_icon
                                );
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <input type="hidden" name="update_cart" value="1" />
            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        </form>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * ۲. تغییر متن خطای پیش‌فرض ووکامرس برای شهرستان
 */
add_filter( 'woocommerce_no_available_payment_methods_message', 'liverakala_override_payment_error_msg', 99 );
function liverakala_override_payment_error_msg( $message ) {
    return '📦 ارسال سفارشات شهرستان<br>برای ثبت و ارسال سفارش به شهرهای خارج از تهران، لطفاً با پشتیبانی ما تماس بگیرید:<br><br><a href="tel:09394008214" class="notice-phone-btn">📞 تماس با 09394008214</a>';
}


/**
 * ۳. تنظیم اولویت و ترتیب فیلدهای آدرس
 */
add_filter( 'woocommerce_default_address_fields', 'liverakala_reorder_address_fields' );
function liverakala_reorder_address_fields( $fields ) {
    $fields['country']['priority']   = 10;
    $fields['state']['priority']     = 20;
    $fields['city']['priority']      = 25;
    $fields['address_1']['priority'] = 30;

    return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'liverakala_custom_checkout_fields' );
function liverakala_custom_checkout_fields( $fields ) {
    if ( isset( $fields['billing']['billing_email'] ) ) {
        $fields['billing']['billing_email']['required'] = false;
        $fields['billing']['billing_email']['label'] = 'ایمیل';
    }

    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        $fields['billing']['billing_postcode']['required'] = false;
        $fields['billing']['billing_postcode']['label'] = 'کد پستی';
    }

    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['label'] = 'موبایل';
    }
        
    if ( isset( $fields['billing']['billing_country'] ) ) {
        $fields['billing']['billing_country']['type'] = 'hidden';
        $fields['billing']['billing_country']['default'] = 'IR';
        $fields['billing']['billing_country']['required'] = false;
    }

    unset( $fields['billing']['billing_company'] );
    unset( $fields['billing']['billing_address_2'] );

    return $fields;
}

/**
 * ۴. شورت‌کد صفحه چک‌اوت اختصاصی [liverakala_custom_checkout]
 */
add_shortcode( 'liverakala_custom_checkout', 'liverakala_render_custom_checkout' );

function liverakala_render_custom_checkout() {
    if ( WC()->cart->is_empty() ) {
        ob_start();
        ?>
        <div class="liverakala-empty-container">
            <div class="liverakala-empty-card">
                <div class="empty-icon-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <h3 class="empty-title">امکان تسویه حساب وجود ندارد!</h3>
                <p class="empty-desc">سبد خرید شما خالی است. لطفاً ابتدا محصولاتی را به سبد خرید خود اضافه کنید سپس برای ثبت سفارش اقدام نمایید.</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="empty-shop-btn">
                    <span>بازگشت به فروشگاه</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // اگر سبد خالی نبود، فرم تسویه حساب رندر می‌شود...
    ob_start();
    ?>
    <div class="liverakala-custom-checkout-wrapper">
        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

            <div class="checkout-grid-container">
                
                <div class="checkout-main-col">
                    <h3 class="checkout-section-title">جزئیات صورتحساب</h3>
                    <?php 
                    $checkout = WC()->checkout();
                    if ( sizeof( $checkout->get_checkout_fields() ) > 0 ) : 
                        do_action( 'woocommerce_checkout_before_customer_details' );
                        ?>
                        <div class="customer-details-box" id="customer_details">
                            <div class="billing-fields-wrap">
                                <?php do_action( 'woocommerce_checkout_billing' ); ?>
                            </div>
                            <div class="shipping-fields-wrap" style="display:none;">
                                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                            </div>
                        </div>
                        <?php 
                        do_action( 'woocommerce_checkout_after_customer_details' ); 
                    endif; 
                    ?>

                    <div class="liverakala-additional-fields" style="margin-top: 20px;">
                        <?php
                        $checkout_fields = $checkout->get_checkout_fields();
                        if ( isset( $checkout_fields['order']['order_comments'] ) ) {
                            foreach ( $checkout__fields['order'] as $key => $field ) {
                                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="checkout-sidebar-col">
                    <h3 class="checkout-section-title">سفارش شما</h3>
                    <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>
                    <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
                </div>

            </div>

        </form>
    </div>
    <?php
    return ob_get_clean();
}