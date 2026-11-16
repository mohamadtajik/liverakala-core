/**
 * ==========================================
 * ۱. اسکرول افقی با ماوس و ایجاد اینرسی (Drag to Scroll) برای اسلایدرها
 * ==========================================
 */
document.addEventListener("DOMContentLoaded", function() {
    const sliders = document.querySelectorAll('.liverakala-horizontal-scroll');
    
    sliders.forEach(slider => {
        slider.querySelectorAll('img, a').forEach(element => {
            element.addEventListener('dragstart', (e) => e.preventDefault());
        });

        let isDown = false;
        let startX;
        let scrollLeft;
        let hasMoved = false;
        let velocity = 0;
        let lastX = 0;
        let lastTime = 0;
        let animationFrameId = null;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            hasMoved = false;
            slider.classList.add('is-dragging');
            startX = e.pageX;
            scrollLeft = slider.scrollLeft;
            
            if (animationFrameId) { cancelAnimationFrame(animationFrameId); }
            lastX = e.pageX;
            lastTime = performance.now();
        });

        const stopDragging = (e) => {
            if (!isDown) return;
            isDown = false;
            slider.classList.remove('is-dragging');

            const now = performance.now();
            const dt = now - lastTime;
            if (dt > 0) {
                velocity = (lastX - e.pageX) / dt * 15;
            }

            const inertiaScroll = () => {
                if (Math.abs(velocity) > 0.5) {
                    slider.scrollLeft += velocity;
                    velocity *= 0.92;
                    animationFrameId = requestAnimationFrame(inertiaScroll);
                }
            };
            inertiaScroll();
        };

        slider.addEventListener('mouseleave', stopDragging);
        slider.addEventListener('mouseup', stopDragging);
        window.addEventListener('mouseup', stopDragging);

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const x = e.pageX;
            const walk = x - startX;
            if (Math.abs(walk) > 3) { hasMoved = true; }

            slider.scrollLeft = scrollLeft - walk;

            const now = performance.now();
            const dt = now - lastTime;
            if (dt > 50) {
                velocity = (lastX - x) / dt * 10;
                lastX = x;
                lastTime = now;
            }
        });

        slider.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                if (hasMoved) { e.preventDefault(); }
            });
        });
    });
});


/**
 * ==========================================
 * ۲. کنترل اسلایدرهای بنر (بهینه‌شده برای جلوگیری از Forced Reflow)
 * ==========================================
 */
document.addEventListener('DOMContentLoaded', function() {
    const wrappers = document.querySelectorAll('.lk-banner-wrapper');

    wrappers.forEach(wrapper => {
        const track = wrapper.querySelector('.lk-banner-track');
        const prevBtn = wrapper.querySelector('.lk-prev-btn');
        const nextBtn = wrapper.querySelector('.lk-next-btn');

        if (!track) return;

        const slides = track.querySelectorAll('.lk-banner-slide');
        if (slides.length === 0) return;

        // کش کردن عرض اسلاید برای جلوگیری از محاسبه مکرر و Forced Reflow هنگام کلیک
        let cachedSlideWidth = 0;
        const getSlideWidth = () => {
            if (cachedSlideWidth === 0 && slides[0]) {
                cachedSlideWidth = slides[0].offsetWidth + 12;
            }
            return cachedSlideWidth || 300; // مقدار پیش‌فرض امن
        };

        if (nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => {
                const slideWidth = getSlideWidth();
                track.scrollBy({ left: slideWidth, behavior: 'smooth' });
            });

            prevBtn.addEventListener('click', () => {
                const slideWidth = getSlideWidth();
                track.scrollBy({ left: -slideWidth, behavior: 'smooth' });
            });
        }

        let isDown = false;
        let startX;
        let scrollLeft;
        let hasMoved = false;

        track.querySelectorAll('img, a').forEach(el => {
            el.addEventListener('dragstart', (e) => e.preventDefault());
        });

        track.addEventListener('mousedown', (e) => {
            isDown = true;
            hasMoved = false;
            startX = e.pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
            track.classList.add('is-dragging');
        });

        const stopDragging = () => {
            if (!isDown) return;
            isDown = false;
            track.classList.remove('is-dragging');
        };

        track.addEventListener('mouseleave', stopDragging);
        track.addEventListener('mouseup', stopDragging);

        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 1.0;

            if (Math.abs(walk) > 5) {
                hasMoved = true;
            }

            track.scrollLeft = scrollLeft - walk;
        });

        track.addEventListener('click', (e) => {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    });
});

