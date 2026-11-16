<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ۱. ثبت شورت‌کد با قابلیت تشخیص نوع دستگاه (دسکتاپ یا موبایل)
add_shortcode('lk_live_search_box', function($atts) {
    $atts = shortcode_atts([
        'device' => 'desktop' // پیش‌فرض دسکتاپ
    ], $atts);

    $device_id = sanitize_html_class($atts['device']);
    
    ob_start();
    ?>
    <div class="lk-modern-search-wrapper lk-search-<?php echo esc_attr($device_id); ?>">
        <div class="lk-search-input-container">
            <!-- آیکون سرچ -->
            <svg class="lk-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            
            <!-- اینپوت جستجو با کلاس مشترک -->
            <input type="text" class="lk-live-search-input" placeholder="جستجوی پیشرفته محصول، برند..." autocomplete="off">
            
            <!-- دکمه پاک کردن (ضربدر) -->
            <button type="button" class="lk-clear-search lk-clear-btn" style="display: none;">&times;</button>
            
            <!-- اسپینر لودینگ -->
            <div class="lk-search-spinner lk-spinner" style="display: none;"></div>
        </div>
        
        <!-- کانتینر نتایج زنده (دراپ‌داون) -->
        <div class="lk-live-results-dropdown lk-results-dropdown" style="display: none;"></div>
    </div>
    <?php
    return ob_get_clean();
});

// ۲. هندلر ایجکس لایو سرچ با الگوریتم امتیازدهی هوشمند (Relevance Ranking)
add_action('wp_ajax_lk_fast_live_search', 'lk_fast_live_search_callback');
add_action('wp_ajax_nopriv_lk_fast_live_search', 'lk_fast_live_search_callback');

function lk_fast_live_search_callback() {
    $keyword = sanitize_text_field($_GET['s'] ?? '');
    
    if (empty($keyword) || mb_strlen(trim($keyword)) < 2) {
        wp_send_json([
            'success' => false,
            'html'    => '<div class="lk-no-results">لطفاً حداقل ۲ کاراکتر برای جستجو وارد کنید.</div>'
        ]);
    }

    global $wpdb;

    $like_keyword   = '%' . $wpdb->esc_like( $keyword ) . '%';
    $starts_keyword = $wpdb->esc_like( $keyword ) . '%';

    /**
     * امتیازدهی هوشمند SQL:
     * 1 -> شروع عنوان محصول با عبارت جستجو شده (اولویت بالاتر)
     * 2 -> وجود عبارت جستجو شده در بخش‌های دیگر عنوان
     */
    $query = $wpdb->prepare( "
        SELECT ID, post_title 
        FROM {$wpdb->posts} 
        WHERE post_type = 'product' 
          AND post_status = 'publish' 
          AND post_title LIKE %s
        ORDER BY 
          CASE 
            WHEN post_title LIKE %s THEN 1
            ELSE 2
          END ASC,
          post_date DESC
        LIMIT 6
    ", $like_keyword, $starts_keyword );

    $results = $wpdb->get_results( $query );

    $html = '';

    if (!empty($results)) {
        foreach ($results as $post) {
            $product = wc_get_product($post->ID);
            if (!$product) {
                continue;
            }
            
            $thumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: wc_placeholder_img_src();
            
            $html .= sprintf(
                '<a href="%s" class="lk-result-item">
                    <img src="%s" alt="%s" class="lk-result-image" loading="lazy">
                    <div class="lk-result-info">
                        <span class="lk-result-title">%s</span>
                        <span class="lk-result-price">%s</span>
                    </div>
                </a>',
                esc_url(get_permalink($post->ID)),
                esc_url($thumbnail),
                esc_attr($post->post_title),
                esc_html($post->post_title),
                $product->get_price_html()
            );
        }

        // هدایت مستقیم و استاندارد به صفحه جستجو با پارامترهای اصلی وردپرس
        $archive_url = add_query_arg([
            's'         => urlencode($keyword),
            'post_type' => 'product'
        ], home_url('/'));
        
        $html .= sprintf(
            '<div class="lk-view-all"><a href="%s">مشاهده همه نتایج برای «%s»</a></div>',
            esc_url($archive_url),
            esc_html($keyword)
        );

    } else {
        $html = '<div class="lk-no-results">محصولی با این مشخصات پیدا نشد.</div>';
    }

    wp_send_json([
        'success' => true,
        'html'    => $html
    ]);
}

// ۳. اسکریپت جاوااسکریپت برای مدیریت باکس‌ها
add_action( 'wp_footer', function() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchWrappers = document.querySelectorAll('.lk-modern-search-wrapper');

        searchWrappers.forEach(wrapper => {
            const input = wrapper.querySelector('.lk-live-search-input');
            const clearBtn = wrapper.querySelector('.lk-clear-search');
            const spinner = wrapper.querySelector('.lk-search-spinner');
            const dropdown = wrapper.querySelector('.lk-live-results-dropdown');
            
            let typingTimer;
            const doneTypingInterval = 300;

            if (!input) return;

            input.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const keyword = input.value.trim();

                if (keyword.length > 0) {
                    clearBtn.style.display = 'flex';
                } else {
                    clearBtn.style.display = 'none';
                    dropdown.style.display = 'none';
                    spinner.style.display = 'none';
                    return;
                }

                if (keyword.length < 2) {
                    dropdown.style.display = 'none';
                    spinner.style.display = 'none';
                    return;
                }

                spinner.style.display = 'block';

                typingTimer = setTimeout(() => {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=lk_fast_live_search&s=' + encodeURIComponent(keyword))
                        .then(response => response.json())
                        .then(data => {
                            spinner.style.display = 'none';
                            if (data.success) {
                                dropdown.innerHTML = data.html;
                                dropdown.style.display = 'block';
                            } else {
                                dropdown.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            spinner.style.display = 'none';
                            console.error('Error:', error);
                        });
                }, doneTypingInterval);
            });

            clearBtn.addEventListener('click', function() {
                input.value = '';
                clearBtn.style.display = 'none';
                dropdown.style.display = 'none';
                spinner.style.display = 'none';
                input.focus();
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        });

        <?php if ( (is_post_type_archive('product') || is_search()) && isset($_GET['s']) && !empty($_GET['s']) ) : ?>
        const searchQuery = "<?php echo esc_js( sanitize_text_field($_GET['s']) ); ?>";
        const observer = new MutationObserver(function(mutations, obs) {
            const titleElements = document.querySelectorAll('h1, h2, h3, .page-title, .archive-title, .woocommerce-products-header__title');
            titleElements.forEach(el => {
                let text = el.innerText.trim().toLowerCase();
                if (text.includes('search results') || text.includes('archive') || text.includes('products')) {
                    el.innerText = 'نتایج جستجو برای: ' + searchQuery;
                    obs.disconnect();
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
        <?php endif; ?>
    });
    </script>
    <?php
});

// ۴. گارد امنیتی قوی برای اجبار کوئری به نمایش محصولات جستجو‌شده حتی در صفحات سفارشی
add_action('pre_get_posts', function($query) {
    if (!is_admin() && isset($_GET['s']) && !empty($_GET['s'])) {
        $search_term = sanitize_text_field($_GET['s']);
        $query->set('s', $search_term);
        $query->set('post_type', 'product');
        $query->set('post_status', 'publish');
    }
});

add_action('woocommerce_product_query', function($q) {
    if (!is_admin() && isset($_GET['s']) && !empty($_GET['s'])) {
        $search_term = sanitize_text_field($_GET['s']);
        $q->set('s', $search_term);
        $q->set('post_type', 'product');
    }
});