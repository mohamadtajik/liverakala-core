<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode('liverakala_archive_products', 'liverakala_render_archive_products_grid');

function liverakala_render_archive_products_grid($atts) {
    ob_start();

    // خواندن پارامترها از URL
    $raw_brands = isset($_GET['lk_brand']) ? sanitize_text_field($_GET['lk_brand']) : '';
    $selected_brand_ids = array_filter(array_map('intval', explode(',', $raw_brands)));
    $current_sort = isset($_GET['lk_sort']) ? sanitize_text_field($_GET['lk_sort']) : 'menu_order';
    
    $price_bounds = liverakala_get_min_max_prices();
    $min_limit = $price_bounds['min'];
    $max_limit = $price_bounds['max'];

    $param_min = (isset($_GET['lk_min_price']) && $_GET['lk_min_price'] !== '') ? intval($_GET['lk_min_price']) : $min_limit;
    $param_max = (isset($_GET['lk_max_price']) && $_GET['lk_max_price'] !== '') ? intval($_GET['lk_max_price']) : $max_limit;
    
    $has_price_filter = ($param_min > $min_limit) || ($param_max < $max_limit);
    $min_price = $param_min;
    $max_price = $param_max;

    $query_data = liverakala_get_archive_products_query($selected_brand_ids, $current_sort, $min_price, $max_price, $has_price_filter);
    $query = $query_data['query'];
    $paged = $query_data['paged'];

    $brands = array();
    $subcategories = array();
    $current_term = (is_product_taxonomy()) ? get_queried_object() : null;

    if ($current_term && isset($current_term->term_id)) {
        $subcategories = get_terms(array(
            'taxonomy'   => $current_term->taxonomy,
            'parent'     => $current_term->term_id,
            'hide_empty' => true,
        ));

        $term_products = get_posts(array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => $current_term->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $current_term->term_id,
                ),
            ),
        ));

        if (!empty($term_products)) {
            $brands = get_terms(array(
                'taxonomy'   => 'berocket_brand',
                'hide_empty' => true,
                'object_ids' => $term_products,
            ));
        }
    } else {
        $brands = get_terms(array(
            'taxonomy'   => 'berocket_brand',
            'hide_empty' => true,
        ));
    }

    $sort_labels = array(
        'menu_order' => 'پیش فرض',
        'date'       => 'جدید ترین',
        'price'      => 'ارزان ترین',
        'price-desc' => 'گران ترین'
    );
    $current_sort_label = ($current_sort === 'menu_order') ? 'مرتب سازی' : (isset($sort_labels[$current_sort]) ? $sort_labels[$current_sort] : 'مرتب سازی');

    echo '<div class="lk-shop-layout-wrapper" id="lk-shop-wrapper">';

    // ۱. نوار زیردسته‌ها (به عنوان فرزند مستقیم کانتینر اصلی)
    if ( ! empty( $subcategories ) && ! is_wp_error( $subcategories ) ) {
        echo '<div class="lk-subcategories-wrapper">';
        echo '<div class="lk-subcategories-scroll">';
        foreach ( $subcategories as $subcat ) {
            $subcat_link = get_term_link( $subcat );
            echo '<a href="' . esc_url( $subcat_link ) . '" class="lk-subcategory-pill">' . esc_html( $subcat->name ) . '</a>';
        }
        echo '</div>';
        echo '</div>';
    }

    // ۲. دکمه‌های موبایل
    echo '<div class="lk-mobile-filter-toggle-wrap">';
    echo '<button type="button" class="lk-mobile-filter-btn" id="lk-open-filter-modal">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-left: 4px;"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>';
    echo '<span>فیلتر</span>';
    echo '</button>';

    echo '<button type="button" class="lk-mobile-sort-btn" id="lk-open-sort-modal">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-left: 4px;"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
    echo '<span id="lk-mobile-sort-btn-text">' . esc_html($current_sort_label) . '</span>';
    echo '</button>';
    echo '</div>';

    // ۳. سایدبار فیلتر
    echo '<aside class="lk-filter-sidebar" id="lk-filter-sidebar">';
    echo '<div class="lk-filter-box">';
    
    echo '<div class="lk-filter-header">';
    echo '<h3 class="lk-filter-title">فیلتر محصولات</h3>';
    echo '<button type="button" class="lk-close-filter-modal" id="lk-close-filter-modal">&times;</button>';
    
    $has_active_filters = !empty($selected_brand_ids) || $has_price_filter || ($current_sort !== 'menu_order');
    if ($has_active_filters) {
        $clear_url = remove_query_arg(array('lk_brand', 'lk_min_price', 'lk_max_price', 'lk_sort', 'paged'));
        echo '<a href="' . esc_url($clear_url) . '" class="lk-clear-filters-btn" id="lk-clear-filters">پاکسازی همه</a>';
    }
    echo '</div>';

    // فیلتر برند
    echo '<div class="lk-filter-section">';
    echo '<h4 class="lk-section-subtitle">فیلتر برند</h4>';
    if (!is_wp_error($brands) && !empty($brands)) {
        echo '<ul class="lk-brand-filter-list">';
        foreach ($brands as $brand) {
            $is_checked = in_array($brand->term_id, $selected_brand_ids);
            echo '<li class="lk-brand-item">';
            echo '<label class="lk-checkbox-label">';
            echo '<input type="checkbox" class="lk-brand-checkbox" value="' . esc_attr($brand->term_id) . '" ' . ($is_checked ? 'checked="checked"' : '') . '>';
            echo '<span class="lk-brand-name">' . esc_html($brand->name) . '</span>';
            echo '</label>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="lk-no-brands">برندی در این دسته وجود ندارد.</p>';
    }
    echo '</div>';

    // فیلتر قیمت
    echo '<div class="lk-filter-section" style="margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">';
    echo '<h4 class="lk-section-subtitle">فیلتر قیمت</h4>';
    echo '<div class="lk-price-slider-container">';
    echo '<div class="lk-price-inputs-display">';
    echo '<span>از <strong id="lk-min-val-text">' . number_format($min_price) . '</strong> تومان</span>';
    echo '<span>تا <strong id="lk-max-val-text">' . number_format($max_price) . '</strong> تومان</span>';
    echo '</div>';
    echo '<div class="lk-range-slider-wrap">';
    echo '<input type="range" id="lk-range-min" name="lk_min_price" min="' . esc_attr($min_limit) . '" max="' . esc_attr($max_limit) . '" step="50000" value="' . esc_attr($min_price) . '">';
    echo '<input type="range" id="lk-range-max" name="lk_max_price" min="' . esc_attr($min_limit) . '" max="' . esc_attr($max_limit) . '" step="50000" value="' . esc_attr($max_price) . '">';
    echo '</div>';
    
    if ($has_price_filter) {
        echo '<div class="lk-price-action-row" style="margin-top: 10px;">';
        echo '<button type="button" id="lk-clear-price" class="lk-clear-price-btn">حذف فیلتر قیمت</button>';
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</aside>';
    echo '<div class="lk-filter-overlay" id="lk-filter-overlay"></div>';

    // مودال مرتب‌سازی موبایل
    echo '<div class="lk-sort-modal" id="lk-sort-modal">';
    echo '<div class="lk-sort-modal-content">';
    echo '<div class="lk-filter-header"><h3 class="lk-filter-title">مرتب سازی بر اساس</h3><button type="button" class="lk-close-sort-modal" id="lk-close-sort-modal">&times;</button></div>';
    echo '<ul class="lk-sort-options-list">';
    echo '<li><a href="#" data-sort="menu_order" class="' . ($current_sort == 'menu_order' ? 'active' : '') . '">پیش فرض</a></li>';
    echo '<li><a href="#" data-sort="date" class="' . ($current_sort == 'date' ? 'active' : '') . '">جدید ترین</a></li>';
    echo '<li><a href="#" data-sort="price" class="' . ($current_sort == 'price' ? 'active' : '') . '">ارزان ترین</a></li>';
    echo '<li><a href="#" data-sort="price-desc" class="' . ($current_sort == 'price-desc' ? 'active' : '') . '">گران ترین</a></li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    // ۴. گرید محصولات و محتوای اصلی (بدون زیردسته‌ها در داخلش)
    echo '<main class="lk-shop-main-content">';
    echo '<div class="lk-desktop-sort-bar">';
    echo '<span class="lk-sort-label">مرتب سازی بر اساس:</span>';
    echo '<div class="lk-desktop-sort-options">';
    echo '<a href="#" data-sort="menu_order" class="lk-sort-pill ' . ($current_sort == 'menu_order' ? 'active' : '') . '">پیش فرض</a>';
    echo '<a href="#" data-sort="date" class="lk-sort-pill ' . ($current_sort == 'date' ? 'active' : '') . '">جدید ترین</a>';
    echo '<a href="#" data-sort="price" class="lk-sort-pill ' . ($current_sort == 'price' ? 'active' : '') . '">ارزان ترین</a>';
    echo '<a href="#" data-sort="price-desc" class="lk-sort-pill ' . ($current_sort == 'price-desc' ? 'active' : '') . '">گران ترین</a>';
    echo '</div>';
    echo '</div>';

    echo '<div id="lk-products-container">';
    echo '<div class="lk-archive-products-grid" id="lk-archive-grid">';
    liverakala_render_products_inner_grid($query, $paged);
    echo '</div>';

    $big = 999999999;
    $pagination = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, $paged),
        'total'     => $query->max_num_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'list',
    ));

    if ($pagination) {
        echo '<nav class="lk-pagination-wrap">' . $pagination . '</nav>';
    }
    echo '</div>';

    echo '</main>';
    echo '</div>';

    return ob_get_clean();
}

