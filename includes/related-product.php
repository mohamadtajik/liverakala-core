<?php
/**
 * ماژول محصولات مشابه (Related Products)
 * نمایش بدون پیجینیشن به صورت اسلایدر افقی با تم قرمز-صورتی و فونت ایران یکان
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Liverakala_Related_Products {

    public function __construct() {
        // ثبت شورتکد اختصاصی
        add_shortcode( 'lk_related_products', array( $this, 'render_related_products' ) );

        // نمایش خودکار در صفحات تک محصول (Single Product)
        add_action( 'woocommerce_after_single_product_summary', function() {
            echo do_shortcode( '[lk_related_products limit="10"]' );
        }, 20 );
    }

    public function render_related_products( $atts ) {
        if ( ! is_product() ) {
            return '';
        }

        global $product;
        if ( ! $product ) {
            return '';
        }

        $atts = shortcode_atts( array(
            'limit' => 10,
        ), $atts, 'lk_related_products' );

        $cat_ids = $product->get_category_ids();
        if ( empty( $cat_ids ) ) {
            return '';
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => intval( $atts['limit'] ),
            'post__not_in'   => array( $product->get_id() ),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $cat_ids,
                ),
            ),
            'orderby'        => 'rand',
        );

        $related_query = new WP_Query( $args );

        if ( ! $related_query->have_posts() ) {
            return '';
        }

        ob_start();
        ?>
        <!-- باکس اصلی با تم قرمز-صورتی (مشابه سبک باکس‌های ویژه) -->
        <div class="liverakala-section-container liverakala-related-sale-box">
            <div class="liverakala-section-header">
                <h2 class="liverakala-section-title">محصولات مشابه</h2>
            </div>
            
            <div class="liverakala-slider-box">
                <div class="liverakala-horizontal-scroll lk-related-products-track" style="display: flex; gap: 0; padding: 10px;">
                    <?php
                    while ( $related_query->have_posts() ) : $related_query->the_post();
                        global $product;
                        $sale_price = $product->get_sale_price();
                        $regular_price = $product->get_regular_price();
                        $discount_percent = 0;
                        if ( $sale_price && $regular_price && $regular_price > 0 ) {
                            $discount_percent = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
                        }
                        ?>
                        <div class="liverakala-item">
                            <?php if ( $discount_percent > 0 ) : ?>
                                <span class="liverakala-discount-badge"><?php echo $discount_percent; ?>%</span>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url( get_permalink() ); ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%;">
                                <img src="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'medium' ) ); ?>" alt="<?php the_title_attribute(); ?>">
                                <div class="liverakala-content-area">
                                    <h3><?php the_title(); ?></h3>
                                    <div class="liverakala-price-box">
                                        <div class="price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// نمونه‌سازی کلاس برای فعال‌سازی شورتکد و هوک
new Liverakala_Related_Products();