/* تایمر تخفیف ویژه */
 /* تایمر تخفیف ویژه - هماهنگ و سراسری برای همه دستگاه‌ها */
document.addEventListener("DOMContentLoaded", function() {
    const timerElement = document.getElementById("timer-counter");
    if (!timerElement) return;

    const targetDate = new Date("2026-11-13T23:59:59").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const remainingSeconds = Math.floor((targetDate - now) / 1000);

        if (remainingSeconds <= 0) {
            timerElement.textContent = "00:00:00";
            return;
        }

        // محاسبه ساعت، دقیقه و ثانیه (حتی اگر ساعت بیش از ۲۴ ساعت باشد)
        let hours = Math.floor(remainingSeconds / 3600);
        let minutes = Math.floor((remainingSeconds % 3600) / 60);
        let seconds = remainingSeconds % 60;

        timerElement.textContent = 
            String(hours).padStart(2, '0') + ":" + 
            String(minutes).padStart(2, '0') + ":" + 
            String(seconds).padStart(2, '0');
    }

    setInterval(updateTimer, 1000);
    updateTimer();
});
/**
 * ==========================================
 * ۳. سیستم فیلترینگ ایجکس فروشگاه، مدیریت سایدبار، اسلایدر قیمت و مرتب‌سازی
 * ==========================================
 */
