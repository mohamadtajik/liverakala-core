<?php
if (!defined('ABSPATH')) {
    exit;
}

class LR_Comments_Handler {

    public function __construct() {
        // ثبت اکشن‌های ایجکس
        add_action('wp_ajax_lr_submit_comment', array($this, 'handle_comment_submission'));
        add_action('wp_ajax_nopriv_lr_submit_comment', array($this, 'handle_comment_submission'));
        
        // بارگذاری استایل‌ها و اسکریپت‌ها با نسخه خودکار بر اساس زمان ویرایش فایل
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // ثبت شورت‌کد
        add_shortcode('lr_comment_form', array($this, 'render_comment_form'));

        // جایگزینی خودکار فرم دیدگاه‌ها در نوشته‌های بلاگ و محصولات
        add_filter('comments_template', array($this, 'override_comments_template'));
    }

    public function enqueue_assets() {
        if (is_singular()) {
            $css_path    = plugin_dir_path(__FILE__) . '../assets/lr-comments.css';
            $css_version = file_exists($css_path) ? filemtime($css_path) : '1.0.0';

            $js_path    = plugin_dir_path(__FILE__) . '../assets/lr-comments.js';
            $js_version = file_exists($js_path) ? filemtime($js_path) : '1.0.0';

            wp_enqueue_style(
                'lr-comments-style',
                plugin_dir_url(__FILE__) . '../assets/lr-comments.css',
                array(),
                $css_version
            );

            wp_enqueue_script(
                'lr-comments-script',
                plugin_dir_url(__FILE__) . '../assets/lr-comments.js',
                array('jquery'),
                $js_version,
                true
            );

            wp_localize_script('lr-comments-script', 'lr_comment_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('lr_comment_nonce')
            ));
        }
    }

    public function override_comments_template($template) {
        $post_type = get_post_type();
        $plugin_dir = plugin_dir_path(dirname(__FILE__)); // مسیر اصلی پوشه افزونه (liverakala-core)
        
        if ($post_type === 'product') {
            $custom_template = $plugin_dir . 'templates/comment-form-product.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        } elseif ($post_type === 'post') {
            $custom_template = $plugin_dir . 'templates/comment-form-blog.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function render_comment_form() {
        ob_start();
        $post_type = get_post_type();
        $plugin_dir = plugin_dir_path(dirname(__FILE__));
        
        if ($post_type === 'product') {
            $template_path = $plugin_dir . 'templates/comment-form-product.php';
        } else {
            $template_path = $plugin_dir . 'templates/comment-form-blog.php';
        }
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p style="color:red;">قالب فرم نظرات یافت نشد. مسیر: ' . esc_html($template_path) . '</p>';
        }
        
        return ob_get_clean();
    }

    public function handle_comment_submission() {
        check_ajax_referer('lr_comment_nonce', 'security');

        if (!empty($_POST['lr_hp_field'])) {
            wp_send_json_error(array('message' => 'ارسال غیرمجاز تشخیص داده شد.'));
        }

        $post_id      = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $comment_text = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : '';
        $rating       = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $post_type    = get_post_type($post_id);

        if (!$post_id || empty($comment_text)) {
            wp_send_json_error(array('message' => 'لطفاً متن نظر را وارد کنید.'));
        }

        // بررسی اجباری بودن امتیاز برای محصولات
        if ($post_type === 'product' && ($rating < 1 || $rating > 5)) {
            wp_send_json_error(array('message' => 'لطفاً امتیاز خود را به محصول انتخاب کنید.'));
        }

        if (is_user_logged_in()) {
            $user         = wp_get_current_user();
            $author_name  = $user->display_name;
            $author_email = $user->user_email;
            $user_id      = $user->ID;
        } else {
            $author_name = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
            $raw_email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $user_id     = 0;

            if (empty($author_name)) {
                wp_send_json_error(array('message' => 'لطفاً نام و نام خانوادگی خود را وارد کنید.'));
            }

            // ایمیل اختیاری: اگر خالی بود، یک ایمیل سیستمی معتبر ساخته می‌شود تا وردپرس خطا ندهد
            $author_email = !empty($raw_email) ? $raw_email : 'guest_' . wp_generate_password(6, false) . '@liverakala.local';
        }

        $is_buyer = 0;
        if ($post_type === 'product' && $user_id > 0 && function_exists('wc_customer_bought_product')) {
            if (wc_customer_bought_product($author_email, $user_id, $post_id)) {
                $is_buyer = 1;
            }
        }

        $comment_data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $author_name,
            'comment_author_email' => $author_email,
            'comment_content'      => $comment_text,
            'comment_type'         => 'comment',
            'comment_parent'       => isset($_POST['parent']) ? intval($_POST['parent']) : 0,
            'user_id'              => $user_id,
            'comment_approved'     => 0,
        );

        $comment_id = wp_insert_comment($comment_data);

        if ($comment_id) {
            if ($post_type === 'product' && $rating >= 1 && $rating <= 5) {
                update_comment_meta($comment_id, 'rating', $rating);
                update_comment_meta($comment_id, 'lr_comment_rating', $rating);
            }
            if ($is_buyer) {
                update_comment_meta($comment_id, 'is_buyer', 1);
            }

            wp_send_json_success(array(
                'message' => 'نظر شما با موفقیت ثبت شد و پس از تایید مدیر نمایش داده خواهد شد.'
            ));
        } else {
            wp_send_json_error(array('message' => 'خطایی در ثبت نظر رخ داد. دوباره تلاش کنید.'));
        }
    }
}

new LR_Comments_Handler();