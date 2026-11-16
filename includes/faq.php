<?php
if (!defined('ABSPATH')) {
    exit;
}

class LiveRakala_Inline_Shortcode_FAQ {

    private static $captured_faqs = array();

    public function __construct() {
        add_shortcode('liverakala_faq', array($this, 'render_inline_faq_shortcode'));
        // با اولویت بالا اسکیما را دقیقاً در هدر تزریق می‌کنیم
        add_action('wp_head', array($this, 'inject_schema_to_head'), 1);
    }

    public function render_inline_faq_shortcode($atts, $content = null) {
        if (empty($content)) {
            return '';
        }

        // پاکسازی تگ‌های المنتور
        $content = str_replace(array('<br>', '<br />', '<br/>', '</p>', '<p>'), "\n", $content);
        $lines = explode("\n", strip_tags($content));
        
        $faqs = array();
        $current_q = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(Q|سوال)\s*:\s*(.+)$/ui', $line, $matches)) {
                $current_q = trim($matches[2]);
            } elseif (preg_match('/^(A|پاسخ|جواب)\s*:\s*(.+)$/ui', $line, $matches) && !empty($current_q)) {
                $faqs[] = array(
                    'q' => $current_q,
                    'a' => trim($matches[2])
                );
                $current_q = '';
            }
        }

        if (empty($faqs)) {
            return '';
        }

        self::$captured_faqs = $faqs;

        // ساخت خروجی HTML برای فرانت‌اند
        $html = '<section class="lr-faq-container" aria-label="سوالات متداول">';
        $html .= '<h2 class="lr-faq-title">سوالات متداول</h2>';

        $index = 0; // Lakkoofsa jalqabaa qopheessuuf
        foreach ($faqs as $faq) {
            // Gaaffii jalqabaaf qofa amalli 'open' akka dabalamu
            $open_attr = ($index === 0) ? ' open' : '';
            
            $html .= '<details class="lr-faq-item"' . $open_attr . '>';
            $html .= '<summary class="lr-faq-summary">';
            $html .= '<span>' . esc_html($faq['q']) . '</span>';
            $html .= '<svg class="lr-faq-icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>';
            $html .= '</summary>';
            $html .= '<div class="lr-faq-answer-wrap">';
            $html .= '<p class="lr-faq-answer">' . esc_html($faq['a']) . '</p>';
            $html .= '</div>';
            $html .= '</details>';
            
            $index++;
        }

        $html .= '</section>';

        return $html;
    }

    public function inject_schema_to_head() {
        if (!is_singular()) {
            return;
        }

        global $post;
        if (!$post) return;

        // استخراج محتوا و بررسی وجود شورت‌کد
        $content = $post->post_content;
        if (has_shortcode($content, 'liverakala_faq')) {
            preg_match('/\[liverakala_faq\](.*?)\[\/liverakala_faq\]/s', $content, $matches);
            if (!empty($matches[1])) {
                $faq_content = str_replace(array('<br>', '<br />', '<br/>', '</p>', '<p>'), "\n", $matches[1]);
                $lines = explode("\n", strip_tags($faq_content));
                
                $faqs = array();
                $current_q = '';

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    if (preg_match('/^(Q|سوال)\s*:\s*(.+)$/ui', $line, $q_matches)) {
                        $current_q = trim($q_matches[2]);
                    } elseif (preg_match('/^(A|پاسخ|جواب)\s*:\s*(.+)$/ui', $line, $a_matches) && !empty($current_q)) {
                        $faqs[] = array(
                            'q' => $current_q,
                            'a' => trim($a_matches[2])
                        );
                        $current_q = '';
                    }
                }

                if (!empty($faqs)) {
                    $main_entity = array();
                    foreach ($faqs as $faq) {
                        $main_entity[] = array(
                            '@type'          => 'Question',
                            'name'           => $faq['q'],
                            'acceptedAnswer' => array(
                                '@type' => 'Answer',
                                'text'  => $faq['a']
                            )
                        );
                    }

                    $schema = array(
                        '@context'   => 'https://schema.org',
                        '@type'      => 'FAQPage',
                        'mainEntity' => $main_entity
                    );

                    $json_output = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    if ($json_output) {
                        echo "\n<!-- NEW LiveRakala FAQ Schema -->\n";
                        echo '<script type="application/ld+json">' . $json_output . '</script>' . "\n";
                    }
                }
            }
        }
    }
}

new LiveRakala_Inline_Shortcode_FAQ();