document.addEventListener('DOMContentLoaded', function() {
    let isLoading = false;

    document.addEventListener('click', function(e) {
        const openFilterBtn = e.target.closest('#lk-open-filter-modal');
        if (openFilterBtn) {
            e.preventDefault();
            const sidebarModal = document.getElementById('lk-filter-sidebar');
            const overlay = document.getElementById('lk-filter-overlay');
            if (sidebarModal && overlay) {
                sidebarModal.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        
        const closeFilterBtn = e.target.closest('#lk-close-filter-modal');
        const openSortBtn = e.target.closest('#lk-open-sort-modal');
        const closeSortBtn = e.target.closest('#lk-close-sort-modal');
        const overlayEl = e.target.closest('#lk-filter-overlay');

        const sidebarModal = document.getElementById('lk-filter-sidebar');
        const sortModal = document.getElementById('lk-sort-modal');
        const overlay = document.getElementById('lk-filter-overlay');

        if (closeFilterBtn || closeSortBtn || (overlayEl && sidebarModal && sortModal)) {
            e.preventDefault();
            if (sidebarModal) sidebarModal.classList.remove('active');
            if (sortModal) sortModal.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (openSortBtn && sortModal && overlay) {
            e.preventDefault();
            sortModal.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    });

    function syncPriceLabels() {
        const rMin = document.getElementById('lk-range-min');
        const rMax = document.getElementById('lk-range-max');
        const minValText = document.getElementById('lk-min-val-text');
        const maxValText = document.getElementById('lk-max-val-text');

        if (!rMin || !rMax) return;
        let minVal = parseInt(rMin.value);
        let maxVal = parseInt(rMax.value);

        if (minVal > maxVal) {
            let temp = minVal;
            minVal = maxVal;
            maxVal = temp;
        }

        if (minValText) minValText.textContent = minVal.toLocaleString();
        if (maxValText) maxValText.textContent = maxVal.toLocaleString();
    }

    function executeAjaxFilter(targetUrl = null, resetPage = true) {
        if (isLoading) return;
        isLoading = true;

        const sidebarModal = document.getElementById('lk-filter-sidebar');
        const sortModal = document.getElementById('lk-sort-modal');
        const overlay = document.getElementById('lk-filter-overlay');

        if (sidebarModal) sidebarModal.classList.remove('active');
        if (sortModal) sortModal.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';

        let url;
        if (targetUrl) {
            url = new URL(targetUrl, window.location.origin);
        } else {
            let cleanPath = window.location.pathname.replace(/\/page\/\d+\/?/, '/');
            url = new URL(cleanPath, window.location.origin);
            
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((val, key) => {
                if (key !== 'paged') {
                    url.searchParams.set(key, val);
                }
            });

            let selectedBrands = [];
            document.querySelectorAll('.lk-brand-checkbox:checked').forEach(cb => selectedBrands.push(cb.value));

            let currentSort = document.querySelector('.lk-sort-pill.active, .lk-sort-options-list a.active');
            let sortVal = currentSort ? currentSort.getAttribute('data-sort') : 'menu_order';

            if (selectedBrands.length > 0) {
                url.searchParams.set('lk_brand', selectedBrands.join(','));
            } else {
                url.searchParams.delete('lk_brand');
            }

            if (sortVal && sortVal !== 'menu_order') {
                url.searchParams.set('lk_sort', sortVal);
            } else {
                url.searchParams.delete('lk_sort');
            }

            const rMin = document.getElementById('lk-range-min');
            const rMax = document.getElementById('lk-range-max');

            if (rMin && rMax) {
                let curMin = parseInt(rMin.value);
                let curMax = parseInt(rMax.value);
                let defMin = parseInt(rMin.min);
                let defMax = parseInt(rMin.max);

                if (curMin > defMin || curMax < defMax) {
                    url.searchParams.set('lk_min_price', curMin);
                    url.searchParams.set('lk_max_price', curMax);
                } else {
                    url.searchParams.delete('lk_min_price');
                    url.searchParams.delete('lk_max_price');
                }
            }
        }

        window.history.pushState({}, '', url.toString());

        const productsContainer = document.getElementById('lk-products-container');
        if (productsContainer) {
            productsContainer.style.opacity = '0.3';
            productsContainer.style.pointerEvents = 'none';
        }

        fetch(url.toString())
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newProductsContainer = doc.getElementById('lk-products-container');
                const newSidebar = doc.getElementById('lk-filter-sidebar');
                const currentSidebar = document.getElementById('lk-filter-sidebar');

                if (newProductsContainer && productsContainer) {
                    productsContainer.innerHTML = newProductsContainer.innerHTML;
                }

                if (newSidebar && currentSidebar) {
                    currentSidebar.innerHTML = newSidebar.innerHTML;
                    
                    const currentBrandsParam = new URL(window.location.href).searchParams.get('lk_brand');
                    if (currentBrandsParam) {
                        const activeList = currentBrandsParam.split(',');
                        currentSidebar.querySelectorAll('.lk-brand-checkbox').forEach(cb => {
                            if (activeList.includes(cb.value)) {
                                cb.checked = true;
                            }
                        });
                    }
                }

                const activeSortEl = document.querySelector('.lk-sort-pill.active, .lk-sort-options-list a.active');
                const mobileSortText = document.getElementById('lk-mobile-sort-btn-text');
                if (mobileSortText) {
                    let sortKey = activeSortEl ? activeSortEl.getAttribute('data-sort') : 'menu_order';
                    mobileSortText.textContent = (sortKey === 'menu_order') ? 'مرتب‌سازی' : (activeSortEl ? activeSortEl.textContent : 'مرتب‌سازی');
                }

                if (productsContainer) {
                    productsContainer.style.opacity = '1';
                    productsContainer.style.pointerEvents = 'auto';
                }

                isLoading = false;
                
                const wrapper = document.getElementById('lk-shop-wrapper') || document.getElementById('lk-blog-wrapper');
                if (wrapper) {
                    const headerOffset = 100;
                    const elementPosition = wrapper.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            })
            .catch(err => {
                console.error('خطا در بارگذاری فیلتر:', err);
                if (productsContainer) {
                    productsContainer.style.opacity = '1';
                    productsContainer.style.pointerEvents = 'auto';
                }
                isLoading = false;
            });
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('lk-brand-checkbox')) {
            executeAjaxFilter(null, true);
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target && (e.target.id === 'lk-range-min' || e.target.id === 'lk-range-max')) {
            syncPriceLabels();
        }
    });

    ['change', 'mouseup', 'touchend'].forEach(eventType => {
        document.addEventListener(eventType, function(e) {
            if (e.target && (e.target.id === 'lk-range-min' || e.target.id === 'lk-range-max')) {
                executeAjaxFilter(null, true);
            }
        });
    });

    document.addEventListener('click', function(e) {
        let pageLink = e.target.closest('.lk-pagination-wrap .page-numbers');
        if (pageLink && !pageLink.classList.contains('current') && !pageLink.classList.contains('dots')) {
            e.preventDefault();
            executeAjaxFilter(pageLink.href, false);
            return;
        }

        let sortLink = e.target.closest('.lk-sort-pill, .lk-sort-options-list a');
        if (sortLink) {
            e.preventDefault();
            document.querySelectorAll('.lk-sort-pill, .lk-sort-options-list a').forEach(l => l.classList.remove('active'));
            sortLink.classList.add('active');

            const mobileSortText = document.getElementById('lk-mobile-sort-btn-text');
            if (mobileSortText) {
                let sortKey = sortLink.getAttribute('data-sort');
                mobileSortText.textContent = (sortKey === 'menu_order') ? 'مرتب‌سازی' : sortLink.textContent;
            }

            executeAjaxFilter(null, true);
        }

        if (e.target && e.target.id === 'lk-clear-filters') {
            e.preventDefault();
            document.querySelectorAll('.lk-brand-checkbox').forEach(cb => cb.checked = false);

            const rMin = document.getElementById('lk-range-min');
            const rMax = document.getElementById('lk-range-max');
            if (rMin && rMax) {
                rMin.value = rMin.min;
                rMax.value = rMax.max;
                syncPriceLabels();
            }

            document.querySelectorAll('.lk-sort-pill, .lk-sort-options-list a').forEach(l => l.classList.remove('active'));
            const defaultSort = document.querySelector('[data-sort="menu_order"]');
            if (defaultSort) defaultSort.classList.add('active');
            
            const mobileSortText = document.getElementById('lk-mobile-sort-btn-text');
            if (mobileSortText) mobileSortText.textContent = 'مرتب‌سازی';

            executeAjaxFilter(null, true);
        }

        if (e.target && e.target.id === 'lk-clear-price') {
            e.preventDefault();
            const rMin = document.getElementById('lk-range-min');
            const rMax = document.getElementById('lk-range-max');
            if (rMin && rMax) {
                rMin.value = rMin.min;
                rMax.value = rMax.max;
                syncPriceLabels();
            }
            executeAjaxFilter(null, true);
        }
    });

    window.addEventListener('popstate', function() {
        location.reload();
    });
});


/**
 * ==========================================
 * سیستم جستجوی زنده (Live Search) بهینه شده
 * ==========================================
 */
document.addEventListener("DOMContentLoaded", function() {
    const searchWrappers = document.querySelectorAll('.lk-modern-search-wrapper');
    if (searchWrappers.length === 0) return;

    let searchCache = {};
    const ajaxUrl = (typeof wc_add_to_cart_params !== 'undefined') ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php';

    searchWrappers.forEach(wrapper => {
        const input = wrapper.querySelector('.lk-live-search-input');
        const dropdown = wrapper.querySelector('.lk-live-results-dropdown');
        const spinner = wrapper.querySelector('.lk-search-spinner');
        const clearBtn = wrapper.querySelector('.lk-clear-search');
        
        if (!input || !dropdown) return;

        let debounceTimer;

        input.addEventListener('input', function() {
            const query = this.value.trim();
            if (clearBtn) {
                clearBtn.style.display = query.length > 0 ? 'block' : 'none';
            }

            if (query.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                if (spinner) spinner.style.display = 'none';
                return;
            }

            if (searchCache[query]) {
                dropdown.innerHTML = searchCache[query];
                dropdown.style.display = 'block';
                if (spinner) spinner.style.display = 'none';
                return;
            }

            if (spinner) spinner.style.display = 'block';
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                fetch(`${ajaxUrl}?action=lk_fast_live_search&s=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (spinner) spinner.style.display = 'none';
                        if (data.success) {
                            searchCache[query] = data.html;
                            dropdown.innerHTML = data.html;
                            dropdown.style.display = 'block';
                        }
                    })
                    .catch(() => {
                        if (spinner) spinner.style.display = 'none';
                    });
            }, 250);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                input.value = '';
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                this.style.display = 'none';
                input.focus();
            });
        }
    });

    // بستن دراپ‌داون با کلیک بیرون از باکس
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.lk-modern-search-wrapper')) {
            document.querySelectorAll('.lk-live-results-dropdown').forEach(drop => {
                drop.style.display = 'none';
            });
        }
    });
});
/**
 * ==========================================
 * ۵. مدیریت منوی حساب کاربری و مگامنو
 * ==========================================
 */
jQuery(document).ready(function($) {
    const $authWrapper =$('.lr-auth-dropdown-wrapper');
    const $authBtn =$authWrapper.find('.lr-auth-btn.logged-in');

    $authBtn.on('click', function(e) {
        if (window.matchMedia("(max-width: 1024px)").matches || 'ontouchstart' in window) {
            e.preventDefault();
            $authWrapper.toggleClass('active');
        }
    });

    $(document).on('click touchstart', function(e) {
        if (!$(e.target).closest('.lr-auth-dropdown-wrapper').length) {$authWrapper.removeClass('active');
        }
    });

    $(document).on('click', '#js-menu-trigger, #js-menu-close, #js-menu-overlay', function(e) {
        e.preventDefault();
        $('#js-menu-dropdown').toggleClass('is-open');
        $('#js-menu-overlay').toggleClass('is-active');
        $('body').toggleClass('mega-menu-open');
    });

    $(document).on('click', '.js-toggle-sub', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $this =$(this);
        $this.toggleClass('is-open');$this.next('.js-sub-box').stop(true, true).slideToggle(200);
    });
});




/* ایجکس دکمه افزودن به سبد خرید */
jQuery(document).ready(function($) {
    
    // تزریق پاپ‌آپ به انتهای صفحه
    if ($('#liverakala-popup-modal').length === 0) {
        var modalHtml = `
            <div id="liverakala-popup-modal" class="liverakala-modal-overlay">
                <div class="liverakala-modal-box">
                    <div class="liverakala-modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div class="liverakala-modal-title">محصول با موفقیت اضافه شد</div>
                    <div class="liverakala-modal-desc">اکنون می‌توانید سبد خرید خود را بررسی کنید یا به خرید ادامه دهید.</div>
                    <div class="liverakala-modal-buttons">
                        <button type="button" class="liverakala-modal-btn liverakala-btn-continue">ادامه خرید</button>
                        <a href="/cart/" class="liverakala-modal-btn liverakala-btn-cart">مشاهده سبد خرید</a>
                    </div>
                    <div class="liverakala-timer-bar"></div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
    }

    var popupTimer;

    function showPopup() {
        var $modal = $('#liverakala-popup-modal');
        var $timerBar = $modal.find('.liverakala-timer-bar');
        
        // تنظیم تایمر ۵ ثانیه‌ای و فعال‌سازی انیمیشن
        $timerBar.css({ 'animation': 'none', 'animation-play-state': 'running' });
        $timerBar[0].offsetHeight;
        $timerBar.css('animation', 'liverakalaTimerAnim 5s linear forwards');

        $modal.addClass('active');

        startTimer();
    }

    function startTimer() {
        clearTimeout(popupTimer);
        popupTimer = setTimeout(function() {
            hidePopup();
        }, 5000); // ۵ ثانیه مکث
    }

    function hidePopup() {
        $('#liverakala-popup-modal').removeClass('active');
        clearTimeout(popupTimer);
    }

    // متوقف کردن تایمر و انیمیشن به محض هاور روی باکس پاپ‌آپ
    $(document).on('mouseenter', '.liverakala-modal-box', function() {
        clearTimeout(popupTimer);
        $('#liverakala-popup-modal').find('.liverakala-timer-bar').css('animation-play-state', 'paused');
    });

    // از سرگیری تایمر و انیمیشن با برداشتن موس از روی پاپ‌آپ
    $(document).on('mouseleave', '.liverakala-modal-box', function() {
        var $timerBar = $('#liverakala-popup-modal').find('.liverakala-timer-bar');
        $timerBar.css('animation-play-state', 'running');
        
        // محاسبه باقی‌مانده زمان یا شروع مجدد ایمن تایمر
        clearTimeout(popupTimer);
        popupTimer = setTimeout(function() {
            hidePopup();
        }, 2500); // زمان باقیمانده پس از هاور
    });

    // بستن پاپ‌آپ با کلیک روی ادامه خرید یا پس‌زمینه
    $(document).on('click', '.liverakala-btn-continue, .liverakala-modal-overlay', function(e) {
        if ($(e.target).is('#liverakala-popup-modal') || $(e.target).hasClass('liverakala-btn-continue')) {
            hidePopup();
        }
    });

    // کلیک روی دکمه افزودن به سبد خرید
    $(document.body).on('click', '.single_add_to_cart_button', function(e) {
        var $btn = $(this);
        var $form = $btn.closest('form.cart');

        if ($form.length > 0) {
            e.preventDefault();

            if ($btn.hasClass('loading') || $btn.hasClass('added-successfully')) return;

            $btn.addClass('loading');

            var formData = $form.serialize();
            var productId = $btn.val() || $form.find('input[name*="add-to-cart"]').val() || '';
            
            if (productId && formData.indexOf('add-to-cart') === -1) {
                formData += '&add-to-cart=' + productId;
            }

            var originalButtonHtml = $btn.html();

            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params ? wc_add_to_cart_params.ajax_url : window.location.href,
                data: formData + '&action=woocommerce_add_to_cart',
                success: function(response) {
                    $btn.removeClass('loading').addClass('added-successfully');
                    $btn.html('<span style="display:inline-flex; align-items:center; gap:8px;">به سبد خرید اضافه شد ✓</span>');

                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);

                    showPopup();

                    setTimeout(function() {
                        $btn.removeClass('added-successfully');
                        $btn.html(originalButtonHtml);
                    }, 2000);
                },
                error: function() {
                    $btn.removeClass('loading');
                    $btn.html(originalButtonHtml);
                }
            });
        }
    });
});




(function($) {
    function initCustomSwatches() {
        const colorMap = {
            'قرمز': '#ff0000', 'آبی': '#0000ff', 'مشکی': '#000000', 'سیاه': '#000000',
            'سفید': '#ffffff', 'سبز': '#008000', 'زرد': '#ffff00', 'خاکستری': '#808080',
            'طوسی': '#808080', 'صورتی': '#ffc0cb', 'بنفش': '#800080', 'نارنجی': '#ffa500',
            'قهوه‌ای': '#a52a2a', 'سرمه‌ای': '#000080', 'طلایی': '#ffd700', 'نقره‌ای': '#c0c0c0',
            'نقره ای': '#c0c0c0', 'استیل': '#c0c0c0', 'سرخابی': '#ff007f', 'کرم': '#f5f5dc',
            'یاسی': '#c8a2c8', 'فیروزه‌ای': '#40e0d0', 'مسی': '#b87333', 'زیتونی': '#808080',
            'یشمی': '#004b49', 'کاراملی': '#ffd59e'
        };

        function cleanColorText(text) {
            if (!text) return '';
            return text.replace(/یک گزینه را انتخاب کنید|انتخاب رنگ|رنگ|صاف کردن|پاک‌سازی|پاک سازی|صاف|[:：]/gi, '').trim();
        }

        function getToastPopup() {
            let popup = document.querySelector('.swatch-out-of-stock-popup');
            if (!popup) {
                popup = document.createElement('div');
                popup.className = 'swatch-out-of-stock-popup';
                document.body.appendChild(popup);
            }
            return popup;
        }

        let popupTimer = null;
        function showOutOfStockPopup(colorName) {
            const popup = getToastPopup();
            popup.innerHTML = '⚠️ رنگ <strong>«' + colorName + '»</strong> ناموجود می‌باشد.';
            popup.classList.add('show');

            if (popupTimer) clearTimeout(popupTimer);
            popupTimer = setTimeout(function () {
                popup.classList.remove('show');
            }, 3000);
        }

        const forms = document.querySelectorAll('.lk-single-product-container form.variations_form');
        if (forms.length === 0) return;

        forms.forEach(form => {
            let variationsData = [];
            try {
                const rawData = form.getAttribute('data-product_variations');
                if (rawData) {
                    variationsData = JSON.parse(rawData);
                }
            } catch (e) {
                console.error('Error parsing variations', e);
            }

            const selects = form.querySelectorAll('.variations select');
            selects.forEach(select => {
                if (select.dataset.swatched) return;

                const attributeName = select.getAttribute('name');
                const options = Array.from(select.querySelectorAll('option'));
                if (options.length <= 1) return;

                select.dataset.swatched = "true";

                const wrapper = document.createElement('div');
                wrapper.className = 'custom-swatches-wrapper';
                wrapper.style.cssText = 'display: block !important; margin-bottom: 15px !important;';

                const titleElem = document.createElement('div');
                titleElem.className = 'selected-color-title';
                titleElem.innerText = 'رنگ: (یک گزینه را انتخاب کنید)';
                wrapper.appendChild(titleElem);

                const circlesContainer = document.createElement('div');
                circlesContainer.style.cssText = 'display: flex !important; gap: 12px !important; flex-wrap: wrap !important; margin-top: 8px !important; align-items: flex-start !important;';

                options.forEach(opt => {
                    if (!opt.value) return;
                    const colorName = cleanColorText(opt.text);
                    if (!colorName) return;

                    let inStock = true;
                    if (variationsData && variationsData.length > 0) {
                        const matchingVariations = variationsData.filter(v => {
                            return v.attributes && v.attributes[attributeName] === opt.value;
                        });
                        if (matchingVariations.length > 0) {
                            inStock = matchingVariations.some(v => v.is_in_stock && v.is_purchasable);
                        }
                    } else {
                        inStock = !opt.disabled;
                    }

                    const circleWrapper = document.createElement('div');
                    circleWrapper.style.cssText = 'display: flex; flex-direction: column; align-items: center; gap: 4px;';

                    const circle = document.createElement('div');
                    circle.className = 'swatch-circle';
                    circle.setAttribute('title', colorName);
                    
                    if (!inStock) {
                        circle.classList.add('disabled');
                    }

                    let hexColor = '#e0e0e0';
                    for (let key in colorMap) {
                        if (colorName.includes(key)) {
                            hexColor = colorMap[key];
                            break;
                        }
                    }

                    circle.style.backgroundColor = hexColor;
                    circle.style.border = `2px solid ${hexColor === '#ffffff' || hexColor === '#f5f5dc' ? '#ccc' : '#e5e7eb'}`;
                    circle.style.position = 'relative';
                    circle.style.overflow = 'hidden';

                    if (!inStock) {
                        const strikeLine = document.createElement('span');
                        strikeLine.className = 'swatch-strike-line';
                        strikeLine.innerHTML = '<span style="position: absolute; top: 50%; left: -10%; width: 120%; height: 2px; background-color: #dc2626; transform: translateY(-50%) rotate(-45deg); display: block; pointer-events: none;"></span>';
                        circle.appendChild(strikeLine);
                    }

                    circle.addEventListener('click', function (e) {
                        if (!inStock) {
                            e.preventDefault();
                            e.stopPropagation();
                            showOutOfStockPopup(colorName);
                            return false;
                        }

                        circlesContainer.querySelectorAll('.swatch-circle').forEach(c => {
                            c.classList.remove('active');
                        });
                        circle.classList.add('active');
                        titleElem.innerText = 'رنگ: ' + colorName;

                        select.value = opt.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        $(select).trigger('change');

                        const cleanColor = colorName.toLowerCase();
                        document.querySelectorAll('.lk-thumb-item').forEach(thumb => {
                            const img = thumb.querySelector('img');
                            if (img) {
                                const alt = (img.alt || '').toLowerCase();
                                const title = (img.title || '').toLowerCase();
                                if (alt.includes(cleanColor) || title.includes(cleanColor)) {
                                    thumb.click();
                                }
                            }
                        });

                        const alertBox = document.querySelector('.custom-variation-alert-box');
                        if (alertBox) alertBox.remove();
                    });

                    circleWrapper.appendChild(circle);

                    if (!inStock) {
                        const outText = document.createElement('span');
                        outText.className = 'swatch-out-text';
                        outText.innerText = 'ناموجود';
                        circleWrapper.appendChild(outText);
                    }

                    circlesContainer.appendChild(circleWrapper);
                });

                wrapper.appendChild(circlesContainer);
                select.style.display = 'none';
                select.parentNode.insertBefore(wrapper, select);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initCustomSwatches, 400);

        // سیستم تعویض تصویر اصلی گالری با انیمیشن روان و نرم
        const mainImg = document.getElementById('lk-main-img-target');
        if (mainImg) {
            // اعمال تگ transition برای نرم شدن تغییرات عکس
            mainImg.style.transition = 'opacity 0.25s ease-in-out, transform 0.25s ease-in-out';
        }

        document.addEventListener('click', function (e) {
            const thumbItem = e.target.closest('.lk-thumb-item');
            if (!thumbItem || !mainImg) return;

            const imgEl = thumbItem.querySelector('img');
            if (!imgEl) return;

            const fullSrc = thumbItem.getAttribute('data-full-src') || imgEl.getAttribute('src');
            if (!fullSrc) return;

            document.querySelectorAll('.lk-thumb-item').forEach(el => el.classList.remove('active'));
            thumbItem.classList.add('active');

            // انیمیشن نرم محو شدن و تغییر عکس
            mainImg.style.opacity = '0';
            mainImg.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                mainImg.src = fullSrc;
                mainImg.removeAttribute('srcset');
                mainImg.removeAttribute('sizes');
                mainImg.style.opacity = '1';
                mainImg.style.transform = 'scale(1)';
            }, 200);
        });

        // ==========================================
        // قابلیت ورق زدن (Swipe) نرم روی عکس اصلی در موبایل
        // ==========================================
        const mainImgContainer = document.getElementById('lk-main-img-target');
        if (mainImgContainer) {
            let touchStartX = 0;
            let touchEndX = 0;

            mainImgContainer.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, {passive: true});

            mainImgContainer.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, {passive: true});

            function handleSwipe() {
                const threshold = 40; // حساسیت مناسب‌تر برای سوایپ موبایل
                const thumbs = Array.from(document.querySelectorAll('.lk-thumb-item'));
                if (thumbs.length <= 1) return;

                let currentIndex = thumbs.findIndex(t => t.classList.contains('active'));
                if (currentIndex === -1) currentIndex = 0;

                if (touchEndX + threshold < touchStartX) {
                    // کشیدن به سمت چپ (رفتن به عکس بعدی)
                    let nextIndex = (currentIndex + 1) % thumbs.length;
                    thumbs[nextIndex].click();
                } else if (touchEndX > touchStartX + threshold) {
                    // کشیدن به سمت راست (رفتن به عکس قبلی)
                    let prevIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
                    thumbs[prevIndex].click();
                }
            }
        }

        // رفع باگ تاخیر کلیک روی دسکتاپ (اصلاح درگ اسکرول افقی تصاویر کوچک)
        const scrollContainer = document.querySelector('.lk-gallery-thumbs-scrollable');
        if (scrollContainer) {
            let isDown = false;
            let startX, scrollLeft;
            let hasMoved = false;

            scrollContainer.addEventListener('mousedown', (e) => {
                isDown = true;
                hasMoved = false;
                scrollContainer.classList.add('is-dragging');
                startX = e.pageX - scrollContainer.offsetLeft;
                scrollLeft = scrollContainer.scrollLeft;
            });

            scrollContainer.addEventListener('mouseleave', () => {
                isDown = false;
                scrollContainer.classList.remove('is-dragging');
            });

            scrollContainer.addEventListener('mouseup', () => {
                isDown = false;
                scrollContainer.classList.remove('is-dragging');
            });

            scrollContainer.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                const x = e.pageX - scrollContainer.offsetLeft;
                const walk = (x - startX);
                if (Math.abs(walk) > 5) { // اگر کاربر موس را جابجا کرد به عنوان درگ در نظر بگیر
                    hasMoved = true;
                    scrollContainer.scrollLeft = scrollLeft - walk;
                }
            });

            // جلوگیری از تداخل درگ با کلیک ساده روی تصاویر
            scrollContainer.addEventListener('click', (e) => {
                if (hasMoved) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                }
            }, true);
        }
    });

})(jQuery);

