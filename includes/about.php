<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ثبت شورت‌کد صفحه درباره ما
function liverakala_about_us_shortcode() {
    ob_start();
    ?>
    <div class="lk-about-wrapper">
        <div class="lk-about-container">
            
            <div class="lk-about-hero lk-reveal-item">
                <div class="lk-about-badge">معماری نوین تجارت الکترونیک</div>
                <h1>فروشگاه لیوراکالا؛ خلق تجربه‌ای بی‌نقص در دنیای لوازم خانگی</h1>
                <p>ما باور داریم که خرید آنلاین لوازم خانگی نباید تنها یک معامله باشد؛ بلکه باید یک تجربه لذت بخش، مطمئن و الهام‌بخش برای هر خریدار باشد.</p>
            </div>

            <!-- بخش مزیت‌های رقابتی و ارزش‌های کلیدی (جایگزین آمار عددی) -->
            <div class="lk-about-stats lk-reveal-item">
                <div class="lk-stat-card">
                    <div class="lk-feature-icon">🚀</div>
                    <div class="lk-feature-title">ارسال اکسپرس و آنی</div>
                    <div class="lk-feature-desc">پوشش سراسری با بسته‌بندی ایمن و تحویل سریع</div>
                </div>
                <div class="lk-stat-card">
                    <div class="lk-feature-icon">🔍</div>
                    <div class="lk-feature-title">شفافیت کامل اطلاعات</div>
                    <div class="lk-feature-desc">مشخصات فنی دقیق، نقد و بررسی‌های بی‌طرفانه و واقعی</div>
                </div>
                <div class="lk-stat-card">
                    <div class="lk-feature-icon">📞</div>
                    <div class="lk-feature-title">پشتیبانی تخصصی </div>
                    <div class="lk-feature-desc">مشاوره فنی پیش و پس از خرید با کارشناس خبره</div>
                </div>
                <div class="lk-stat-card">
                    <div class="lk-feature-icon">💎</div>
                    <div class="lk-feature-title">تضمین اصالت کالا</div>
                    <div class="lk-feature-desc">همراه با معتبرترین ضمانت‌ها و گارانتی‌های رسمی</div>
                </div>
            </div>

            <div class="lk-story-section lk-reveal-item">
                <div class="lk-story-content">
                    <h2>از یک ایده کوچک تا پیشروترین پلتفرم تخصصی</h2>
                    <p>لیوراکالا با هدف پر کردن خلاء موجود در بازار لوازم هوشمند و دیجیتال رده‌بالا متولد شد. چالش اصلی ما همیشه ساده‌سازی فرآیندهای پیچیده خرید و ارائه دقیق‌ترین اطلاعات فنی به مخاطب بوده است.</p>
                    <p>امروز با تکیه بر استانداردهای مدرن رابط کاربری، سیستم‌های انبارداری پیشرفته و تیمی متشکل از خبره‌ترین متخصصان، توانسته‌ایم استانداردهای جدیدی را در کیفیت خدمات‌رسانی آنلاین بنیان‌گذاری کنیم.</p>
                </div>
                <div class="lk-story-image">LiveraKala Story</div>
            </div>

            <div class="lk-section-header lk-reveal-item">
                <h2>ارزش‌های بنیادین لیوراکالا</h2>
                <p>اصولی که راهنمای هر تصمیم و اقدام ما در مسیر رشد است</p>
            </div>

            <div class="lk-bento-grid lk-reveal-item">
                <div class="lk-bento-card large">
                    <div class="lk-bento-icon">🎯</div>
                    <h3>تمرکز مطلق بر تجربه کاربری</h3>
                    <p>ما تک‌تک اجزای پلتفرم خود را با دقت مهندسی می‌کنیم تا خریدی بدون اصطکاک، سریع و لذت‌بخش داشته باشید.</p>
                </div>
                <div class="lk-bento-card">
                    <div class="lk-bento-icon">⚡</div>
                    <h3>سرعت و عملکرد بی‌نظیر</h3>
                    <p>استفاده از ساختارهای بهینه و تکنولوژی‌های روز وب برای دسترسی آنی به محصولات.</p>
                </div>
                <div class="lk-bento-card">
                    <div class="lk-bento-icon">🛡️</div>
                    <h3>اصالت و سلامت گارانتی</h3>
                    <p>تعهد صددرصدی به ارائه کالاهای اورجینال همراه با معتبرترین ضمانت‌های اجرایی.</p>
                </div>
                <div class="lk-bento-card large">
                    <div class="lk-bento-icon">💎</div>
                    <h3>نوآوری مستمر در سبد محصولات</h3>
                    <p>رصد مداوم تکنولوژی‌های روز دنیا و گلچین کردن برترین برندهای جهانی برای سخت‌پسندان بازار ایران، ویژگی اصلی خانواده لیوراکالا است.</p>
                </div>
            </div>

            <div class="lk-section-header lk-reveal-item" style="margin-top: 60px;">
                <h2>مغزهای متفکر پشت صحنه</h2>
                <p>تیمی پرانگیزه که برای ساختن بهترین تجربه روزانه شما تلاش می‌کنند</p>
            </div>

            <div class="lk-team-grid lk-reveal-item">
                <div class="lk-team-card">
                    <div class="lk-team-avatar" style="background: linear-gradient(135deg, #f43f5e, #e11d48);">LK</div>
                    <div class="lk-team-name">تیم نوآوری و معماری محصول</div>
                    <div class="lk-team-role">طراحی تجربه خرید هوشمند و باکیفیت</div>
                </div>
                <div class="lk-team-card">
                    <div class="lk-team-avatar" style="background: linear-gradient(135deg, #0f172a, #475569);">LK</div>
                    <div class="lk-team-name">تیم پشتیبانی و تجربه مشتری</div>
                    <div class="lk-team-role">مشاوره تخصصی و همراهی پیش و پس از خرید</div>
                </div>
                <div class="lk-team-card">
                    <div class="lk-team-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">LK</div>
                    <div class="lk-team-name">تیم تامین و ارزیابی کالا</div>
                    <div class="lk-team-role">ارزیابی اصالت و بسته بندی امن کالاها </div>
                </div>
            </div>
            <div class="lk-contact-cta-section lk-reveal-item">
                <div class="lk-cta-content">
                    <h3>سوالی دارید یا به مشاوره تخصصی نیازمندید؟</h3>
                    <p>کارشناسان لیوراکالا آماده پاسخگویی به سوالات شما هستند.</p>
                    <div class="lk-cta-actions">
                        <a href="/contact-us" class="lk-cta-btn">تماس با ما</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ثبت شورت‌کد
if ( ! shortcode_exists( 'liverakala_about_us' ) ) {
    add_shortcode( 'liverakala_about_us', 'liverakala_about_us_shortcode' );
}