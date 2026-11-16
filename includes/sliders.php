<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * ==================================================
 * ۱. ثبت پست تایپ بنرهای لیوراکالا
 * ==================================================
 */
function liverakala_register_banner_post_type() {
    if ( post_type_exists( 'liverakala_banner' ) ) {
        return;
    }
    $args = array(
        'labels' => array(
            'name'          => 'بنرهای لیوراکالا',
            'singular_name' => 'بنر'
        ),
        'public'        => true,
        'has_archive'   => false,
        'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
        'menu_icon'     => 'dashicons-images-alt2',
    );
    register_post_type( 'liverakala_banner', $args );
}
add_action( 'init', 'liverakala_register_banner_post_type' );


/**
 * ==================================================
 * ۲. متا باکس تنظیمات تصویر موبایل و لینک بنر
 * ==================================================
 */
function liverakala_banner_add_metaboxes() {
    add_meta_box(
        'liverakala_banner_details',
        'تنظیمات تصاویر و لینک بنر',
        'liverakala_banner_metabox_callback',
        'liverakala_banner',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'liverakala_banner_add_metaboxes' );

function liverakala_banner_metabox_callback( $post ) {
    wp_nonce_field( 'liverakala_save_banner_meta', 'liverakala_banner_nonce' );
    $link = get_post_meta( $post->ID, '_liverakala_banner_link', true );
    $mobile_img_id = get_post_meta( $post->ID, '_liverakala_banner_mobile_img', true );
    $mobile_img_url = $mobile_img_id ? wp_get_attachment_image_url( $mobile_img_id, 'full' ) : '';
    ?>
    <p>
        <label><strong>لینک مقصد بنر (URL):</strong></label><br>
        <input type="text" name="liverakala_banner_link" value="<?php echo esc_attr( $link ); ?>" style="width:100%; margin-top:5px;" placeholder="https://...">
    </p>
    <p>
        <label><strong>تصویر مخصوص موبایل:</strong> (تصویر شاخص همین پست برای دسکتاپ و ویندوز استفاده می‌شود)</label><br>
        <div id="liverakala-mobile-img-container" style="margin: 10px 0;">
            <?php if ( $mobile_img_url ) : ?>
                <img src="<?php echo esc_url( $mobile_img_url ); ?>" style="max-width: 200px; display: block; border-radius: 6px; border: 1px solid #ccc;">
            <?php endif; ?>
        </div>
        <input type="hidden" id="liverakala_banner_mobile_img" name="liverakala_banner_mobile_img" value="<?php echo esc_attr( $mobile_img_id ); ?>">
        <button type="button" class="button" id="liverakala_upload_mobile_btn">انتخاب تصویر موبایل</button>
        <button type="button" class="button" id="liverakala_remove_mobile_btn" style="color: red;">حذف تصویر</button>
    </p>
    <script>
    jQuery(document).ready(function($){
        var mediaUploader;
        $('#liverakala_upload_mobile_btn').click(function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media({
                title: 'انتخاب تصویر موبایل',
                button: { text: 'استفاده از این تصویر' },
                multiple: false
            });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#liverakala_banner_mobile_img').val(attachment.id);
                $('#liverakala-mobile-img-container').html('<img src="' + attachment.url + '" style="max-width: 200px; display: block; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc;">');
            });
            mediaUploader.open();
        });
        $('#liverakala_remove_mobile_btn').click(function(e) {
            e.preventDefault();
            $('#liverakala_banner_mobile_img').val('');
            $('#liverakala-mobile-img-container').html('');
        });
    });
    </script>
    <?php
}

