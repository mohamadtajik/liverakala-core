<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', function() {
    if ( shortcode_exists( 'dynamic_mega_menu' ) ) {
        remove_shortcode( 'dynamic_mega_menu' );
    }

    add_shortcode( 'dynamic_mega_menu', function() {
        try {
            if ( ! class_exists( 'WooCommerce' ) ) {
                return '';
            }

             // ۱. دریافت تمامی دسته‌های مادر
            $all_parents = get_terms( array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'parent'     => 0,
                'orderby'    => 'name',
                'order'      => 'ASC'
            ) );

            if ( empty( $all_parents ) || is_wp_error( $all_parents ) ) {
                $all_parents = array();
            }

            $parents_with_subs = array();
            $parents_without_subs = array();

            foreach ( $all_parents as $parent_cat ) {
                if ( ! is_a( $parent_cat, 'WP_Term' ) ) continue;

                if ( trim( $parent_cat->name ) === 'کادو روز پدر' ) {
                    continue;
                }

                $sub_categories = get_terms( array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                    'parent'     => $parent_cat->term_id,
                    'orderby'    => 'name',
                    'order'      => 'ASC'
                ) );

                $has_subs = ( ! empty( $sub_categories ) && ! is_wp_error( $sub_categories ) );

                $item_data = array(
                    'term'     => $parent_cat,
                    'subs'     => $has_subs ? $sub_categories : array(),
                    'has_subs' => $has_subs
                );

                if ( $has_subs ) {
                    $parents_with_subs[] = $item_data;
                } else {
                    $parents_without_subs[] = $item_data;
                }
            }

            $ordered_categories = array_merge( $parents_with_subs, $parents_without_subs );

            // ۲. دریافت برندها
            $berocket_taxonomies = array( 'berocket_brand', 'product_brand', 'br_product_brand', 'brands' );
            $brand_terms = array();

            foreach ( $berocket_taxonomies as $tax ) {
                if ( taxonomy_exists( $tax ) ) {
                    $get_brands = get_terms( array(
                        'taxonomy'   => $tax,
                        'hide_empty' => false,
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                    ) );

                    if ( ! empty( $get_brands ) && ! is_wp_error( $get_brands ) ) {
                        $brand_terms = $get_brands;
                        break;
                    }
                }
            }

            // ۳. لینک‌های سفارشی
            $blog_url   = 'https://liverakala.com/blog/';
            $about_url  = 'https://liverakala.com/%d8%af%d8%b1%d8%a8%d8%a7%d8%b1%d9%87-%d9%85%d8%a7/';
            $contact_page = get_page_by_path( 'contact-us' ) ?: get_page_by_path( 'contact' );
            $contact_url  = $contact_page ? get_permalink( $contact_page->ID ) : home_url( '/contact-us/' );

            ob_start();
            ?>
            <div class="nav-categories-wrapper">
                
                <div class="mobile-overlay" id="js-menu-overlay"></div>

                <div class="mega-menu-trigger-container">
                    <div class="cat-trigger-btn" id="js-menu-trigger" role="button" tabindex="0">
                        <div class="burger-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="cat-trigger-text">دسته‌ بندی محصولات</span>
                    </div>

                    <div class="mega-menu-dropdown" id="js-menu-dropdown">
                        <div class="mobile-menu-header">
                            <span class="mobile-menu-title">منوی دسته‌ بندی‌</span>
                            <span class="mobile-menu-close" id="js-menu-close">&times;</span>
                        </div>

                        <ul class="parent-categories-column">
                            <?php 
                            $is_first = true;
                            foreach ( $ordered_categories as $item ) : 
                                $parent_cat     = $item['term'];
                                $sub_categories = $item['subs'];
                                $has_subs       = $item['has_subs'];
                                $category_brands = $item['brands'];

                                $parent_link = get_term_link( $parent_cat );
                                if ( is_wp_error( $parent_link ) ) $parent_link = '#';

                                $active_class = $is_first ? ' active' : '';
                                $is_first = false;
                            ?>
                                <li class="parent-cat-item<?php echo $active_class; ?>">
                                    <a href="<?php echo esc_url( $parent_link ); ?>" class="parent-cat-link">
                                        <?php echo esc_html( $parent_cat->name ); ?>
                                    </a>

                                    <?php if ( $has_subs ) : ?>
                                        <div class="mobile-arrow-btn js-toggle-sub">
                                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </div>
                                        <div class="sub-categories-column js-sub-box">
                                            <div class="sub-cat-group">
                                                <a href="<?php echo esc_url( $parent_link ); ?>" class="sub-cat-title">
                                                    همه محصولات <?php echo esc_html( $parent_cat->name ); ?> &rsaquo;
                                                </a>

                                                <?php if ( ! empty( $category_brands ) ) : ?>
                                                    <div class="mega-menu-brands-wrap" style="margin-bottom: 15px;">
                                                        <div class="mega-brands-list" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                                            <?php foreach ( $category_brands as $brand ) : ?>
                                                                <a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="mega-brand-pill" style="background: #f8f9fa; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #444; text-decoration: none;">
                                                                    <?php echo esc_html( $brand->name ); ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <ul class="sub-cat-list">
                                                    <?php foreach ( $sub_categories as $sub_cat ) : 
                                                        if ( ! is_a( $sub_cat, 'WP_Term' ) ) continue;
                                                        $sub_link = get_term_link( $sub_cat );
                                                        if ( is_wp_error( $sub_link ) ) $sub_link = '#';
                                                    ?>
                                                        <li class="sub-cat-item-card">
                                                            <a href="<?php echo esc_url( $sub_link ); ?>" class="sub-cat-link">
                                                                <?php echo esc_html( $sub_cat->name ); ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="sub-categories-column js-sub-box">
                                            <div class="sub-cat-group">
                                                <a href="<?php echo esc_url( $parent_link ); ?>" class="sub-cat-title">
                                                    مشاهده تمام محصولات <?php echo esc_html( $parent_cat->name ); ?> &rsaquo;
                                                </a>
                                                <?php if ( ! empty( $category_brands ) ) : ?>
                                                    <div class="mega-menu-brands-wrap" style="margin-bottom: 15px;">
                                                        <div class="mega-brands-list" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                                            <?php foreach ( $category_brands as $brand ) : ?>
                                                                <a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="mega-brand-pill" style="background: #f8f9fa; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #444; text-decoration: none;">
                                                                    <?php echo esc_html( $brand->name ); ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                             <!-- برندها -->
                            <li class="parent-cat-item brand-cat-item">
                                <a href="#" class="parent-cat-link">
                                    <span>برندها</span>
                                </a>
                                <div class="mobile-arrow-btn js-toggle-sub">
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>

                                <div class="sub-categories-column brand-sub-column js-sub-box">
                                    <div class="sub-cat-group">
                                        <span class="sub-cat-title">همه برندها</span>
                                        <?php if ( ! empty( $brand_terms ) ) : ?>
                                            <ul class="sub-cat-list brand-grid-list">
                                                <?php foreach ( $brand_terms as $brand ) : 
                                                    $b_link = get_term_link( $brand );
                                                    if ( is_wp_error( $b_link ) ) $b_link = '#';
                                                ?>
                                                    <li class="sub-cat-item-card">
                                                        <a href="<?php echo esc_url( $b_link ); ?>" class="sub-cat-link brand-link">
                                                            <?php echo esc_html( $brand->name ); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else : ?>
                                            <p class="no-sub-msg">برندی یافت نشد.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <!-- لینک‌های اضافی مخصوص نمایش در انتهای منوی موبایل -->
                        <div class="mobile-extra-links">
                            <a href="<?php echo esc_url( $blog_url ); ?>" class="mobile-single-link">
                                <span class="link-content-left">
                                    <span class="icon-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                    </span>
                                    <span>بلاگ</span>
                                </span>
                            </a>
                            <a href="<?php echo esc_url( $about_url ); ?>" class="mobile-single-link">
                                <span class="link-content-left">
                                    <span class="icon-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    </span>
                                    <span>درباره ما</span>
                                </span>
                            </a>
                            <a href="<?php echo esc_url( $contact_url ); ?>" class="mobile-single-link">
                                <span class="link-content-left">
                                    <span class="icon-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </span>
                                    <span>تماس با لیوراکالا</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- لینک‌های کناری هدر (مخصوص دسکتاپ) -->
                <div class="nav-links-container">
                    <a href="<?php echo esc_url( $blog_url ); ?>" class="nav-single-link">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg></span>
                        <span>بلاگ</span>
                    </a>
                    <a href="<?php echo esc_url( $about_url ); ?>" class="nav-single-link">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></span>
                        <span>درباره ما</span>
                    </a>
                    <a href="<?php echo esc_url( $contact_url ); ?>" class="nav-single-link">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
                        <span>تماس با لیوراکالا</span>
                    </a>
                </div>

            </div>
            <?php
            return ob_get_clean();

        } catch ( Exception $e ) {
            return '';
        }
    } );
} );