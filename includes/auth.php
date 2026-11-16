<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * شورت‌کد دکمه احراز هویت
 * Shortcode: [lr_auth_btn]
 */
function liverakala_user_auth_button() {
    ob_start();
    
    $account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );

    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $user_name    = ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
        $logout_url   = wp_logout_url( home_url() );
        
        ?>
        <div class="lr-auth-dropdown-wrapper">
            <a href="<?php echo esc_url( $account_url ); ?>" class="lr-auth-btn logged-in">
                <span class="lr-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                <span class="lr-text">سلام، <?php echo esc_html( $user_name ); ?></span>
            </a>
            
            <div class="lr-user-popup-menu">
                <a href="<?php echo esc_url( $account_url ); ?>" class="lr-popup-item">
                    <span class="popup-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <span>مشاهده پنل کاربری</span>
                </a>
                
                <a href="<?php echo esc_url( $logout_url ); ?>" class="lr-popup-item logout-item">
                    <span class="popup-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </span>
                    <span>خروج از حساب کاربری</span>
                </a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="lr-auth-dropdown-wrapper">
            <a href="<?php echo esc_url( $account_url ); ?>" class="lr-auth-btn logged-out">
                <span class="lr-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                </span>
                <span class="lr-text">ورود / ثبت‌ نام</span>
            </a>
        </div>
        <?php
    }
    
    return ob_get_clean();
}
add_shortcode( 'lr_auth_btn', 'liverakala_user_auth_button' );

/**
 * رد کردن صفحه تاییدیه خروج و هدایت مستقیم کاربر
 */
function liverakala_skip_logout_confirmation( $url, $redirect ) {
    return wp_nonce_url( str_replace( '&amp;', '&', $url ), 'log-out' );
}
add_filter( 'logout_url', 'liverakala_skip_logout_confirmation', 10, 2 );