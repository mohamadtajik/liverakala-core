jQuery(document).ready(function($) {
    // تابع جامع محاسبه و آپدیت آنی قیمت‌ها و شمارنده هدر
    function updateCartInstantly() {
        var grandTotal = 0;
        var currencySymbol = '';
        var remainingItems = 0;
        var totalQuantity = 0;

        $('.liverakala-cart-item').each(function() {
            var $row = $(this);
            if ($row.hasClass('is-deleting')) return;

            remainingItems++;
            var $qtyInput = $row.find('.qty');
            var qty = parseFloat($qtyInput.val()) || 1;

            if (qty < 1) {
                qty = 1;
                $qtyInput.val(1);
            }

            totalQuantity += qty;

            var unitPrice = parseFloat($row.attr('data-unit-price')) || 0;
            var $subtotalElem = $row.find('.item-subtotal').first();

            if (unitPrice > 0 && $subtotalElem.length) {
                var totalRowPrice = unitPrice * qty;

                if (!currencySymbol) {
                    var sampleText = $subtotalElem.text();
                    currencySymbol = sampleText.replace(/[0-9.,]/g, '').trim();
                }

                $subtotalElem.text(totalRowPrice.toLocaleString('en-US') + ' ' + currencySymbol);
                grandTotal += totalRowPrice;
            }
        });

        // به‌روزرسانی بخش مجموع کل (سایدبار)
        var $sidebarBoxes = $('.cart-collaterals .amount, .ct-cart-totals .amount, .cart_totals .amount');
        if ($sidebarBoxes.length > 0) {
            $sidebarBoxes.each(function() {
                if (currencySymbol) {
                    $(this).text(grandTotal.toLocaleString('en-US') + ' ' + currencySymbol);
                }
            });
        }

        // به‌روزرسانی شمارنده هدر
        if (remainingItems > 0 && totalQuantity > 0) {
            $('.ct-cart-count, .count, .woocommerce-cart-count, .cart-counter').text(totalQuantity).show();
        } else {
            $('.ct-cart-count, .count, .woocommerce-cart-count, .cart-counter').text('0').hide();
        }

        return remainingItems;
    }

    // مقداردهی اولیه برای قفل کردن قیمت واحد واقعی ردیف‌ها
    function initPrices() {
        $('.liverakala-cart-item').each(function() {
            var $row = $(this);
            var $qty = $row.find('.qty');
            var $priceElem = $row.find('.item-subtotal').first();

            if ($qty.length && $priceElem.length && !$row.attr('data-unit-price')) {
                var currentQty = parseInt($qty.val(), 10) || 1;
                var rawText = $priceElem.text();
                var cleanVal = parseFloat(rawText.replace(/[^0-9.]/g, '')) || 0;
                
                if (cleanVal > 0) {
                    var unitPrice = Math.round(cleanVal / currentQty);
                    $row.attr('data-unit-price', unitPrice);
                }
            }
        });
    }

    // قالب گرافیکی و خفن سبد خرید خالی (سازگار با استایل‌های جدید)
    var modernEmptyHtml = `
        <div class="liverakala-empty-container">
            <div class="liverakala-empty-card">
                <div class="empty-icon-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <h3 class="empty-title">سبد خرید شما در حال حاضر خالی است!</h3>
                <p class="empty-desc">هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. از محصولات شگفت‌انگیز ما دیدن کنید و خرید خود را آغاز نمایید.</p>
                <a href="/shop/" class="empty-shop-btn">
                    <span>مشاهده و خرید محصولات</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    `;

    // اجرای اولیه
    initPrices();
    updateCartInstantly();

    // ۱. مدیریت بهینه و بدون فریزِ تغییرات تعداد
    let cartTimeout;
    let isAjaxRunning = false;

    $(document).on('click', '.liverakala-cart-item .plus, .liverakala-cart-item .minus, .quantity .plus, .quantity .minus, .ct-plus, .ct-minus', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $row = $button.closest('.liverakala-cart-item');
        var $input = $row.find('.qty');
        
        var val = parseInt($input.val(), 10) || 1;
        var step = 1;
        
        if ($button.hasClass('plus') || $button.hasClass('ct-plus')) {
            val += step;
        } else {
            val = Math.max(1, val - step);
        }
        
        $input.val(val);
        updateCartInstantly();

        if (cartTimeout) clearTimeout(cartTimeout);

        cartTimeout = setTimeout(function() {
            if (isAjaxRunning) return;
            var $form = $('form.woocommerce-cart-form');
            if ($form.length) {
                isAjaxRunning = true;
                $.ajax({
                    type: 'POST',
                    url: $form.attr('action') || window.location.href,
                    data: $form.serialize() + '&update_cart=1',
                    complete: function() {
                        isAjaxRunning = false;
                    }
                });
            }
        }, 600);
    });

    // مدیریت تغییر مستقیم عدد داخل اینپوت
    $(document).on('change', '.liverakala-cart-item .qty', function() {
        var $input = $(this);
        var val = parseInt($input.val(), 10);
        if (isNaN(val) || val < 1) {
            $input.val(1);
        }
        updateCartInstantly();

        if (cartTimeout) clearTimeout(cartTimeout);
        cartTimeout = setTimeout(function() {
            var $form = $('form.woocommerce-cart-form');
            if ($form.length) {
                $.ajax({
                    type: 'POST',
                    url: $form.attr('action') || window.location.href,
                    data: $form.serialize() + '&update_cart=1'
                });
            }
        }, 600);
    });

    // ۲. حذف محصول از صفحه سبد خرید اختصاصی (با اعمال آنی کارت گرافیکی جدید)
    $(document).on('click', '.liverakala-cart-item a.remove, a.remove', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $removeBtn = $(this);
        var $row = $removeBtn.closest('.liverakala-cart-item');
        var removeUrl = $removeBtn.attr('href');

        $row.addClass('is-deleting');
        $row.fadeOut(150, function() {
            $(this).remove();
            var leftItems = updateCartInstantly();

            if (leftItems === 0) {
                $('.ct-cart-count, .count, .woocommerce-cart-count, .cart-counter').text('0').hide();
                $('.cart-collaterals, .ct-cart-collaterals, .cart_totals').fadeOut(200);
                $('.liverakala-custom-cart-wrapper').html(modernEmptyHtml);
            }
        });

        if (removeUrl) {
            $.get(removeUrl, function() {
                $(document.body).trigger('wc_fragment_refresh');
            });
        }

        return false;
    });

    // ۳. هماهنگ‌سازی با مینی‌کارت و پاسخ سرور
    $(document.body).on('removed_from_cart wc_fragments_refreshed', function() {
        setTimeout(function() {
            if ($('.liverakala-custom-cart-wrapper').length) {
                $.get(window.location.href, function(data) {
                    var $serverResponse = $(data);
                    var $newCartItems = $serverResponse.find('.liverakala-cart-items');
                    
                    if ($newCartItems.length && $newCartItems.find('.liverakala-cart-item').length > 0) {
                        $('.liverakala-cart-items').html($newCartItems.html());
                        initPrices();
                        updateCartInstantly();
                    } else if ($serverResponse.find('.woocommerce-cart-empty-notice').length || $newCartItems.find('.liverakala-cart-item').length === 0) {
                        $('.ct-cart-count, .count, .woocommerce-cart-count, .cart-counter').text('0').hide();
                        $('.cart-collaterals, .ct-cart-collaterals, .cart_totals').fadeOut(200);
                        $('.liverakala-custom-cart-wrapper').html(modernEmptyHtml);
                    }
                });
            }
        }, 350);
    });

});