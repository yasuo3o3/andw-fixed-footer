<?php
/**
 * Plugin Name: andW Fixed Footer
 * Description: スマホ向けの固定フッターバーを表示・管理するプラグイン。スクロール方向に応じてスライド表示されます。
 * Version: 0.0.3
 * Author: yasuo3o3
 * Author URI: https://yasuo-o.xyz/
 * License: GPLv2 or later
 * Text Domain: andw-fixed-footer
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ANDW_FIXED_FOOTER_VERSION', '0.0.3');
define('ANDW_FIXED_FOOTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ANDW_FIXED_FOOTER_PLUGIN_PATH', plugin_dir_path(__FILE__));

class ANDW_Fixed_Footer {

    private static $instance = null;
    private $option_name = 'andw_fixed_footer_options';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'andw_fixed_footer_add_admin_menu'));
        add_action('admin_init', array($this, 'andw_fixed_footer_settings_init'));
        add_action('wp_footer', array($this, 'andw_fixed_footer_output'));
        add_action('wp_enqueue_scripts', array($this, 'andw_fixed_footer_enqueue_scripts'));
    }

    public function andw_fixed_footer_add_admin_menu() {
        add_options_page(
            __('固定フッター設定', 'andw-fixed-footer'),
            __('固定フッター', 'andw-fixed-footer'),
            'manage_options',
            'andw-fixed-footer',
            array($this, 'andw_fixed_footer_options_page')
        );
    }

    public function andw_fixed_footer_settings_init() {
        register_setting(
            'andw_fixed_footer',
            $this->option_name,
            array(
                'sanitize_callback' => array($this, 'andw_fixed_footer_sanitize_options'),
                'autoload' => false
            )
        );

        add_settings_section(
            'andw_fixed_footer_general_section',
            __('全体設定', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_general_section_callback'),
            'andw_fixed_footer'
        );

        add_settings_section(
            'andw_fixed_footer_buttons_section',
            __('ボタン設定', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_buttons_section_callback'),
            'andw_fixed_footer'
        );

        add_settings_section(
            'andw_fixed_footer_fontawesome_section',
            __('Font Awesomeについて', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_fontawesome_section_callback'),
            'andw_fixed_footer'
        );

        add_settings_section(
            'andw_fixed_footer_bottom_section',
            __('下段住所帯設定', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_bottom_section_callback'),
            'andw_fixed_footer'
        );

        $this->andw_fixed_footer_add_settings_fields();
    }

    private function andw_fixed_footer_add_settings_fields() {
        // 全体設定フィールド
        add_settings_field(
            'enabled',
            __('プラグイン有効/無効', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_checkbox_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'enabled', 'description' => __('固定フッターを表示する', 'andw-fixed-footer'))
        );

        add_settings_field(
            'display_mode',
            __('表示モード', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_radio_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array(
                'field' => 'display_mode',
                'options' => array(
                    '2' => __('2分割', 'andw-fixed-footer'),
                    '3' => __('3分割', 'andw-fixed-footer'),
                    '4' => __('4分割', 'andw-fixed-footer'),
                    '5' => __('5分割', 'andw-fixed-footer'),
                    '6' => __('6分割', 'andw-fixed-footer')
                )
            )
        );

        add_settings_field(
            'button_height',
            __('上段ボタン高さ (px)', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_number_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'button_height', 'min' => 30, 'max' => 100)
        );

        add_settings_field(
            'max_screen_width',
            __('表示画面幅 (px)', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_number_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'max_screen_width', 'min' => 200, 'max' => 2000, 'description' => 'この幅以下でフッターを表示します（デフォルト: 768px）')
        );

        add_settings_field(
            'button_width_right_2',
            __('2分割時 右側ボタン幅 (%)', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_number_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'button_width_right_2', 'min' => 1, 'max' => 99)
        );

        add_settings_field(
            'button_width_left_3',
            __('3分割時 左側ボタン幅 (%)', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_number_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'button_width_left_3', 'min' => 1, 'max' => 98)
        );

        add_settings_field(
            'button_width_right_3',
            __('3分割時 右側ボタン幅 (%)', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_number_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'button_width_right_3', 'min' => 1, 'max' => 98)
        );

        add_settings_field(
            'show_close_button',
            __('閉じるボタンを表示', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_checkbox_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array('field' => 'show_close_button', 'description' => __('閉じるボタンを表示する', 'andw-fixed-footer'))
        );

        add_settings_field(
            'close_button_position',
            __('閉じるボタンの位置', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_radio_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_general_section',
            array(
                'field' => 'close_button_position',
                'options' => array(
                    'left' => __('左', 'andw-fixed-footer'),
                    'right' => __('右', 'andw-fixed-footer')
                )
            )
        );


        // 下段設定フィールド
        add_settings_field(
            'bottom_bg_color',
            __('下段背景色', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_color_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_bottom_section',
            array('field' => 'bottom_bg_color')
        );

        add_settings_field(
            'bottom_text_color',
            __('下段文字色', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_color_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_bottom_section',
            array('field' => 'bottom_text_color')
        );

        add_settings_field(
            'bottom_text',
            __('下段テキスト', 'andw-fixed-footer'),
            array($this, 'andw_fixed_footer_textarea_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_bottom_section',
            array('field' => 'bottom_text', 'description' => __('改行は自動で&lt;br&gt;に変換されます', 'andw-fixed-footer'))
        );

        // ボタン設定フィールド
        for ($i = 1; $i <= 6; $i++) {
            $this->andw_fixed_footer_add_button_fields($i);
        }
    }

    private function andw_fixed_footer_add_button_fields($button_num) {
        /* translators: %d is button number */
        $button_label = sprintf(__('ボタン%d', 'andw-fixed-footer'), $button_num);

        add_settings_field(
            "button_{$button_num}_enabled",
            /* translators: %s is button label */
            sprintf(__('%s 有効/無効', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_checkbox_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_enabled", 'description' =>
                /* translators: %s is button label */
                sprintf(__('%sを表示する', 'andw-fixed-footer'), $button_label))
        );

        add_settings_field(
            "button_{$button_num}_bg_color",
            /* translators: %s is button label */
            sprintf(__('%s 背景色', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_color_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_bg_color")
        );

        add_settings_field(
            "button_{$button_num}_text_color",
            /* translators: %s is button label */
            sprintf(__('%s 文字色', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_color_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_text_color")
        );

        add_settings_field(
            "button_{$button_num}_icon",
            /* translators: %s is button label */
            sprintf(__('%s アイコンコード', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_text_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_icon", 'description' => __('例: \\f095', 'andw-fixed-footer'))
        );

        add_settings_field(
            "button_{$button_num}_label",
            /* translators: %s is button label */
            sprintf(__('%s ラベルテキスト', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_text_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_label")
        );

        add_settings_field(
            "button_{$button_num}_url",
            /* translators: %s is button label */
            sprintf(__('%s リンクURL', 'andw-fixed-footer'), $button_label),
            array($this, 'andw_fixed_footer_url_callback'),
            'andw_fixed_footer',
            'andw_fixed_footer_buttons_section',
            array('field' => "button_{$button_num}_url")
        );
    }

    public function andw_fixed_footer_general_section_callback() {
        echo '<p>' . esc_html__('固定フッターの全体的な設定を行います。', 'andw-fixed-footer') . '</p>';
    }

    public function andw_fixed_footer_buttons_section_callback() {
        echo '<p>' . esc_html__('各ボタンの設定を行います。', 'andw-fixed-footer') . '</p>';
    }

    public function andw_fixed_footer_fontawesome_section_callback() {
        // Font Awesomeの読み込み状況を検出
        $fontawesome_detected = $this->detect_fontawesome();

        if ($fontawesome_detected) {
            echo '<div class="notice notice-success inline">';
            echo '<p><strong>✓ ' . esc_html__('Font Awesomeが検出されました', 'andw-fixed-footer') . '</strong></p>';
            echo '<p>' . esc_html__('Font Awesomeが正常に読み込まれているため、アイコンが表示されます。', 'andw-fixed-footer') . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning inline">';
            echo '<p><strong>⚠ ' . esc_html__('Font Awesomeが検出されませんでした', 'andw-fixed-footer') . '</strong></p>';
            echo '<p>' . esc_html__('このプラグインではボタンにFont Awesomeアイコンを使用します。', 'andw-fixed-footer') . '</p>';
            echo '<p>' . esc_html__('以下のいずれかの方法でFont Awesomeを読み込んでください：', 'andw-fixed-footer') . '</p>';
            echo '<ul>';
            echo '<li>' . sprintf(
                /* translators: %s: Link to Font Awesome plugin */
                esc_html__('%s（推奨）', 'andw-fixed-footer'),
                '<a href="https://ja.wordpress.org/plugins/font-awesome/" target="_blank" rel="noopener noreferrer">' . esc_html__('Font Awesome公式プラグイン', 'andw-fixed-footer') . '</a>'
            ) . '</li>';
            echo '<li>' . esc_html__('他のテーマやプラグインでFont Awesomeが既に読み込まれている場合は不要です', 'andw-fixed-footer') . '</li>';
            echo '</ul>';
            echo '</div>';
        }
    }

    private function detect_fontawesome() {
        global $wp_styles;

        if (!isset($wp_styles) || !is_object($wp_styles)) {
            return false;
        }

        // 登録されているスタイルをチェック
        if (isset($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                if (isset($style->src)) {
                    // Font Awesomeのパターンをチェック
                    if (preg_match('/font-?awesome/i', $style->src) ||
                        preg_match('/fa\.(min\.)?css/i', $style->src) ||
                        preg_match('/fontawesome/i', $handle)) {
                        return true;
                    }
                }
            }
        }

        // エンキューされているスタイルもチェック
        if (isset($wp_styles->queue)) {
            foreach ($wp_styles->queue as $handle) {
                if (preg_match('/font-?awesome/i', $handle) || preg_match('/fa$/i', $handle)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function andw_fixed_footer_bottom_section_callback() {
        echo '<p>' . esc_html__('下段の住所帯の設定を行います。', 'andw-fixed-footer') . '</p>';
    }

    public function andw_fixed_footer_checkbox_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : 0;
        echo '<input type="checkbox" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="1" ' . checked(1, $value, false) . ' />';
        if (isset($args['description'])) {
            echo '<label for="' . esc_attr($args['field']) . '">' . esc_html($args['description']) . '</label>';
        }
    }

    public function andw_fixed_footer_radio_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';

        foreach ($args['options'] as $val => $label) {
            echo '<input type="radio" id="' . esc_attr($args['field'] . '_' . $val) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($val) . '" ' . checked($val, $value, false) . ' />';
            echo '<label for="' . esc_attr($args['field'] . '_' . $val) . '">' . esc_html($label) . '</label><br>';
        }
    }

    public function andw_fixed_footer_number_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="number" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '"';
        if (isset($args['min'])) echo ' min="' . esc_attr($args['min']) . '"';
        if (isset($args['max'])) echo ' max="' . esc_attr($args['max']) . '"';
        echo ' />';
    }

    public function andw_fixed_footer_color_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="text" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" placeholder="#000000" />';
    }

    public function andw_fixed_footer_text_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="text" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function andw_fixed_footer_textarea_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<textarea id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function andw_fixed_footer_url_callback($args) {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="url" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    public function andw_fixed_footer_sanitize_options($input) {
        // nonce検証（CSRF攻撃対策）
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'andw_fixed_footer-options')) {
            wp_die(esc_html__('セキュリティチェックに失敗しました。', 'andw-fixed-footer'));
        }

        // 重複メッセージ防止: 既存の設定エラーをクリア
        global $wp_settings_errors;
        if (isset($wp_settings_errors)) {
            $wp_settings_errors = array_filter($wp_settings_errors, function($error) {
                return $error['setting'] !== 'general' || $error['code'] !== 'settings_updated';
            });
        }

        $sanitized = array();

        $sanitized['enabled'] = isset($input['enabled']) ? 1 : 0;
        $sanitized['display_mode'] = in_array($input['display_mode'], array('2', '3', '4', '5', '6')) ? $input['display_mode'] : '2';
        $sanitized['button_height'] = absint($input['button_height']);
        $sanitized['max_screen_width'] = absint($input['max_screen_width']);
        $sanitized['button_width_right_2'] = max(1, min(99, absint($input['button_width_right_2'])));
        $sanitized['button_width_left_3'] = max(1, min(98, absint($input['button_width_left_3'])));
        $sanitized['button_width_right_3'] = max(1, min(98, absint($input['button_width_right_3'])));
        $sanitized['show_close_button'] = isset($input['show_close_button']) ? 1 : 0;
        $sanitized['close_button_position'] = in_array($input['close_button_position'], array('left', 'right')) ? $input['close_button_position'] : 'right';

        $sanitized['bottom_bg_color'] = sanitize_hex_color($input['bottom_bg_color']);
        $sanitized['bottom_text_color'] = sanitize_hex_color($input['bottom_text_color']);
        $sanitized['bottom_text'] = sanitize_textarea_field($input['bottom_text']);

        for ($i = 1; $i <= 6; $i++) {
            $sanitized["button_{$i}_enabled"] = isset($input["button_{$i}_enabled"]) ? 1 : 0;
            $sanitized["button_{$i}_bg_color"] = sanitize_hex_color($input["button_{$i}_bg_color"]);
            $sanitized["button_{$i}_text_color"] = sanitize_hex_color($input["button_{$i}_text_color"]);
            $sanitized["button_{$i}_icon"] = sanitize_text_field($input["button_{$i}_icon"]);
            $sanitized["button_{$i}_label"] = sanitize_text_field($input["button_{$i}_label"]);
            $sanitized["button_{$i}_url"] = $this->andw_fixed_footer_sanitize_url($input["button_{$i}_url"]);
        }

        return $sanitized;
    }

    private function andw_fixed_footer_sanitize_url($url) {
        if (empty($url)) {
            return '';
        }

        $allowed_schemes = array('tel', 'mailto', 'http', 'https');
        $parsed_url = wp_parse_url($url);

        if (is_array($parsed_url) && isset($parsed_url['scheme']) && in_array($parsed_url['scheme'], $allowed_schemes)) {
            return esc_url_raw($url);
        }

        return '';
    }

    private function andw_fixed_footer_get_default_options() {
        return array(
            'enabled' => 1,
            'display_mode' => '2',
            'button_height' => 50,
            'max_screen_width' => 768,
            'button_width_right_2' => 50,
            'button_width_left_3' => 33,
            'button_width_right_3' => 33,
            'show_close_button' => 1,
            'close_button_position' => 'right',
            'bottom_bg_color' => '#333333',
            'bottom_text_color' => '#ffffff',
            'bottom_text' => '',
            'button_1_enabled' => 1,
            'button_1_bg_color' => '#007cba',
            'button_1_text_color' => '#ffffff',
            'button_1_icon' => '\\f095',
            'button_1_label' => __('電話', 'andw-fixed-footer'),
            'button_1_url' => 'tel:000-000-0000',
            'button_2_enabled' => 1,
            'button_2_bg_color' => '#28a745',
            'button_2_text_color' => '#ffffff',
            'button_2_icon' => '\\f0e0',
            'button_2_label' => __('メール', 'andw-fixed-footer'),
            'button_2_url' => 'mailto:info@example.com',
            'button_3_enabled' => 0,
            'button_3_bg_color' => '#ffc107',
            'button_3_text_color' => '#212529',
            'button_3_icon' => '\\f041',
            'button_3_label' => __('地図', 'andw-fixed-footer'),
            'button_3_url' => 'https://example.com/map',
            'button_4_enabled' => 0,
            'button_4_bg_color' => '#dc3545',
            'button_4_text_color' => '#ffffff',
            'button_4_icon' => '\\f015',
            'button_4_label' => __('ホーム', 'andw-fixed-footer'),
            'button_4_url' => 'https://example.com',
            'button_5_enabled' => 0,
            'button_5_bg_color' => '#6f42c1',
            'button_5_text_color' => '#ffffff',
            'button_5_icon' => '\\f0d6',
            'button_5_label' => __('予約', 'andw-fixed-footer'),
            'button_5_url' => 'https://example.com/booking',
            'button_6_enabled' => 0,
            'button_6_bg_color' => '#fd7e14',
            'button_6_text_color' => '#ffffff',
            'button_6_icon' => '\\f1ad',
            'button_6_label' => __('ニュース', 'andw-fixed-footer'),
            'button_6_url' => 'https://example.com/news',
        );
    }

    public function andw_fixed_footer_options_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('このページにアクセスする権限がありません。', 'andw-fixed-footer'));
        }

        // WordPressが自動でメッセージを表示するため、手動呼び出しは不要
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('andw_fixed_footer');
                do_settings_sections('andw_fixed_footer');
                submit_button(__('設定を保存', 'andw-fixed-footer'));
                ?>
            </form>
        </div>
        <?php
    }

    public function andw_fixed_footer_enqueue_scripts() {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());

        if (!$options['enabled']) {
            return;
        }


        wp_enqueue_style(
            'andw-fixed-footer-style',
            ANDW_FIXED_FOOTER_PLUGIN_URL . 'assets/css/andw-fixed-footer.css',
            array(),
            ANDW_FIXED_FOOTER_VERSION
        );

        // CSS変数として設定値を出力（ブラウザ互換性強化）
        $max_width = !empty($options['max_screen_width']) ? absint($options['max_screen_width']) : 768;
        $custom_css = "
        /* andW Fixed Footer 動的設定 */
        :root {
            --andw-max-width: {$max_width}px;
        }

        /* デバッグ用：CSS変数が適用されているかの確認 */
        .andw-fixed-footer-wrapper::before {
            content: 'MaxWidth: {$max_width}px';
            position: absolute;
            top: -20px;
            left: 10px;
            font-size: 10px;
            color: #666;
            background: rgba(255,255,255,0.8);
            padding: 2px 4px;
            border-radius: 2px;
            z-index: 10001;
        }

        /* 緊急フォールバック：CSS変数が効かない場合の基本表示 */
        @media (max-width: {$max_width}px) {
            .andw-fixed-footer-wrapper.andw-emergency-fallback {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 9999 !important;
                display: flex !important;
                flex-direction: column !important;
                background: #ffffff !important;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1) !important;
            }
        }
        ";
        wp_add_inline_style('andw-fixed-footer-style', $custom_css);

        wp_enqueue_script(
            'andw-fixed-footer-script',
            ANDW_FIXED_FOOTER_PLUGIN_URL . 'assets/js/andw-fixed-footer.js',
            array(),
            ANDW_FIXED_FOOTER_VERSION,
            true
        );

        // JavaScriptに設定値を渡す
        wp_localize_script('andw-fixed-footer-script', 'andwFooterSettings', array(
            'maxWidth' => !empty($options['max_screen_width']) ? absint($options['max_screen_width']) : 768
        ));
    }

    public function andw_fixed_footer_output() {
        $options = get_option($this->option_name, $this->andw_fixed_footer_get_default_options());

        if (!$options['enabled']) {
            return;
        }

        $display_mode = $options['display_mode'];
        $button_height = absint($options['button_height']);

        echo '<div id="andw-fixed-footer-wrapper" class="andw-fixed-footer-wrapper andw-close-' . esc_attr($options['close_button_position']) . '">';

        if ($options['show_close_button']) {
            echo '<button class="andw-close-button" aria-label="' . esc_attr__('閉じる', 'andw-fixed-footer') . '">&times;</button>';
        }

        echo '<div class="andw-footer-buttons" style="height: ' . esc_attr($button_height) . 'px;">';

        $buttons = $this->andw_fixed_footer_get_active_buttons($options, $display_mode);
        $button_widths = $this->andw_fixed_footer_calculate_widths($options, $display_mode, count($buttons));

        foreach ($buttons as $index => $button) {
            $width = isset($button_widths[$index]) ? $button_widths[$index] : 0;
            if ($width > 0) {
                $this->andw_fixed_footer_render_button($button, $width);
            }
        }

        echo '</div>';

        if (!empty($options['bottom_text'])) {
            $bottom_style = '';
            if (!empty($options['bottom_bg_color'])) {
                $bottom_style .= 'background-color: ' . esc_attr($options['bottom_bg_color']) . ';';
            }
            if (!empty($options['bottom_text_color'])) {
                $bottom_style .= 'color: ' . esc_attr($options['bottom_text_color']) . ';';
            }

            echo '<div class="andw-footer-bottom" style="' . esc_attr($bottom_style) . '">';
            echo wp_kses_post(nl2br(esc_html($options['bottom_text'])));
            echo '</div>';
        }

        echo '</div>';
    }

    private function andw_fixed_footer_get_active_buttons($options, $display_mode) {
        $buttons = array();
        $max_buttons = intval($display_mode);

        for ($i = 1; $i <= $max_buttons; $i++) {
            if (!empty($options["button_{$i}_enabled"])) {
                $buttons[] = array(
                    'bg_color' => $options["button_{$i}_bg_color"],
                    'text_color' => $options["button_{$i}_text_color"],
                    'icon' => $options["button_{$i}_icon"],
                    'label' => $options["button_{$i}_label"],
                    'url' => $options["button_{$i}_url"]
                );
            }
        }

        return $buttons;
    }

    private function andw_fixed_footer_calculate_widths($options, $display_mode, $active_count) {
        if ($active_count == 0) {
            return array();
        }

        $mode = intval($display_mode);

        // 2分割と3分割は既存の幅設定を使用（下位互換性）
        if ($mode == 2) {
            $right_width = absint($options['button_width_right_2']);
            $left_width = 100 - $right_width;
            return array($left_width, $right_width);
        } elseif ($mode == 3) {
            $left_width = absint($options['button_width_left_3']);
            $right_width = absint($options['button_width_right_3']);
            $center_width = 100 - $left_width - $right_width;
            return array($left_width, $center_width, $right_width);
        } else {
            // 4分割以上は均等分割
            $equal_width = 100 / $mode;
            $widths = array();
            for ($i = 0; $i < $mode; $i++) {
                $widths[] = $equal_width;
            }
            return $widths;
        }
    }

    private function andw_fixed_footer_render_button($button, $width) {
        $button_style = '';
        $button_style .= 'flex-basis: ' . esc_attr($width) . '%;';
        if (!empty($button['bg_color'])) {
            $button_style .= 'background-color: ' . esc_attr($button['bg_color']) . ';';
        }
        if (!empty($button['text_color'])) {
            $button_style .= 'color: ' . esc_attr($button['text_color']) . ';';
        }

        $url = !empty($button['url']) ? $button['url'] : '#';

        echo '<a href="' . esc_url($url) . '" class="andw-footer-button" style="' . esc_attr($button_style) . '">';

        if (!empty($button['icon'])) {
            echo '<span class="andw-button-icon" data-icon="' . esc_attr($button['icon']) . '"></span>';
        }

        if (!empty($button['label'])) {
            echo '<span class="andw-button-label">' . esc_html($button['label']) . '</span>';
        }

        echo '</a>';
    }
}

ANDW_Fixed_Footer::get_instance();