<?php
/**
 * Plugin Name: Liverakala Core
 * Plugin URI:  https://liverakala.com/
 * Description: افزونه جامع، ماژولار و اختصاصی لیوراکالا (هدر، لایو سرچ، احراز هویت، مینی‌کارت، اسلایدرها و بلاگ)
 * Version:     2.0.0
 * Author:      Liverakala
 * Text Domain: liverakala-core
 */

// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// تعریف ثوابت مرکزی افزونه برای دسترسی سریع به مسیرها و نسخه‌ها
define( 'LIVERAKALA_VERSION', '2.0.0' );
define( 'LIVERAKALA_PATH', plugin_dir_path( __FILE__ ) );
define( 'LIVERAKALA_URL', plugin_dir_url( __FILE__ ) );

/**
 * ۱. بارگذاری اصولی و استاندارد فایل‌های استایل (CSS) و اسکریپت (JS)
 */
function liverakala_enqueue_assets() {
    // بارگذاری فایل استایل اصلی از پوشه assets با قابلیت ضد کش (Cache Busting)
    $style_path = LIVERAKALA_PATH . 'assets/style.css';
    if ( file_exists( $style_path ) ) {
        wp_enqueue_style( 'liverakala-main-style', LIVERAKALA_URL . 'assets/style.css', array(), filemtime( $style_path ) );
    }

    // بارگذاری فایل اسکریپت اصلی
    $script_path = LIVERAKALA_PATH . 'assets/script.js';
    if ( file_exists( $script_path ) ) {
        wp_enqueue_script( 'liverakala-main-script', LIVERAKALA_URL . 'assets/script.js', array( 'jquery' ), filemtime( $script_path ), true );
    }
}
// استفاده از اولویت 99 جهت تضمین بارگذاری استایل‌ها پس از استایل‌های قالب اصلی
add_action( 'wp_enqueue_scripts', 'liverakala_enqueue_assets', 99 );

/**
 * ۲. بارگذاری اسکریپت‌های پنل مدیریت
 */
function liverakala_admin_assets( $hook ) {
    global $typenow;
    if ( 'liverakala_banner' === $typenow ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'liverakala_admin_assets' );

/**
 * ۳. بارگذاری امن و خودکار ماژول‌های افزونه از پوشه includes
 */
$liverakala_modules = array(
    'archive-products.php',
    'blog.php',
    'search.php',
    'sliders.php',
    'auth.php',
    'mega-menu.php',
    'related-product.php',
    'single-product.php',
    'faq.php',
    'class-lr-comments-handler.php',
    'class-lr-comments-schema.php',
    'checkout-cart.php',
    'about.php',
    'contact.php',
    'register-login.php',
   
);

foreach ( $liverakala_modules as $module ) {
    $file_path = LIVERAKALA_PATH . 'includes/' . $module;
    if ( file_exists( $file_path ) ) {
        require_once $file_path;
    }
}
// استثنا کردن تصاویر بنر لیوراکالا از لیزی‌لود لایت‌اسپید
add_filter( 'litespeed_media_lazy_load_excludes', function( $excludes ) {
    $excludes[] = 'lk-img-desktop';
    $excludes[] = 'lk-img-mobile';
    return $excludes;
});

/**
 * ==================================================
 * تزریق مستقیم کلاس skip-lazy و لود سریع به تصویر لوگو
 * ==================================================
 */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment, $size ) {
    // بررسی شناسه تصویر لوگو (6915)
    if ( isset( $attachment->ID ) && $attachment->ID == 6915 ) {
        $attr['loading'] = 'eager';
        $attr['fetchpriority'] = 'high';
        $attr['data-no-lazy'] = '1';
        
        // اضافه کردن کلاس skip-lazy برای عدم دخالت لایت‌اسپید
        if ( isset( $attr['class'] ) ) {
            $attr['class'] .= ' skip-lazy';
        } else {
            $attr['class'] = 'skip-lazy';
        }
    }
    return $attr;
}, 10, 3 );

// جایگزینی قالب کامنت پیش‌فرض وردپرس و ووکامرس با قالب اختصاصی لیوراکالا
add_filter('comments_template', 'lr_load_custom_comment_template');
function lr_load_custom_comment_template($template) {
    // بررسی اینکه آیا در صفحه محصول یا مقاله (پست) هستیم
    if (is_singular(array('post', 'product'))) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/comment-form.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
/**
 * ثبت شورت‌کد [liverakala_cart]
 */
add_shortcode( 'liverakala_cart', 'liverakala_render_modern_cart' );

function liverakala_render_modern_cart() {
    ob_start();
    
    // آدرس فایل بالا که در پوشه includes قرار دارد
    $file_path = plugin_dir_path( __FILE__ ) . 'includes/checkout-cart.php';
    
    if ( file_exists( $file_path ) ) {
        include $file_path;
    } else {
        echo '<p>فایل تمپلیت سبد خرید یافت نشد.</p>';
    }
    
    return ob_get_clean();
}