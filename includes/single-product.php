<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! shortcode_exists( 'lk_single_product' ) ) {
    add_shortcode( 'lk_single_product', 'lk_single_product_shortcode_callback' );
}

function lk_single_product_shortcode_callback( $atts ) {
    $atts = shortcode_atts( [
        'id' => 0,
    ], $atts, 'lk_single_product' );

    $product_id = intval( $atts['id'] );

    if ( ! $product_id ) {
        if ( is_product() ) {
            $product_id = get_the_ID();
        } else {
            global $product;
            if ( is_a( $product, 'WC_Product' ) ) {
                $product_id = $product->get_id();
            }
        }
    }

    if ( ! $product_id ) {
        return '<div style="padding: 20px; color: #dc2626; text-align:center; font-family:IranYekan,Tahoma; font-weight:bold; background:#fef2f2; border:1px solid #fee2e2; border-radius:12px;">محصولی برای نمایش یافت نشد.</div>';
    }

    if ( ! function_exists( 'wc_get_product' ) ) {
        return '<div style="padding: 20px; color: #dc2626; text-align:center; font-family:IranYekan,Tahoma;">افزونه ووکامرس فعال نیست!</div>';
    }

    $product = wc_get_product( $product_id );

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return '<div style="padding: 20px; color: #dc2626; text-align:center; font-family:IranYekan,Tahoma;">محصولی با این آیدی یافت نشد!</div>';
    }

    $attachment_ids = $product->get_gallery_image_ids();
    $main_image_id  = $product->get_image_id();
    
    $main_image_url = wp_get_attachment_image_url( $main_image_id, 'full' );
    if ( ! $main_image_url ) {
        $main_image_url = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('full') : '';
    }

    // استخراج برند BeRocket یا سایر افزونه‌ها
    $brand_name = '';
    if ( function_exists( 'berocket_get_brands' ) ) {
        $b_terms = berocket_get_brands( $product_id );
        if ( ! empty( $b_terms ) && ! is_wp_error( $b_terms ) ) {
            $brand_links = [];
            foreach ( $b_terms as $term ) {
                $brand_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
            }
            $brand_name = implode( ', ', $brand_links );
        }
    }

    if ( empty( $brand_name ) ) {
        $taxonomies_to_check = ['brands', 'product_brand', 'pwb-brand', 'berocket_brand'];
        foreach ( $taxonomies_to_check as $tax ) {
            if ( taxonomy_exists( $tax ) ) {
                $terms = wp_get_object_terms( $product_id, $tax );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $brand_links = [];
                    foreach ( $terms as $term ) {
                        $brand_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
                    }
                    $brand_name = implode( ', ', $brand_links );
                    break;
                }
            }
        }
    }

    $short_desc = $product->get_short_description();
    $short_desc = preg_replace('/^\s*<p>(.*?)<\/p>\s*$/is', '$1', $short_desc);

    ob_start();
    ?>

    <div class="lk-single-product-container">
        <div class="lk-product-unified-card">
            <div class="lk-product-wrapper">
                
                <!-- ۱. گالری تصاویر -->
                <div class="lk-product-col-gallery">
                    <div class="lk-main-image-box">
                        <img src="<?php echo esc_url( $main_image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="lk-main-product-img" id="lk-main-img-target">
                    </div>
                    <?php if ( ! empty( $attachment_ids ) || $main_image_id ) : ?>
                        <div class="lk-gallery-thumbs-scrollable" id="lk-gallery-thumbs-container">
                            <div class="lk-gallery-thumbs">
                                <?php if ( $main_image_id ) : 
                                    $full_src = wp_get_attachment_image_url( $main_image_id, 'full' );
                                ?>
                                    <div class="lk-thumb-item active" data-full-src="<?php echo esc_url( $full_src ); ?>">
                                        <?php echo wp_get_attachment_image( $main_image_id, 'woocommerce_thumbnail' ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( ! empty( $attachment_ids ) ) : ?>
                                    <?php foreach ( $attachment_ids as $attachment_id ) : 
                                        $full_src = wp_get_attachment_image_url( $attachment_id, 'full' );
                                        if ( ! $full_src ) continue;
                                        ?>
                                        <div class="lk-thumb-item" data-full-src="<?php echo esc_url( $full_src ); ?>">
                                            <?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_thumbnail' ); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ۲. اطلاعات پایه محصول (ستون وسط) -->
                <div class="lk-product-col-info">
                    <h1 class="lk-product-title"><?php echo esc_html( $product->get_name() ); ?></h1>
                    
                    <div class="lk-product-meta-info">
                        <?php if ( ! empty( $brand_name ) ) : ?>
                            <div class="lk-meta-row">
                                <span class="lk-meta-label">برند:</span>
                                <span class="lk-meta-value"><?php echo $brand_name; ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="lk-meta-row">
                            <span class="lk-meta-label">دسته بندی:</span>
                            <span class="lk-meta-value"><?php echo wc_get_product_category_list( $product->get_id(), ', ' ); ?></span>
                        </div>
                    </div>

                    <!-- کارت‌های مشخصات پویا (خواندن ۶ ویژگی اول محصول) -->
               <!-- تایتل ویژه بخش مشخصات (استفاده از تگ span مطابق با سایر بخش‌ها) -->
<div class="lk-specs-section-header">
    <div class="lk-specs-title-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
    </div>
    <span class="lk-specs-section-title">ویژگی محصول</span>
</div>

<!-- کانتینر مشخصات پویا (۶ ویژگی اول با خروجی span و p) -->
<div class="lk-product-short-desc">
    <?php 
    $attributes = $product->get_attributes();
    
    if ( ! empty( $attributes ) ) {
        $count = 0;
        foreach ( $attributes as $attribute ) {
            if ( ! $attribute->get_visible() ) {
                continue;
            }

            $label = wc_attribute_label( $attribute->get_name() );
            
            if ( $attribute->is_taxonomy() ) {
                $values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
                $value = implode( ', ', $values );
            } else {
                $values = $attribute->get_options();
                $value = implode( ', ', $values );
            }
            
            if ( ! empty( $value ) ) {
                echo '<div class="lk-spec-item">';
                echo '<span>' . esc_html( $label ) . '</span>';
                echo '<p>' . esc_html( $value ) . '</p>';
                echo '</div>';

                $count++;
                if ( $count >= 6 ) {
                    break;
                }
            }
        }
    }
    ?>
</div>
                </div>

                <!-- ۳. ستون خرید -->
                <div class="lk-product-col-buy">
                    <div class="lk-buy-box-inner">
                        
                        <!-- مزیت‌ها و ضمانت -->
                        <div class="lk-product-features-list">
                            <div class="lk-feature-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span>ضمانت اصالت و سلامت فیزیکی کالا</span>
                            </div>
                            <div class="lk-feature-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                <span>ارسال سریع به سراسر کشور</span>
                            </div>
                            <div class="lk-feature-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span>پشتیبانی ۲۴ ساعته</span>
                            </div>
                            <div class="lk-feature-item lk-consult-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span>مشاوره قبل از خرید: <a href="tel:09394008214">09394008214</a></span>
                            </div>
                        </div>

                        <!-- قیمت -->
<div class="lk-product-price-box" style="display: flex !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; border-bottom: 1px solid #eeeeee !important; padding-bottom: 15px !important; margin-bottom: 15px !important;">
    <span class="lk-price-label" style="font-size: 15px !important; font-weight: 700 !important; color: #374151 !important; font-family: 'IRANYekan', sans-serif !important;">قیمت:</span>
    <div class="lk-price-value" style="font-size: 20px !important; font-weight: 900 !important; color: #111827 !important; font-family: 'IRANYekan', sans-serif !important;">
        <?php 
        $price_html = $product->get_price_html();
        // تزریق استایل اجباری به تمام اجزای قیمت برای تضمین نمایش بزرگ و پررنگ
        $price_html = str_replace('<ins', '<ins style="font-size: 20px !important; font-weight: 900 !important; color: #111827 !important; text-decoration: none !important;"', $price_html);
        $price_html = str_replace('<del', '<del style="font-size: 14px !important; font-weight: 600 !important; color: #6b7280 !important;"', $price_html);
        echo $price_html;
        ?>
    </div>
</div>

                        <!-- فرم خرید ووکامرس -->
                        <div class="lk-add-to-cart-section">
                            <?php 
                            if ( function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
                                $GLOBALS['product'] = $product;
                                woocommerce_template_single_add_to_cart();
                            }
                            ?>
                        </div>

                    </div>
                </div>

            </div>

            <!-- بخش پایین صفحه -->
            <div class="lk-product-bottom-section">
                <?php if ( $product->has_attributes() && function_exists('wc_display_product_attributes') ) : ?>
                    <div class="lk-specs-box">
                        <div class="lk-section-title-wrap">
                            <span class="lk-title-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </span>
                            <span class="lk-section-main-title">مشخصات محصول</span>
                        </div>
                        <?php wc_display_product_attributes( $product ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $product->get_description() ) ) : ?>
                    <div class="lk-full-desc-box">
                        <div class="lk-section-title-wrap">
                            <span class="lk-title-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </span>
                            <span class="lk-section-main-title">توضیح محصول</span>
                        </div>
                        <div class="lk-desc-content" id="lk-desc-container">
                            <?php echo wpautop( $product->get_description() ); ?>
                        </div>
                        <div class="lk-toggle-desc-wrapper">
                            <button type="button" id="lk-toggle-desc-btn" class="lk-toggle-desc-btn">
                                <span>مشاهده ادامه متن</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}