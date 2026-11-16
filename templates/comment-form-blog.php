<?php
if (!defined('ABSPATH')) {
    exit;
}

$all_comments = get_comments(array(
    'post_id' => get_the_ID(),
    'status'  => 'approve',
    'orderby' => 'comment_date_gmt',
    'order'   => 'ASC',
));

$comment_tree = array();
foreach ($all_comments as $comment) {
    if ($comment->comment_parent == 0) {
        $comment_tree[$comment->comment_ID] = array(
            'comment'  => $comment,
            'children' => array()
        );
    } else {
        if (isset($comment_tree[$comment->comment_parent])) {
            $comment_tree[$comment->comment_parent]['children'][] = $comment;
        }
    }
}
?>

<div class="lr-comments-section">
    <h3 class="lr-comments-section-title">نظرات مقاله (<?php echo count($all_comments); ?>)</h3>

    <?php if (!empty($comment_tree)) : ?>
        <ul class="lr-comments-list">
            <?php foreach ($comment_tree as $item) : 
                $comment = $item['comment'];
                $is_admin = user_can($comment->user_id, 'administrator'); // بررسی ادمین بودن برای والد
                $first_letter = mb_substr($comment->comment_author, 0, 1, 'UTF-8');
            ?>
                <li class="lr-comment-item" id="div-comment-<?php echo $comment->comment_ID; ?>">
                    <div class="lr-comment-header">
                        <div class="lr-comment-header-right">
                            <div class="lr-comment-avatar"><?php echo esc_html($first_letter); ?></div>
                            <span class="lr-comment-author"><?php echo esc_html($comment->comment_author); ?></span>
                            <?php if ($is_admin) : ?>
                                <span class="lr-admin-badge">پاسخ مدیر</span>
                            <?php endif; ?>
                        </div>
                        <span class="lr-comment-date"><?php echo get_comment_date('j F Y', $comment->comment_ID); ?></span>
                    </div>

                    <div class="lr-comment-body">
                        <?php echo wpautop($comment->comment_content); ?>
                    </div>

                    <a href="#lr-comment-form" class="lr-reply-btn" data-commentid="<?php echo $comment->comment_ID; ?>" data-author="<?php echo esc_attr($comment->comment_author); ?>">پاسخ</a>

                    <?php if (!empty($item['children'])) : ?>
                        <ul class="lr-children-comments">
                            <?php foreach ($item['children'] as $child) : 
                                $is_child_admin = user_can($child->user_id, 'administrator');
                                $child_letter = mb_substr($child->comment_author, 0, 1, 'UTF-8');
                            ?>
                                <li class="lr-comment-item lr-reply-item" id="div-comment-<?php echo $child->comment_ID; ?>">
                                    <div class="lr-comment-header">
                                        <div class="lr-comment-header-right">
                                            <div class="lr-comment-avatar"><?php echo esc_html($child_letter); ?></div>
                                            <span class="lr-comment-author"><?php echo esc_html($child->comment_author); ?></span>
                                            <?php if ($is_child_admin) : ?>
                                                <span class="lr-admin-badge">پاسخ مدیر</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="lr-comment-date"><?php echo get_comment_date('j F Y', $child->comment_ID); ?></span>
                                    </div>
                                    <div class="lr-comment-body">
                                        <?php echo wpautop($child->comment_content); ?>
                                    </div>
                                    <a href="#lr-comment-form" class="lr-reply-btn" data-commentid="<?php echo $child->comment_ID; ?>" data-author="<?php echo esc_attr($child->comment_author); ?>">پاسخ</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p class="lr-no-comments">اولین نفری باشید که برای این مقاله نظر می‌دهید.</p>
    <?php endif; ?>
</div>

<div class="lr-comments-wrapper" id="lr-comment-form-wrap">
    <h3 class="lr-form-title">ارسال دیدگاه</h3>
    
    <form id="lr-comment-form" method="post" novalidate>
        <div style="display:none;" aria-hidden="true">
            <input type="text" name="lr_hp_field" value="" tabindex="-1" autocomplete="off">
        </div>

        <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
        <input type="hidden" name="parent" id="lr_comment_parent" value="0">

        <?php if (!is_user_logged_in()) : ?>
            <div class="lr-form-row">
                <div class="lr-form-group">
                    <label for="lr_author">نام و نام خانوادگی *</label>
                    <input type="text" name="author" id="lr_author">
                </div>
                <div class="lr-form-group">
                    <label for="lr_email">ایمیل (اختیاری)</label>
                    <input type="email" name="email" id="lr_email" placeholder="example@gmail.com" dir="ltr" style="text-align: right;">
                </div>
            </div>
        <?php endif; ?>

        <div class="lr-form-group">
            <label for="lr_comment">دیدگاه شما *</label>
            <textarea name="comment" id="lr_comment" rows="4"></textarea>
        </div>

        <button type="submit" class="lr-submit-btn">ارسال نظر</button>
        <div id="lr-form-response" class="lr-response-message"></div>
    </form>
</div>