<?php

add_filter( 'woocommerce_add_error', 'lk_persianize_woocommerce_errors_v3', 10, 1 );
function lk_persianize_woocommerce_errors_v3( $error ) {
    if ( strpos( $error, 'already registered' ) !== false || strpos( $error, 'حسابی با این آدرس ایمیل' ) !== false ) {
        return 'حسابی با این ایمیل قبلاً ثبت‌نام کرده است. لطفاً وارد شوید یا از ایمیل دیگری استفاده کنید.';
    }
    if ( strpos( $error, 'Please provide a valid email address' ) !== false ) {
        return 'لطفاً یک آدرس ایمیل معتبر وارد کنید.';
    }
    if ( strpos( $error, 'Please enter an account password' ) !== false || strpos( $error, 'Please enter a valid account password' ) !== false ) {
        return 'لطفاً یک رمز عبور معتبر برای حساب خود وارد کنید.';
    }
    if ( strpos( $error, 'Unknown username' ) !== false || strpos( $error, 'Invalid username' ) !== false ) {
        return 'نام کاربری یا ایمیل وارد شده وجود ندارد.';
    }
    if ( strpos( $error, 'Password you entered' ) !== false || strpos( $error, 'incorrect' ) !== false ) {
        return 'رمز عبور وارد شده اشتباه است. لطفاً دوباره تلاش کنید.';
    }
    return $error;
}

// ثبت و بارگذاری هوشمند فایل‌های CSS و JS در افزونه
add_action( 'wp_enqueue_scripts', 'lk_auth_assets_enqueue' );
function lk_auth_assets_enqueue() {
    // فقط در صفحه حساب کاربری و برای کاربران مهمان بارگذاری شود
    if ( ! is_account_page() || is_user_logged_in() ) {
        return;
    }

    $css_path = plugin_dir_path( __FILE__ ) . '../assets/auth-style.css';
    $js_path  = plugin_dir_path( __FILE__ ) . '../assets/auth-script.js';

   
    $css_uri  = plugin_dir_url( __FILE__ ) . '../assets/auth-style.css';
    $js_uri   = plugin_dir_url( __FILE__ ) . '../assets/auth-script.js';

    $css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0';
    $js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0';

    // بارگذاری فایل استایل
    wp_enqueue_style( 'lk-auth-style', $css_uri, array(), $css_ver );

    // بارگذاری فایل اسکریپت
    wp_enqueue_script( 'lk-auth-script', $js_uri, array(), $js_ver, true );
}

// غیرفعال‌سازی تکمیل خودکار فرم ورود
add_filter( 'woocommerce_login_form_args', function( $args ) {
    $args['autocomplete'] = 'off';
    return $args;
});