document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('lk-toggle-desc-btn');
    const descContent = document.getElementById('lk-desc-container');

    if (toggleBtn && descContent) {
        toggleBtn.addEventListener('click', function () {
            const spanText = toggleBtn.querySelector('span');
            const svgIcon = toggleBtn.querySelector('svg');

            if (!descContent.classList.contains('expanded')) {
                // محاسبه ارتفاع دقیق و واقعی محتوا و اعمال آن به عنوان max-height
                descContent.style.maxHeight = descContent.scrollHeight + 'px';
                descContent.classList.add('expanded');

                if (spanText) spanText.innerText = 'بستن متن';
                if (svgIcon) svgIcon.style.transform = 'rotate(180deg)';
            } else {
                // برگرداندن به حالت بسته اولیه (باید با مقدار اولیه CSS همخوانی داشته باشد)
                descContent.style.maxHeight = '250px'; 
                descContent.classList.remove('expanded');

                if (spanText) spanText.innerText = 'مشاهده ادامه متن';
                if (svgIcon) svgIcon.style.transform = 'rotate(0deg)';
            }
        });
    }
});





document.addEventListener('DOMContentLoaded', function() {
    var tocLinks = document.querySelectorAll('.lr-toc-link');
    
    tocLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // جلوگیری از پرش پیش‌فرض مرورگر
            
            var targetId = this.getAttribute('href');
            var targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // اسکرول نرم و دقیق به سمت سکشن مورد نظر
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // به‌روزرسانی آدرس بار بدون پرش صفحه
                if(history.pushState) {
                    history.pushState(null, null, targetId);
                }
            }
        });
    });
});


  /* ==========================================================
 * اسکریپت Scroll Reveal صفحه درباره ما - لیوراکالا
 * ========================================================== */
document.addEventListener("DOMContentLoaded", function() {
    const revealItems = document.querySelectorAll('.lk-reveal-item');
    
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.05
        };

        const observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('lk-in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealItems.forEach(el => {
            observer.observe(el);
        });
    } else {
        // Fallback برای مرورگرهای خیلی قدیمی که IntersectionObserver ندارند
        revealItems.forEach(el => {
            el.classList.add('lk-in-view');
        });
    }
});