function liverakala_save_banner_meta( $post_id ) {
    if ( ! isset( $_POST['liverakala_banner_nonce'] ) || ! wp_verify_nonce( $_POST['liverakala_banner_nonce'], 'liverakala_save_banner_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['liverakala_banner_link'] ) ) {
        update_post_meta( $post_id, '_liverakala_banner_link', esc_url_raw( $_POST['liverakala_banner_link'] ) );
    }
    if ( isset( $_POST['liverakala_banner_mobile_img'] ) ) {
        update_post_meta( $post_id, '_liverakala_banner_mobile_img', sanitize_text_field( $_POST['liverakala_banner_mobile_img'] ) );
    }
}
add_action( 'save_post', 'liverakala_save_banner_meta' );


/**
 * ==================================================
 * ۳. تابع کمکی رندر کارت محصول
 * ==================================================
 */
if ( ! function_exists( 'liverakala_render_product_item' ) ) {
    function liverakala_render_product_item( $product ) {
        $discount_percentage = '';
        $is_onsale = $product->is_on_sale();

        if ( $is_onsale && $product->is_type( 'simple' ) ) {
            $regular_price = (float) $product->get_regular_price();
            $sale_price    = (float) $product->get_sale_price();
            if ( $regular_price > 0 ) {
                $discount_percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
            }
        } elseif ( $is_onsale && $product->is_type( 'variable' ) ) {
            $percentage = 0;
            foreach ( $product->get_available_variations() as $variation ) {
                if ( $variation['display_regular_price'] > $variation['display_price'] ) {
                    $p = round( ( ( $variation['display_regular_price'] - $variation['display_price'] ) / $variation['display_regular_price'] ) * 100 );
                    if ( $p > $percentage ) { $percentage = $p; }
                }
            }
            if ( $percentage > 0 ) { $discount_percentage = $percentage; }
        }
        
        $item_class = $is_onsale ? 'liverakala-item liverakala-has-sale' : 'liverakala-item';

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $item_class ); ?>">
            <?php if ( $is_onsale && $discount_percentage ) : ?>
                <span class="liverakala-discount-badge"><?php echo $discount_percentage; ?>% تخفیف</span>
            <?php endif; ?>

            <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                <?php 
                if ( has_post_thumbnail( $product->get_id() ) ) {
                    echo get_the_post_thumbnail( $product->get_id(), 'medium', array( 
                        'loading' => 'lazy',
                        'width'   => '300',
                        'height'  => '300'
                    ) );
                } else {
                    echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="محصول" loading="lazy" width="300" height="300">';
                }
                ?>
            </a>
            
            <div class="liverakala-content-area">
                <h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>" style="text-decoration: none; color: inherit;"><?php echo esc_html( $product->get_name() ); ?></a></h3>
                
                <div class="liverakala-price-box">
                    <div class="price"><?php echo $product->get_price_html(); ?></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}


/**
 * ==================================================
 * ۴. شورت‌کد بنر
 * ==================================================
 */
function liverakala_banner_slider_shortcode() {
    $args = array(
        'post_type'      => 'liverakala_banner',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC'
    );
    
    $query = new WP_Query( $args );
    if ( ! $query->have_posts() ) return '';
    
    ob_start();
    $i = 0;
    ?>
    <style>
        .lk-banner-wrapper .lk-banner-btn { display: none !important; }
        @media (min-width: 768px) {
            .lk-banner-wrapper .lk-banner-btn { display: flex !important; }
        }
    </style>
    
    <div class="lk-banner-wrapper">
        <div class="lk-banner-track">
            <?php while ( $query->have_posts() ) : $query->the_post(); 
                $link = get_post_meta( get_the_ID(), '_liverakala_banner_link', true );
                $desktop_img_id = get_post_thumbnail_id( get_the_ID() );
                $mobile_img_id  = get_post_meta( get_the_ID(), '_liverakala_banner_mobile_img', true );
                if ( ! $mobile_img_id ) {
                    $mobile_img_id = $desktop_img_id;
                }

                $desktop_data = wp_get_attachment_image_src( $desktop_img_id, 'full' );
                $mobile_data  = wp_get_attachment_image_src( $mobile_img_id, 'full' );
                
                $desktop_url = $desktop_data ? $desktop_data[0] : '';
                $mobile_url  = $mobile_data ? $mobile_data[0] : '';
                $mobile_w    = $mobile_data ? $mobile_data[1] : '';
                $mobile_h    = $mobile_data ? $mobile_data[2] : '';
                
                $is_first = ( $i === 0 );
                $loading_attr = $is_first ? 'loading="eager" fetchpriority="high" data-no-lazy="1" skip-lazy="1"' : 'loading="lazy" data-no-lazy="0"';
                ?>
                <div class="lk-banner-slide">
                    <?php if ( $link ) : ?>
                        <a href="<?php echo esc_url( $link ); ?>">
                    <?php else : ?>
                        <div class="lk-slide-inner">
                    <?php endif; ?>
                    
                        <picture>
                            <source media="(min-width: 768px)" srcset="<?php echo esc_url( $desktop_url ); ?>">
                            <img src="<?php echo esc_url( $mobile_url ); ?>" 
                                 width="<?php echo esc_attr( $mobile_w ); ?>" 
                                 height="<?php echo esc_attr( $mobile_h ); ?>" 
                                 alt="<?php echo esc_attr( get_the_title() ); ?>" 
                                 class="lk-banner-img"
                                 <?php echo $loading_attr; ?>>
                        </picture>

                    <?php if ( $link ) : ?>
                        </a>
                    <?php else : ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                $i++;
            endwhile; wp_reset_postdata(); ?>
        </div>

        <button class="lk-banner-btn lk-prev-btn" aria-label="Previous">&#10094;</button>
        <button class="lk-banner-btn lk-next-btn" aria-label="Next">&#10095;</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'liverakala_banner_slider', 'liverakala_banner_slider_shortcode' );


/**
 * ==================================================
 * ۵. شورت‌کد محصولات: [liverakala_slider] (ویرایش‌شده برای تایمر)
 * ==================================================
 */

function liverakala_universal_slider_func( $atts ) {
    $atts = shortcode_atts( array(
        'type'         => 'category', 
        'category'     => '',         
        'parent_cat'   => '',         
        'limit'        => 10,
        'title'        => '',
        'view_all_url' => '#',
        'rows'         => '1',      
        'ids'          => '', 
    ), $atts, 'liverakala_slider' );

    $container_id = 'liverakala-scroll-' . rand( 100, 999 );
    $row_class = ( $atts['rows'] == '2' ) ? 'liverakala-two-rows' : 'liverakala-one-row';
    $is_sale_box = ( $atts['type'] == 'sale' );
    $container_class = $is_sale_box ? 'liverakala-section-container liverakala-special-sale-box' : 'liverakala-section-container';

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $container_class ); ?>">
        <div class="liverakala-section-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h2 class="liverakala-section-title">
                        <?php if ( $is_sale_box ) echo '🔥'; ?> 
                        <?php echo esc_html( $atts['title'] ); ?>
                    </h2>
                <?php endif; ?>
            </div>
            
            <?php if ( $is_sale_box ) : ?>
            
                <div class="liverakala-countdown-badge" id="liverakala-sale-timer">
                    <span>پایان تخفیف:</span>
                    <span style="letter-spacing: 1px;" id="timer-counter">00:00:00</span>
                </div>
            <?php elseif ( ! empty( $atts['view_all_url'] ) && $atts['view_all_url'] !== '#' ) : ?>
                <a href="<?php echo esc_url( $atts['view_all_url'] ); ?>" class="liverakala-view-all">
                    مشاهده همه 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(180deg);"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            <?php endif; ?>
        </div>

        <div class="liverakala-slider-box">
            <div class="liverakala-horizontal-scroll <?php echo esc_attr( $row_class ); ?>" id="<?php echo esc_attr( $container_id ); ?>">
                <?php
                $query_args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => intval( $atts['limit'] ),
                    'post_status'    => 'publish',
                );

                if ( ! empty( $atts['ids'] ) ) {
                    $product_ids = array_map( 'trim', explode( ',', $atts['ids'] ) );
                    $query_args['post__in'] = $product_ids;
                    $query_args['orderby'] = 'post__in'; 
                } elseif ( $atts['type'] == 'sale' ) {
                    $query_args['post__in'] = wc_get_product_ids_on_sale();
                } elseif ( $atts['type'] == 'latest' ) {
                    $query_args['orderby'] = 'date';
                    $query_args['order']   = 'DESC';
                } elseif ( $atts['type'] == 'category' && ! empty( $atts['category'] ) ) {
                    $cat_val = $atts['category'];
                    $field_type = is_numeric( $cat_val ) ? 'term_id' : 'name';
                    $cat_val = ($field_type === 'term_id') ? intval( $cat_val ) : sanitize_text_field( $cat_val );

                    $query_args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field'    => $field_type,
                            'terms'    => $cat_val,
                        ),
                    );
                } elseif ( $atts['type'] == 'parent_cat_random' && ! empty( $atts['parent_cat'] ) ) {
                    $parent_val = $atts['parent_cat'];
                    $parent_field = is_numeric( $parent_val ) ? 'term_id' : 'name';
                    $parent_term = ($parent_field === 'term_id') ? get_term( intval( $parent_val ), 'product_cat' ) : get_term_by( 'name', sanitize_text_field( $parent_val ), 'product_cat' );

                    if ( $parent_term && ! is_wp_error( $parent_term ) ) {
                        $child_terms = get_terms( array(
                            'taxonomy'   => 'product_cat',
                            'parent'     => $parent_term->term_id,
                            'hide_empty' => true,
                        ) );
                        
                        $child_ids = array();
                        if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
                            foreach ( $child_terms as $child ) {
                                $child_ids[] = $child->term_id;
                            }
                        }
                        $child_ids[] = $parent_term->term_id;

                        $query_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $child_ids,
                                'operator' => 'IN',
                            ),
                        );
                        $query_args['orderby'] = 'rand';
                    }
                }

                $loop = new WP_Query( $query_args );

                if ( $loop->have_posts() ) {
                    while ( $loop->have_posts() ) {
                        $loop->the_post();
                        global $product;
                        echo liverakala_render_product_item( $product );
                    }
                } else {
                    echo '<p style="text-align:center; width:100%; padding:25px; color:#666;">محصولی یافت نشد.</p>';
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'liverakala_slider', 'liverakala_universal_slider_func' );

