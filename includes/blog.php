<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // جلوگیری از دسترسی مستقیم
}

/**
 * ۱. شورت‌کد اسلایدر بلاگ و مجله لیوراکالا
 * Shortcode: [liverakala_blog_slider]
 */
function liverakala_blog_slider_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit'        => 8,
        'title'        => 'تازه‌ترین‌های مجله',
        'view_all_url' => '', 
    ), $atts, 'liverakala_blog_slider' );

    $view_all_link = ! empty( $atts['view_all_url'] ) ? $atts['view_all_url'] : get_permalink( get_option( 'page_for_posts' ) );

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );
    
    $blog_query = new WP_Query( $args );

    ob_start();

    if ( $blog_query->have_posts() ) :
        ?>
        <div class="liverakala-section-container liverakala-blog-container">
            <div class="liverakala-section-header liverakala-blog-header">
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h2 class="liverakala-section-title liverakala-blog-section-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                <?php endif; ?>
                
                <?php if ( ! empty( $view_all_link ) ) : ?>
                    <a href="<?php echo esc_url( $view_all_link ); ?>" class="liverakala-view-all liverakala-blog-view-all">
                        مشاهده همه مقالات 
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(180deg);"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                <?php endif; ?>
            </div>

            <div class="liverakala-slider-box liverakala-blog-slider-box">
                <div class="liverakala-horizontal-scroll liverakala-blog-scroll">
                    <?php
                    while ( $blog_query->have_posts() ) :
                        $blog_query->the_post();
                        ?>
                        <div class="liverakala-item liverakala-blog-item">
                            <div class="liverakala-blog-img-wrap">
                                <span class="liverakala-discount-badge liverakala-blog-date"><?php echo get_the_date('j F Y'); ?></span>
                                <a href="<?php the_permalink(); ?>">
                                    <?php 
                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( 'medium' );
                                    } else {
                                        echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="Placeholder">';
                                    }
                                    ?>
                                </a>
                            </div>
                            <div class="liverakala-content-area liverakala-blog-content">
                                <h3><a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;"><?php the_title(); ?></a></h3>
                                <p class="liverakala-blog-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 12, '...' ); ?></p>
                                <a href="<?php the_permalink(); ?>" class="liverakala-blog-link">ادامه مطلب &larr;</a>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <?php
    else:
        echo '<p style="text-align:center; width:100%; padding:25px; color:#666;">مقاله‌ای یافت نشد.</p>';
    endif;

    return ob_get_clean();
}
add_shortcode( 'liverakala_blog_slider', 'liverakala_blog_slider_shortcode' );


/**
 * ۲. شورت‌کد گرید آرشیو بلاگ با صفحه‌بندی (نسخه استاندارد و لینک‌دار)
 * Shortcode: [liverakala_blog_archive]
 */
function liverakala_render_blog_archive_grid( $atts ) {
    ob_start();

    $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 9,
        'paged'          => $paged,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    echo '<div class="lk-shop-layout-wrapper" id="lk-blog-wrapper">';
    echo '<main class="lk-shop-main-content" style="width: 100%;">';

    echo '<div id="lk-products-container">';
    echo '<div class="lk-archive-products-grid lk-blog-grid" id="lk-archive-grid">';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $post_permalink = get_permalink();
            $post_image     = has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'large' ) : '';
            $post_title     = get_the_title();
            $post_date      = get_the_date( 'j F Y' );
            ?>
            <div class="lk-blog-card">
                <!-- تصویر لینک‌دار در بالای کارت -->
                <a href="<?php echo esc_url( $post_permalink ); ?>" class="lk-blog-image-wrap">
                    <?php echo $post_image; ?>
                </a>

                <!-- محتوای کارت (تاریخ و عنوان با فاصله اصولی) -->
                <div class="lk-blog-content">
                    <span class="lk-blog-date"><?php echo esc_html( $post_date ); ?></span>
                    <h3 class="lk-blog-title">
                        <a href="<?php echo esc_url( $post_permalink ); ?>">
                            <?php echo esc_html( $post_title ); ?>
                        </a>
                    </h3>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo '<div class="lk-no-products-box" style="grid-column: 1 / -1;"><p>هیچ مقاله‌ای یافت نشد.</p></div>';
    }

    echo '</div>'; // پایان گرید

    $big = 999999999;
    $pagination = paginate_links( array(
        'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format'    => '?paged=%#%',
        'current'   => max( 1, $paged ),
        'total'     => $query->max_num_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'list',
    ) );

    if ( $pagination ) {
        echo '<nav class="lk-pagination-wrap">' . $pagination . '</nav>';
    }

    echo '</div>'; // پایان کانتینر
    echo '</main>';
    echo '</div>';

    return ob_get_clean();
}
add_shortcode( 'liverakala_blog_archive', 'liverakala_render_blog_archive_grid' );