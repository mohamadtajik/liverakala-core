jQuery(document).ready(function($) {
    var $form = $('#lr-comment-form');

    // غیرفعال کردن اعتبارسنجی پیش‌فرض مرورگر
    $form.attr('novalidate', 'novalidate');
    $form.find('input, textarea').removeAttr('required');

    // ذخیره عنوان اصلی فرم
    var originalTitle = $('.lr-form-title').text();

    // کلیک روی دکمه پاسخ
    $(document).on('click', '.lr-reply-btn', function(e) {
        e.preventDefault();
        var commentId = $(this).data('commentid');
        var authorName = $(this).data('author');

        $('#lr_comment_parent').val(commentId);

        $('.lr-form-title').html('پاسخ به ' + authorName + ' <a href="#" id="lr-cancel-reply" style="font-size:12px; color:#e11d48; font-weight:600; margin-right:15px; text-decoration:none; font-family:iranYekan, sans-serif;">لغو پاسخ</a>');

        $('html, body').animate({
            scrollTop: $('#lr-comment-form').offset().top - 100
        }, 500);

        $('#lr_comment').focus();
    });

    // لغو پاسخ
    $(document).on('click', '#lr-cancel-reply', function(e) {
        e.preventDefault();
        $('#lr_comment_parent').val(0);
        $('.lr-form-title').text(originalTitle);
    });

    // ارسال فرم با اعتبارسنجی کاملاً سفارشی
    $form.on('submit', function(e) {
        e.preventDefault();

        var $submitBtn = $form.find('.lr-submit-btn');
        var $responseDiv = $('#lr-form-response');

        // پاک کردن خطاهای قبلی
        $form.find('.lr-form-group, .lr-rating-wrapper').removeClass('has-error');
        $form.find('.lr-field-error').remove();
        $responseDiv.hide().removeClass('success error');

        var author = $form.find('input[name="author"]').val();
        var email = $form.find('input[name="email"]').val();
        var comment = $form.find('textarea[name="comment"]').val();
        var rating = $form.find('input[name="rating"]:checked').val();
        var hasError = false;

        // تابع کمکی برای خطای فیلدها
        function showFieldError($input, message) {
            $input.closest('.lr-form-group').addClass('has-error');
            if ($input.closest('.lr-form-group').find('.lr-field-error').length === 0) {
                $input.after('<span class="lr-field-error">' + message + '</span>');
            }
            hasError = true;
        }

        // بررسی امتیاز محصول (اجباری فقط برای صفحه محصولات)
        var $ratingWrapper = $form.find('.lr-star-rating');
        if ($ratingWrapper.length && (!rating || rating == '0')) {
            $ratingWrapper.closest('.lr-rating-wrapper').addClass('has-error');
            if ($ratingWrapper.closest('.lr-rating-wrapper').find('.lr-field-error').length === 0) {
                $ratingWrapper.closest('.lr-rating-wrapper').after('<span class="lr-field-error" style="display:block; margin-top:-10px; margin-bottom:15px; padding-right:4px;">لطفاً برای ثبت نظر امتیاز خود را به محصول انتخاب کنید ⭐</span>');
            }
            hasError = true;
        }

        // بررسی فیلد نام (اجباری)
        var $authorInput = $form.find('input[name="author"]');
        if ($authorInput.length && (!author || author.trim() === '')) {
            showFieldError($authorInput, 'لطفاً نام و نام خانوادگی خود را وارد کنید.');
            hasError = true;
        }

        // بررسی فیلد ایمیل (اختیاری: فقط اگر کاربر چیزی وارد کرد، فرمتش بررسی شود)
        var $emailInput = $form.find('input[name="email"]');
        if ($emailInput.length && email && email.trim() !== '') {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError($emailInput, 'فرمت ایمیل وارد شده معتبر نمی‌باشد.');
                hasError = true;
            }
        }

        // بررسی متن دیدگاه (اجباری)
        var $commentInput = $form.find('textarea[name="comment"]');
        if (!comment || comment.trim() === '') {
            showFieldError($commentInput, 'متن دیدگاه نمی‌تواند خالی باشد.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // ارسال ایجکس
        var originalBtnText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('در حال ارسال...');

        var formData = {
            action: 'lr_submit_comment',
            security: lr_comment_params.nonce,
            post_id: $form.find('input[name="post_id"]').val(),
            parent: $form.find('input[name="parent"]').val(),
            comment: comment,
            rating: rating || 0,
            author: author || '',
            email: email || '',
            lr_hp_field: $form.find('input[name="lr_hp_field"]').val()
        };

        $.post(lr_comment_params.ajax_url, formData, function(response) {
            $submitBtn.prop('disabled', false).text(originalBtnText);

            if (response.success) {
                $responseDiv.addClass('success').text(response.data.message).fadeIn();
                $form[0].reset();
                $('#lr_comment_parent').val(0);
                $('.lr-form-title').text(originalTitle);
            } else {
                $responseDiv.addClass('error').text(response.data.message).fadeIn();
            }
        }).fail(function() { // اصلاح شده: حذف خطای نگارشی
            $submitBtn.prop('disabled', false).text(originalBtnText);
            $responseDiv.addClass('error').text('خطایی در ارتباط با سرور رخ داد.').fadeIn();
        });
    });
});