/**
 * ==================================================
 * ۶. شورت‌کد دسته‌بندی‌ها: [liverakala_categories]
 * ==================================================
 */
function liverakala_categories_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit'    => 8,
        'title'    => 'دسته‌بندی‌های محبوب',
        'ids'      => '', 
        'sub_only' => 'yes', 
        'rows'     => '1', 
    ), $atts, 'liverakala_categories' );

    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true, 
        'number'     => intval( $atts['limit'] ),
        'orderby'    => 'count', 
        'order'      => 'DESC',
    );

    if ( ! empty( $atts['ids'] ) ) {
        $ids_array = array_map( 'intval', explode( ',', $atts['ids'] ) );
        $args['include'] = $ids_array;
        $args['orderby'] = 'include';
    } elseif ( $atts['sub_only'] === 'yes' ) {
        $terms_raw = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
        ) );
        
        $sub_ids = array();
        if ( ! empty( $terms_raw ) && ! is_wp_error( $terms_raw ) ) {
            foreach ( $terms_raw as $t ) {
                if ( $t->parent != 0 ) {
                    $sub_ids[] = $t->term_id;
                }
            }
        }
        
        if ( ! empty( $sub_ids ) ) {
            $args['include'] = $sub_ids;
        }
    }

    $terms = get_terms( $args );
    $is_two_rows = ( $atts['rows'] == '2' );
    $grid_class  = $is_two_rows ? 'liverakala-cat-two-rows' : 'liverakala-cat-one-row';

    ob_start();

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
        ?>
        <div class="liverakala-section-container liverakala-cat-container">
            <?php if ( ! empty( $atts['title'] ) ) : ?>
                <div class="liverakala-section-header">
                    <h2 class="liverakala-section-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                </div>
            <?php endif; ?>

            <div class="liverakala-cat-grid <?php echo esc_attr( $grid_class ); ?>">
                <?php foreach ( $terms as $term ) : 
                    $thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
                    $term_link    = get_term_link( $term );
                    ?>
                    <a href="<?php echo esc_url( $term_link ); ?>" class="liverakala-cat-item">
                        <div class="liverakala-cat-img-box">
                            <?php if ( $thumbnail_id ) : ?>
                                <?php echo wp_get_attachment_image( $thumbnail_id, 'thumbnail', false, array(
                                    'alt'     => esc_attr( $term->name ),
                                    'loading' => 'lazy',
                                    'width'   => '100',
                                    'height'  => '100'
                                ) ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" width="100" height="100">
                            <?php endif; ?>
                        </div>
                        <span class="liverakala-cat-title"><?php echo esc_html( $term->name ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    endif;

    return ob_get_clean();
}
add_shortcode( 'liverakala_categories', 'liverakala_categories_shortcode' );
/**
 * ==================================================
 * شورت‌کد نمایش نظرات مشتریان: [liverakala_reviews]
 * ==================================================
 */
function liverakala_customer_reviews_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit' => 3, 
        'title' => 'نظرات خریداران لیوراکالا',
    ), $atts, 'liverakala_reviews' );

    $args = array(
        'status'      => 'approve',
        'post_type'   => 'product',
        'number'      => intval( $atts['limit'] ),
        'meta_key'    => 'rating',
        'meta_value'  => '5',
    );

    $comments = get_comments( $args );

    if ( empty( $comments ) ) {
        return '';
    }

    $gradient_palettes = array(
        'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)',
        'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
        'linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)',
    );

    ob_start();
    ?>
    <div class="liverakala-section-container lr-reviews-section">
        <?php if ( ! empty( $atts['title'] ) ) : ?>
            <div class="liverakala-section-header">
                <h2 class="liverakala-section-title">⭐ <?php echo esc_html( $atts['title'] ); ?></h2>
            </div>
        <?php endif; ?>

        <!-- کانتینر اسلایدر افقی در موبایل و گرید در دسکتاپ -->
        <div class="lr-reviews-scroll-container">
            <div class="lr-reviews-grid">
                <?php 
                $index = 0;
                foreach ( $comments as $comment ) : 
                    $product = wc_get_product( $comment->comment_post_ID );
                    if ( ! $product ) continue;
                    
                    $author_name = trim( $comment->comment_author );
                    $words = explode( ' ', $author_name );
                    $initials = '';
                    if ( count( $words ) >= 2 ) {
                        $initials = mb_substr( $words[0], 0, 1 ) . mb_substr( $words[1], 0, 1 );
                    } else {
                        $initials = mb_substr( $author_name, 0, 2 );
                    }
                    $initials = mb_strtoupper( $initials );

                    $bg_gradient = $gradient_palettes[ $index % count( $gradient_palettes ) ];
                    $index++;
                    ?>
                    <div class="lr-review-card">
                        <div class="lr-review-header">
                            <div class="lr-telegram-avatar" style="background: <?php echo esc_attr( $bg_gradient ); ?>;">
                                <span><?php echo esc_html( $initials ); ?></span>
                            </div>
                            <div class="lr-review-author-info">
                                <span class="lr-review-author-name"><?php echo esc_html( $comment->comment_author ); ?></span>
                                <span class="lr-review-verified">✓ خریدار تاییدشده</span>
                            </div>
                        </div>
                        
                        <div class="lr-review-stars">
                            <?php for ( $i = 0; $i < 5; $i++ ) : ?>
                                <span class="star">★</span>
                            <?php endfor; ?>
                        </div>

                        <p class="lr-review-content">
                            «<?php echo esc_html( wp_trim_words( $comment->comment_content, 20, '...' ) ); ?>»
                        </p>

                        <div class="lr-review-product-link">
                            <span>خریدار محصول:</span>
                            <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                                <?php echo esc_html( $product->get_name() ); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'liverakala_reviews', 'liverakala_customer_reviews_shortcode' );