function liverakala_get_min_max_prices() {
    global $wpdb;
    $current_term = (is_product_taxonomy()) ? get_queried_object() : null;

    if ($current_term && isset($current_term->term_id)) {
        $term_ids = get_term_children($current_term->term_id, $current_term->taxonomy);
        $term_ids[] = $current_term->term_id;
        $term_ids_sql = implode(',', array_map('intval', $term_ids));

        $sql = "
            SELECT MIN(CAST(pm.meta_value AS DECIMAL(15,2))) as min_price, 
                   MAX(CAST(pm.meta_value AS DECIMAL(15,2))) as max_price
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE p.post_type IN ('product', 'product_variation') 
              AND p.post_status = 'publish' 
              AND pm.meta_key = '_price' 
              AND pm.meta_value != '' 
              AND pm.meta_value > 0
              AND tt.term_id IN ($term_ids_sql)
        ";
    } else {
        $sql = "
            SELECT MIN(CAST(pm.meta_value AS DECIMAL(15,2))) as min_price, 
                   MAX(CAST(pm.meta_value AS DECIMAL(15,2))) as max_price
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type IN ('product', 'product_variation') 
              AND p.post_status = 'publish' 
              AND pm.meta_key = '_price' 
              AND pm.meta_value != '' 
              AND pm.meta_value > 0
        ";
    }

    $results = $wpdb->get_row($sql);
    
    $min = (isset($results->min_price) && $results->min_price !== null) ? floor($results->min_price) : 0;
    $max = (isset($results->max_price) && $results->max_price !== null) ? ceil($results->max_price) : 10000000;

    if ($min >= $max) {
        $max = $min + 1000000;
    }

    return array('min' => $min, 'max' => $max);
}

