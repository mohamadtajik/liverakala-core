<?php
if (!defined('ABSPATH')) {
    exit;
}

class LR_Comments_Schema {

    public function __construct() {
        add_action('wp_head', array($this, 'output_comment_schema'));
    }

    public function output_comment_schema() {
        if (!is_singular('product')) {
            return;
        }

        $comments = get_comments(array(
            'post_id' => get_the_ID(),
            'status'  => 'approve',
        ));

        if (empty($comments)) {
            return;
        }

        $total_rating = 0;
        $count        = 0;

        foreach ($comments as $comment) {
            $rating = get_comment_meta($comment->comment_ID, 'rating', true);
            if ($rating) {
                $total_rating += intval($rating);
                $count++;
            }
        }

        if ($count > 0) {
            $average = round($total_rating / $count, 1);
            
            $schema = array(
                '@context'    => 'https://schema.org/',
                '@type'       => 'Product',
                'name'        => get_the_title(),
                'aggregateRating' => array(
                    '@type'       => 'AggregateRating',
                    'ratingValue' => (string)$average,
                    'reviewCount' => (string)$count,
                    'bestRating'  => '5',
                    'worstRating' => '1'
                )
            );

            echo "\n<!-- LR Comments JSON-LD Schema -->\n";
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
}

new LR_Comments_Schema();