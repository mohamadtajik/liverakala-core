<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ۱. ثبت پست تایپ اختصاصی برای مدیریت پیام‌ها در پیشخوان وردپرس
function lk_register_contact_messages_cpt() {
    register_post_type( 'lk_contact_message', array(
        'labels' => array(
            'name'               => 'پیام‌های تماس',
            'singular_name'      => 'پیام تماس',
            'menu_name'          => 'پیام‌های لیوراکالا',
            'all_items'          => 'همه پیام‌ها',
            'view_item'          => 'مشاهده پیام',
        ),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'capability_type'     => 'post',
        'capabilities'        => array( 'create_posts' => false ),
        'map_meta_cap'        => true,
        'menu_icon'           => 'dashicons-email-alt',
        'supports'            => array( 'title', 'editor' ),
    ) );
}
add_action( 'init', 'lk_register_contact_messages_cpt' );

// ۲. پردازش فرم و شورت‌کد صفحه تماس
function liverakala_contact_page_shortcode() {
    $message_status = '';

    // بررسی اینکه آیا از صفحه قبلی پیام موفقیت منتقل شده است یا خیر
    if ( isset( $_GET['status'] ) && $_GET['status'] === 'success' ) {
        $message_status = '<div class="lk-form-alert lk-alert-success">پیام شما با موفقیت ثبت شد. کارشناسان ما به زودی با شما تماس خواهند گرفت.</div>';
    } elseif ( isset( $_GET['status'] ) && $_GET['status'] === 'error' ) {
        $message_status = '<div class="lk-form-alert lk-alert-error">خطا در ثبت اطلاعات یا تکمیل فیلدها. لطفاً مجدداً تلاش کنید.</div>';
    }

    // بررسی ارسال فرم و امنیت Nonce
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['lk_contact_nonce'] ) && wp_verify_nonce( $_POST['lk_contact_nonce'], 'lk_contact_form_action' ) ) {
        
        $name    = sanitize_text_field( $_POST['lk_name'] );
        $email   = sanitize_email( $_POST['lk_email'] );
        $subject = sanitize_text_field( $_POST['lk_subject'] );
        $message = sanitize_textarea_field( $_POST['lk_message'] );

        if ( ! empty( $name ) && ! empty( $email ) && ! empty( $message ) ) {
            
            // گام اول: ذخیره امن در دیتابیس وردپرس
            $post_id = wp_insert_post( array(
                'post_title'   => $subject ? $subject . ' - ' . $name : 'پیام از طرف ' . $name,
                'post_content' => "نام فرستنده: $name \nایمیل: $email \nموضوع: $subject \n\nمتن پیام:\n$message",
                'post_status'  => 'publish',
                'post_type'    => 'lk_contact_message',
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                
                // گام دوم: ارسال ایمیل اطلاع‌رسانی به مدیر سایت
                $to            = get_option( 'admin_email' ); 
                $email_subject = 'پیام جدید در سایت: ' . ( $subject ? $subject : 'تماس با ما' );
                $body          = "یک پیام جدید از طریق فرم تماس با ما ثبت شد:\n\nنام: $name \nایمیل: $email \nموضوع: $subject \n\nمتن پیام:\n$message";
                $headers       = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );

                @wp_mail( $to, $email_subject, $body, $headers );

                // ریدایرکت هوشمند برای جلوگیری از ارسال مجدد با رفرش صفحه (PRG Pattern)
                $redirect_url = add_query_arg( 'status', 'success', remove_query_arg( array('lk_name', 'lk_email', 'lk_subject', 'lk_message') ) );
                wp_safe_redirect( $redirect_url );
                exit;

            } else {
                $redirect_url = add_query_arg( 'status', 'error' );
                wp_safe_redirect( $redirect_url );
                exit;
            }
        } else {
            $message_status = '<div class="lk-form-alert lk-alert-error">لطفاً تمامی فیلدهای الزامی را پر کنید.</div>';
        }
    }

    ob_start();
    ?>
    <div class="lk-contact-wrapper-main">
        <div class="lk-contact-container-main">
            
            <!-- بخش هیرو (Hero Section) -->
            <div class="lk-about-hero lk-reveal-item lk-delay-1">
                <span class="lk-about-badge">پل‌های ارتباطی لیوراکالا</span>
                <h1>همیشه در کنار شما؛ پاسخگوی سوالات و نیازهای شما هستیم</h1>
                <p>تیم پشتیبانی و توسعه لیوراکالا در تمامی مراحل همراه شماست. از طریق راه‌های زیر با ما در ارتباط باشید یا درخواست خود را مستقیماً ثبت کنید.</p>
            </div>

            <!-- گرید دو ستونی اصلی -->
            <div class="lk-contact-grid">
                
                <!-- فرم تماس -->
                <div class="lk-contact-form-wrapper lk-reveal-item lk-delay-2">
                    <form class="lk-contact-form" action="" method="post">
                        <?php 
                        if ( ! empty( $message_status ) ) {
                            echo $message_status;
                        }
                        wp_nonce_field( 'lk_contact_form_action', 'lk_contact_nonce' );
                        ?>
                        <h3>ارسال پیام به تیم لیوراکالا</h3>
                        <p>فرم زیر را تکمیل کنید تا کارشناسان ما در سریع‌ترین زمان ممکن با شما تماس بگیرند.</p>
                        
                        <div class="lk-form-group">
                            <input type="text" name="lk_name" placeholder="نام و نام خانوادگی" required>
                        </div>
                        
                        <div class="lk-form-group">
                            <input type="email" name="lk_email" placeholder="ایمیل سازمانی یا شخصی" required>
                        </div>

                        <div class="lk-form-group">
                            <input type="text" name="lk_subject" placeholder="موضوع پیام" required>
                        </div>
                        
                        <div class="lk-form-group">
                            <textarea name="lk_message" rows="4" placeholder="متن پیام خود را بنویسید..." required></textarea>
                        </div>
                        
                        <button type="submit" class="lk-submit-btn">ارسال پیام</button>
                    </form>
                </div>

                <!-- اطلاعات تماس و کارت گارانتی -->
                <div class="lk-contact-info-col">
                    
                    <!-- کارت دفتر مرکزی -->
                    <div class="lk-contact-card lk-card-primary lk-reveal-item lk-delay-1">
                        <div class="lk-feature-icon">📍</div>
                        <h3 class="lk-feature-title">دفتر مرکزی</h3>
                        <p class="lk-feature-desc">تهران - خیابان سرچشمه - سه راه امین حضور - پاساژ امام جواد - طبقه منفی همکف - پلاک 13</p>
                    </div>

                    <!-- کارت تلفن و ساعات پاسخگویی -->
                    <div class="lk-contact-card lk-card-secondary lk-reveal-item lk-delay-2">
                        <div class="lk-feature-icon">📞</div>
                        <h3 class="lk-feature-title">تلفن‌های پشتیبانی</h3>
                        <div class="lk-phone-numbers">
                            <a href="tel:02136876253" class="lk-phone-link">02136876253</a>
                            <a href="tel:09394008214" class="lk-phone-link">09394008214</a>
                        </div>
                        <div class="lk-support-status">
                            <span class="lk-status-dot"></span>
                            همه روزه (۹ الی ۱۷) - پشتیبانی آنلاین فعال است
                        </div>
                    </div>

                    <!-- کارت ایمیل -->
                    <div class="lk-contact-card lk-card-primary lk-reveal-item lk-delay-3">
                        <div class="lk-feature-icon">✉️</div>
                        <h3 class="lk-feature-title">پشتیبانی الکترونیک</h3>
                        <p class="lk-feature-desc">info@liverakala.com</p>
                    </div>

                    <!-- کارت گارانتی -->
                    <div class="lk-contact-card lk-warranty-card lk-reveal-item lk-delay-4">
                        <div class="lk-feature-icon lk-warranty-icon">🛡️</div>
                        <h3 class="lk-feature-title">گارانتی و ضمانت پاسخگویی سریع</h3>
                        <p class="lk-feature-desc">تمامی درخواست‌ها و تیکت‌ها در کمتر از ۲ ساعت کاری توسط تیم تخصصی لیوراکالا بررسی و پاسخ داده می‌شوند.</p>
                    </div>

                </div>

            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('liverakala_contact', 'liverakala_contact_page_shortcode');