function liverakala_get_archive_products_query($selected_brand_ids = array(), $sort = 'menu_order', $min_price = 0, $max_price = 0, $has_price_filter = false, $paged = 1) {
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : (get_query_var('paged') ? get_query_var('paged') : 1);

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    if ($sort == 'date') {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    } elseif ($sort == 'price') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'ASC';
    } elseif ($sort == 'price-desc') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'DESC';
    }

    $tax_query = array('relation' => 'AND');

    if (is_product_taxonomy()) {
        $term = get_queried_object();
        if ($term && isset($term->taxonomy)) {
            $tax_query[] = array(
                'taxonomy' => $term->taxonomy,
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            );
        }
    }

    if (!empty($selected_brand_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'berocket_brand',
            'field'    => 'term_id',
            'terms'    => $selected_brand_ids,
            'operator' => 'IN',
        );
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    if ($has_price_filter) {
        $args['meta_query'] = array(
            array(
                'key'     => '_price',
                'value'   => array($min_price, $max_price),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ),
        );
    }

    return array(
        'query' => new WP_Query($args),
        'paged' => $paged
    );
}

function liverakala_render_products_inner_grid($query, $paged) {
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            
            $product_permalink = $product->get_permalink();
            $product_image     = $product->get_image('medium');
            $product_title     = $product->get_name();
            $product_price     = $product->get_price_html();
            ?>
            <div class="lk-archive-product-card">
                <a href="<?php echo esc_url($product_permalink); ?>" class="lk-prod-link">
                    <div class="lk-prod-image-wrap" style="position: relative;">
                        <?php 
                        // بررسی تخفیف و نمایش تگ با کلاس صفحه اصلی
                        if ( $product->is_on_sale() ) {
                            $regular_price = (float) $product->get_regular_price();
                            $sale_price    = (float) $product->get_sale_price();
                            if ( $regular_price > 0 ) {
                                $percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
                                echo '<span class="liverakala-discount-badge">' . $percentage . '% تخفیف</span>';
                            }
                        }
                        echo $product_image; 
                        ?>
                    </div>
                    <h3 class="lk-prod-title"><?php echo esc_html($product_title); ?></h3>
                    <div class="lk-prod-price-wrap">
                        <?php echo $product_price; ?>
                    </div>
                </a>
            </div>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo '<div class="lk-no-products-box" style="grid-column: 1 / -1;"><p>هیچ محصولی با این مشخصات پیدا نشد.</p></div>';
    }
}