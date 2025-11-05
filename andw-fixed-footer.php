<?php
/**
 * Plugin Name: andW Fixed Footer
 * Description: A mobile-first fixed footer bar plugin with scroll-based show/hide behavior for smartphones.
 * Version: 0.2.1
 * Author: yasuo3o3
 * Author URI: https://yasuo-o.xyz/
 * License: GPLv2 or later
 * Text Domain: andw-fixed-footer
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * Note: Release ZIP files must be named 'andw-fixed-footer.zip' (without version suffix)
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ANDWFF_VERSION', '0.2.1');
define('ANDWFF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ANDWFF_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Backward compatibility constants
define('ANDW_FIXED_FOOTER_VERSION', ANDWFF_VERSION);
define('ANDW_FIXED_FOOTER_PLUGIN_URL', ANDWFF_PLUGIN_URL);
define('ANDW_FIXED_FOOTER_PLUGIN_PATH', ANDWFF_PLUGIN_PATH);

class ANDWFF_Fixed_Footer {

    private static $instance = null;
    private $option_name = 'andwff_options';
    private $legacy_option_name = 'andw_fixed_footer_options';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Migrate legacy options on first load
        add_action('init', array($this, 'andwff_migrate_options'));

        add_action('admin_menu', array($this, 'andwff_add_admin_menu'));
        add_action('admin_init', array($this, 'andwff_settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'andwff_admin_enqueue_scripts'));
        add_action('wp_footer', array($this, 'andwff_output'));
        add_action('wp_enqueue_scripts', array($this, 'andwff_enqueue_scripts'));
    }

    public function andwff_migrate_options() {
        // Only run once
        if (get_option('andwff_migration_done', false)) {
            return;
        }

        // Check if legacy options exist
        $legacy_options = get_option($this->legacy_option_name, false);
        if ($legacy_options !== false) {
            // Copy to new option name
            update_option($this->option_name, $legacy_options);
            // Mark migration as complete
            update_option('andwff_migration_done', true);
        }
    }

    public function andwff_add_admin_menu() {
        add_options_page(
            __('Fixed Footer Settings', 'andw-fixed-footer'),
            __('Fixed Footer', 'andw-fixed-footer'),
            'manage_options',
            'andw-fixed-footer',
            array($this, 'andwff_options_page')
        );
    }

    public function andwff_settings_init() {
        register_setting(
            'andwff_settings',
            $this->option_name,
            array(
                'sanitize_callback' => array($this, 'andwff_sanitize_options'),
                'autoload' => false
            )
        );

        // General settings tab sections
        add_settings_section(
            'andwff_general_section',
            __('General Settings', 'andw-fixed-footer'),
            array($this, 'andwff_general_section_callback'),
            'andwff_general'
        );

        add_settings_section(
            'andwff_fontawesome_section',
            __('About Font Awesome', 'andw-fixed-footer'),
            array($this, 'andwff_fontawesome_section_callback'),
            'andwff_general'
        );

        // Button settings tab sections
        add_settings_section(
            'andwff_buttons_section',
            __('Button Settings', 'andw-fixed-footer'),
            array($this, 'andwff_buttons_section_callback'),
            'andwff_buttons'
        );

        add_settings_section(
            'andwff_bottom_section',
            __('Bottom Bar Settings', 'andw-fixed-footer'),
            array($this, 'andwff_bottom_section_callback'),
            'andwff_buttons'
        );

        // Display page settings tab sections
        add_settings_section(
            'andwff_exclusion_section',
            __('Display Page Settings', 'andw-fixed-footer'),
            array($this, 'andwff_exclusion_section_callback'),
            'andwff_pages'
        );

        $this->andwff_add_settings_fields();
    }

    private function andwff_add_settings_fields() {
        // General settings fields
        add_settings_field(
            'enabled',
            __('Plugin Enable/Disable', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'enabled', 'description' => __('Display fixed footer', 'andw-fixed-footer'))
        );

        add_settings_field(
            'display_mode',
            __('Display Mode', 'andw-fixed-footer'),
            array($this, 'andwff_radio_callback'),
            'andwff_general',
            'andwff_general_section',
            array(
                'field' => 'display_mode',
                'options' => array(
                    '2' => __('2 Columns', 'andw-fixed-footer'),
                    '3' => __('3 Columns', 'andw-fixed-footer'),
                    '4' => __('4 Columns', 'andw-fixed-footer'),
                    '5' => __('5 Columns', 'andw-fixed-footer'),
                    '6' => __('6 Columns', 'andw-fixed-footer')
                )
            )
        );

        add_settings_field(
            'button_height',
            __('Button Height (px)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'button_height', 'min' => 30, 'max' => 100)
        );

        add_settings_field(
            'max_screen_width',
            __('Display Screen Width (px)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'max_screen_width', 'min' => 200, 'max' => 2000, 'description' => __('Display footer when screen width is at or below this value (default: 768px)', 'andw-fixed-footer'))
        );

        add_settings_field(
            'button_width_right_2',
            __('2-Column Right Button Width (%)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'button_width_right_2', 'min' => 1, 'max' => 99)
        );

        add_settings_field(
            'button_width_left_3',
            __('3-Column Left Button Width (%)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'button_width_left_3', 'min' => 1, 'max' => 98)
        );

        add_settings_field(
            'button_width_right_3',
            __('3-Column Right Button Width (%)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'button_width_right_3', 'min' => 1, 'max' => 98)
        );

        add_settings_field(
            'show_close_button',
            __('Show Close Button', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'show_close_button', 'description' => __('Display a close button for the footer', 'andw-fixed-footer'))
        );

        add_settings_field(
            'close_button_position',
            __('Close Button Position', 'andw-fixed-footer'),
            array($this, 'andwff_radio_callback'),
            'andwff_general',
            'andwff_general_section',
            array(
                'field' => 'close_button_position',
                'options' => array(
                    'left' => __('Left', 'andw-fixed-footer'),
                    'right' => __('Right', 'andw-fixed-footer')
                )
            )
        );

        add_settings_field(
            'button_label_font_size',
            __('Button Label Font Size (px)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'button_label_font_size', 'description' => __('Font size for all button label text', 'andw-fixed-footer'))
        );

        add_settings_field(
            'bottom_text_font_size',
            __('Bottom Text Font Size (px)', 'andw-fixed-footer'),
            array($this, 'andwff_number_callback'),
            'andwff_general',
            'andwff_general_section',
            array('field' => 'bottom_text_font_size', 'description' => __('Font size for bottom area text', 'andw-fixed-footer'))
        );

        // 除外設定フィールド
        add_settings_field(
            'exclusion_mode',
            __('Exclusion Mode', 'andw-fixed-footer'),
            array($this, 'andwff_radio_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array(
                'field' => 'exclusion_mode',
                'options' => array(
                    'blacklist' => __('Hide on specified pages', 'andw-fixed-footer'),
                    'whitelist' => __('Show only on specified pages', 'andw-fixed-footer')
                )
            )
        );

        add_settings_field(
            'exclude_home',
            __('Hide on Home Page', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'exclude_home', 'description' => __('Hide the footer on home page (front page)', 'andw-fixed-footer'))
        );

        add_settings_field(
            'exclude_pages',
            __('Hide on Static Pages', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'exclude_pages', 'description' => __('Hide the footer on all static pages', 'andw-fixed-footer'))
        );

        add_settings_field(
            'exclude_posts',
            __('Hide on Posts', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'exclude_posts', 'description' => __('Hide the footer on all post pages', 'andw-fixed-footer'))
        );

        add_settings_field(
            'exclude_categories',
            __('Hide on Category Pages', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'exclude_categories', 'description' => __('Hide the footer on all category pages', 'andw-fixed-footer'))
        );

        add_settings_field(
            'exclude_search',
            __('Hide on Search Results', 'andw-fixed-footer'),
            array($this, 'andwff_checkbox_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'exclude_search', 'description' => __('Hide the footer on search results pages', 'andw-fixed-footer'))
        );

        add_settings_field(
            'excluded_page_ids',
            __('Excluded Page IDs', 'andw-fixed-footer'),
            array($this, 'andwff_text_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'excluded_page_ids', 'description' => __('Enter IDs of pages/posts to exclude, separated by commas (e.g., 1,5,12)', 'andw-fixed-footer'))
        );

        add_settings_field(
            'excluded_url_patterns',
            __('Excluded URL Patterns', 'andw-fixed-footer'),
            array($this, 'andwff_textarea_callback'),
            'andwff_pages',
            'andwff_exclusion_section',
            array('field' => 'excluded_url_patterns', 'description' => __('Enter URL patterns to exclude, one per line (e.g., /contact/, /privacy/)', 'andw-fixed-footer'))
        );

        // 下段設定フィールド
        add_settings_field(
            'bottom_bg_color',
            __('Bottom Background Color', 'andw-fixed-footer'),
            array($this, 'andwff_color_callback'),
            'andwff_buttons',
            'andwff_bottom_section',
            array('field' => 'bottom_bg_color')
        );

        add_settings_field(
            'bottom_text_color',
            __('Bottom Text Color', 'andw-fixed-footer'),
            array($this, 'andwff_color_callback'),
            'andwff_buttons',
            'andwff_bottom_section',
            array('field' => 'bottom_text_color')
        );

        add_settings_field(
            'bottom_text',
            __('Bottom Text', 'andw-fixed-footer'),
            array($this, 'andwff_textarea_callback'),
            'andwff_buttons',
            'andwff_bottom_section',
            array('field' => 'bottom_text', 'description' => __('Line breaks are automatically converted to &lt;br&gt;', 'andw-fixed-footer'))
        );

        // ボタン設定フィールド
        for ($i = 1; $i <= 6; $i++) {
            $this->andwff_add_button_fields($i);
        }
    }

    private function andwff_add_button_fields($button_num) {
        /* translators: %d is button number */
        $button_label = sprintf(__('Button %d', 'andw-fixed-footer'), $button_num);

        add_settings_field(
            "button_{$button_num}_enabled",
            /* translators: %s is button label */
            sprintf(__('%s Enable/Disable', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_checkbox_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_enabled", 'description' =>
                /* translators: %s is button label */
                sprintf(__('Display %s', 'andw-fixed-footer'), $button_label))
        );

        add_settings_field(
            "button_{$button_num}_bg_color",
            /* translators: %s is button label */
            sprintf(__('%s Background Color', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_color_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_bg_color")
        );

        add_settings_field(
            "button_{$button_num}_text_color",
            /* translators: %s is button label */
            sprintf(__('%s Text Color', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_color_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_text_color")
        );

        add_settings_field(
            "button_{$button_num}_icon",
            /* translators: %s is button label */
            sprintf(__('%s Icon Code', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_text_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_icon", 'description' => __('Example: \\f095', 'andw-fixed-footer'))
        );

        add_settings_field(
            "button_{$button_num}_label",
            /* translators: %s is button label */
            sprintf(__('%s Label Text', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_text_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_label")
        );

        add_settings_field(
            "button_{$button_num}_url",
            /* translators: %s is button label */
            sprintf(__('%s Link URL', 'andw-fixed-footer'), $button_label),
            array($this, 'andwff_url_callback'),
            'andwff_buttons',
            'andwff_buttons_section',
            array('field' => "button_{$button_num}_url")
        );
    }

    public function andwff_general_section_callback() {
        echo '<p>' . esc_html__('Configure general settings for the fixed footer.', 'andw-fixed-footer') . '</p>';
    }

    public function andwff_buttons_section_callback() {
        echo '<p>' . esc_html__('Configure settings for each button.', 'andw-fixed-footer') . '</p>';
    }

    public function andwff_fontawesome_section_callback() {
        // Font Awesomeの読み込み状況を検出
        $fontawesome_detected = $this->andwff_detect_fontawesome();

        if ($fontawesome_detected) {
            echo '<div class="notice notice-success inline">';
            echo '<p><strong>✓ ' . esc_html__('Font Awesome detected', 'andw-fixed-footer') . '</strong></p>';
            echo '<p>' . esc_html__('Font Awesome is loaded properly, so icons will be displayed.', 'andw-fixed-footer') . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning inline">';
            echo '<p><strong>⚠ ' . esc_html__('Font Awesome not detected', 'andw-fixed-footer') . '</strong></p>';
            echo '<p>' . esc_html__('This plugin uses Font Awesome icons for buttons.', 'andw-fixed-footer') . '</p>';
            echo '<p>' . esc_html__('Please load Font Awesome using one of the following methods:', 'andw-fixed-footer') . '</p>';
            echo '<ul>';
            echo '<li>' . sprintf(
                /* translators: %s: Link to Font Awesome plugin */
                esc_html__('%s (Recommended)', 'andw-fixed-footer'),
                '<a href="https://wordpress.org/plugins/font-awesome/" target="_blank" rel="noopener noreferrer">' . esc_html__('Font Awesome Official Plugin', 'andw-fixed-footer') . '</a>'
            ) . '</li>';
            echo '<li>' . esc_html__('Not required if Font Awesome is already loaded by other themes or plugins', 'andw-fixed-footer') . '</li>';
            echo '</ul>';
            echo '</div>';
        }
    }

    private function andwff_detect_fontawesome() {
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

    private function andwff_should_exclude_current_page($options) {
        $mode = isset($options['exclusion_mode']) ? $options['exclusion_mode'] : 'blacklist';
        $is_excluded = false;

        // ページタイプによる除外判定
        if (!empty($options['exclude_home']) && is_home()) {
            $is_excluded = true;
        }

        if (!empty($options['exclude_pages']) && is_page()) {
            $is_excluded = true;
        }

        if (!empty($options['exclude_posts']) && is_single()) {
            $is_excluded = true;
        }

        if (!empty($options['exclude_categories']) && is_category()) {
            $is_excluded = true;
        }

        if (!empty($options['exclude_search']) && is_search()) {
            $is_excluded = true;
        }

        // 個別ページIDによる除外判定
        if (!empty($options['excluded_page_ids'])) {
            $excluded_ids = array_map('intval', array_filter(explode(',', $options['excluded_page_ids'])));
            if (!empty($excluded_ids)) {
                $current_id = get_queried_object_id();
                if (in_array($current_id, $excluded_ids)) {
                    $is_excluded = true;
                }
            }
        }

        // URLパターンによる除外判定
        if (!empty($options['excluded_url_patterns'])) {
            if (!isset($_SERVER['REQUEST_URI'])) {
                $current_url = '';
            } else {
                $current_url = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            }

            if (!empty($current_url)) {
                $patterns = array_filter(explode("\n", $options['excluded_url_patterns']));

                foreach ($patterns as $pattern) {
                    $pattern = trim($pattern);
                    if (!empty($pattern)) {
                        // シンプルな部分一致判定（ワイルドカード的動作）
                        if (strpos($current_url, $pattern) !== false) {
                            $is_excluded = true;
                            break;
                        }
                    }
                }
            }
        }

        // モードに応じて結果を返す
        if ($mode === 'whitelist') {
            // ホワイトリストモード：除外されていない場合のみ表示
            return !$is_excluded;
        } else {
            // ブラックリストモード：除外されている場合は非表示
            return $is_excluded;
        }
    }

    public function andwff_exclusion_section_callback() {
        echo '<p>' . esc_html__('Configure pages where the fixed footer is displayed or hidden.', 'andw-fixed-footer') . '</p>';
    }

    public function andwff_bottom_section_callback() {
        echo '<p>' . esc_html__('Configure the bottom bar settings.', 'andw-fixed-footer') . '</p>';
    }

    public function andwff_checkbox_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : 0;
        echo '<input type="checkbox" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="1" ' . checked(1, $value, false) . ' />';
        if (isset($args['description'])) {
            echo '<label for="' . esc_attr($args['field']) . '">' . esc_html($args['description']) . '</label>';
        }
    }

    public function andwff_radio_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';

        foreach ($args['options'] as $val => $label) {
            echo '<input type="radio" id="' . esc_attr($args['field'] . '_' . $val) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($val) . '" ' . checked($val, $value, false) . ' />';
            echo '<label for="' . esc_attr($args['field'] . '_' . $val) . '">' . esc_html($label) . '</label><br>';
        }
    }

    public function andwff_number_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="number" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '"';
        if (isset($args['min'])) echo ' min="' . esc_attr($args['min']) . '"';
        if (isset($args['max'])) echo ' max="' . esc_attr($args['max']) . '"';
        echo ' />';
    }

    public function andwff_color_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="text" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" placeholder="#000000" />';
    }

    public function andwff_text_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="text" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function andwff_textarea_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<textarea id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function andwff_url_callback($args) {
        $options = get_option($this->option_name, $this->andwff_get_default_options());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo '<input type="url" id="' . esc_attr($args['field']) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    public function andwff_sanitize_options($input) {
        // nonceチェック（CSRF対策）
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'andwff_settings-options')) {
            wp_die(esc_html__('Security check failed.', 'andw-fixed-footer'));
        }

        // 重複メッセージ防止: 既存の設定エラーをクリア
        global $wp_settings_errors;
        if (isset($wp_settings_errors)) {
            $wp_settings_errors = array_filter($wp_settings_errors, function($error) {
                return $error['setting'] !== 'general' || $error['code'] !== 'settings_updated';
            });
        }

        // 既存の設定値を取得（未送信フィールド保持用）
        $existing_options = get_option($this->option_name, $this->andwff_get_default_options());
        $sanitized = $existing_options;

        // 送信元タブを判定
        $current_tab = isset($_POST['andwff_current_tab'])
            ? sanitize_key(wp_unslash($_POST['andwff_current_tab']))
            : 'general';
        if (!in_array($current_tab, array('general', 'buttons', 'pages'), true)) {
            $current_tab = 'general';
        }

        if ($current_tab === 'general') {
            $sanitized['enabled'] = isset($input['enabled']) ? 1 : 0;

            if (isset($input['display_mode']) && in_array($input['display_mode'], array('2', '3', '4', '5', '6'), true)) {
                $sanitized['display_mode'] = $input['display_mode'];
            }

            if (isset($input['button_height'])) {
                $sanitized['button_height'] = absint($input['button_height']);
            }

            if (isset($input['max_screen_width'])) {
                $sanitized['max_screen_width'] = absint($input['max_screen_width']);
            }

            if (isset($input['button_width_right_2'])) {
                $sanitized['button_width_right_2'] = max(1, min(99, absint($input['button_width_right_2'])));
            }

            if (isset($input['button_width_left_3'])) {
                $sanitized['button_width_left_3'] = max(1, min(98, absint($input['button_width_left_3'])));
            }

            if (isset($input['button_width_right_3'])) {
                $sanitized['button_width_right_3'] = max(1, min(98, absint($input['button_width_right_3'])));
            }

            $sanitized['show_close_button'] = isset($input['show_close_button']) ? 1 : 0;

            if (isset($input['close_button_position']) && in_array($input['close_button_position'], array('left', 'right'), true)) {
                $sanitized['close_button_position'] = $input['close_button_position'];
            }

            if (isset($input['button_label_font_size'])) {
                $sanitized['button_label_font_size'] = absint($input['button_label_font_size']);
            }

            if (isset($input['bottom_text_font_size'])) {
                $sanitized['bottom_text_font_size'] = absint($input['bottom_text_font_size']);
            }
        } elseif ($current_tab === 'buttons') {
            $bottom_bg_color = isset($input['bottom_bg_color']) ? sanitize_hex_color($input['bottom_bg_color']) : null;
            if ($bottom_bg_color !== null) {
                $sanitized['bottom_bg_color'] = $bottom_bg_color;
            }

            $bottom_text_color = isset($input['bottom_text_color']) ? sanitize_hex_color($input['bottom_text_color']) : null;
            if ($bottom_text_color !== null) {
                $sanitized['bottom_text_color'] = $bottom_text_color;
            }

            if (isset($input['bottom_text'])) {
                $sanitized['bottom_text'] = sanitize_textarea_field($input['bottom_text']);
            }

            for ($i = 1; $i <= 6; $i++) {
                $sanitized["button_{$i}_enabled"] = isset($input["button_{$i}_enabled"]) ? 1 : 0;

                $bg_color = isset($input["button_{$i}_bg_color"]) ? sanitize_hex_color($input["button_{$i}_bg_color"]) : null;
                if ($bg_color !== null) {
                    $sanitized["button_{$i}_bg_color"] = $bg_color;
                }

                $text_color = isset($input["button_{$i}_text_color"]) ? sanitize_hex_color($input["button_{$i}_text_color"]) : null;
                if ($text_color !== null) {
                    $sanitized["button_{$i}_text_color"] = $text_color;
                }

                if (isset($input["button_{$i}_icon"])) {
                    $sanitized["button_{$i}_icon"] = sanitize_text_field($input["button_{$i}_icon"]);
                }

                if (isset($input["button_{$i}_label"])) {
                    $sanitized["button_{$i}_label"] = sanitize_text_field($input["button_{$i}_label"]);
                }

                if (isset($input["button_{$i}_url"])) {
                    $sanitized["button_{$i}_url"] = $this->andwff_sanitize_url($input["button_{$i}_url"]);
                }
            }
        } elseif ($current_tab === 'pages') {
            if (isset($input['exclusion_mode']) && in_array($input['exclusion_mode'], array('blacklist', 'whitelist'), true)) {
                $sanitized['exclusion_mode'] = $input['exclusion_mode'];
            }

            $sanitized['exclude_home'] = isset($input['exclude_home']) ? 1 : 0;
            $sanitized['exclude_pages'] = isset($input['exclude_pages']) ? 1 : 0;
            $sanitized['exclude_posts'] = isset($input['exclude_posts']) ? 1 : 0;
            $sanitized['exclude_categories'] = isset($input['exclude_categories']) ? 1 : 0;
            $sanitized['exclude_search'] = isset($input['exclude_search']) ? 1 : 0;

            if (isset($input['excluded_page_ids'])) {
                $page_ids = sanitize_text_field($input['excluded_page_ids']);
                if (!empty($page_ids)) {
                    $ids = explode(',', $page_ids);
                    $valid_ids = array();
                    foreach ($ids as $id) {
                        $id = trim($id);
                        if (is_numeric($id) && intval($id) > 0) {
                            $valid_ids[] = intval($id);
                        }
                    }
                    $sanitized['excluded_page_ids'] = implode(',', $valid_ids);
                } else {
                    $sanitized['excluded_page_ids'] = '';
                }
            }

            if (isset($input['excluded_url_patterns'])) {
                $sanitized['excluded_url_patterns'] = sanitize_textarea_field($input['excluded_url_patterns']);
            }
        }

        return $sanitized;
    }

    private function andwff_sanitize_url($url) {
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

    private function andwff_get_default_options() {
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
            'button_1_label' => __('Phone', 'andw-fixed-footer'),
            'button_1_url' => 'tel:000-000-0000',
            'button_2_enabled' => 1,
            'button_2_bg_color' => '#28a745',
            'button_2_text_color' => '#ffffff',
            'button_2_icon' => '\\f0e0',
            'button_2_label' => __('Email', 'andw-fixed-footer'),
            'button_2_url' => 'mailto:info@example.com',
            'button_3_enabled' => 0,
            'button_3_bg_color' => '#ffc107',
            'button_3_text_color' => '#212529',
            'button_3_icon' => '\\f041',
            'button_3_label' => __('Map', 'andw-fixed-footer'),
            'button_3_url' => 'https://example.com/map',
            'button_4_enabled' => 0,
            'button_4_bg_color' => '#dc3545',
            'button_4_text_color' => '#ffffff',
            'button_4_icon' => '\\f015',
            'button_4_label' => __('Home', 'andw-fixed-footer'),
            'button_4_url' => 'https://example.com',
            'button_5_enabled' => 0,
            'button_5_bg_color' => '#6f42c1',
            'button_5_text_color' => '#ffffff',
            'button_5_icon' => '\\f0d6',
            'button_5_label' => __('Booking', 'andw-fixed-footer'),
            'button_5_url' => 'https://example.com/booking',
            'button_6_enabled' => 0,
            'button_6_bg_color' => '#fd7e14',
            'button_6_text_color' => '#ffffff',
            'button_6_icon' => '\\f1ad',
            'button_6_label' => __('News', 'andw-fixed-footer'),
            'button_6_url' => 'https://example.com/news',

            // 除外設定のデフォルト値
            'exclusion_mode' => 'blacklist',
            'exclude_home' => 0,
            'exclude_pages' => 0,
            'exclude_posts' => 0,
            'exclude_categories' => 0,
            'exclude_search' => 0,
            'excluded_page_ids' => '',
            'excluded_url_patterns' => '',

            // スクロール動作設定のデフォルト値
            'scroll_reveal_threshold' => 150,

            // フォントサイズ設定のデフォルト値
            'button_label_font_size' => 14,
            'bottom_text_font_size' => 12,
        );
    }

    public function andwff_options_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'andw-fixed-footer'));
        }

        // 現在のタブを取得（デフォルト: general）
        $current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 管理画面タブ切替のみ
        $valid_tabs = array('general', 'buttons', 'pages');
        if (!in_array($current_tab, $valid_tabs)) {
            $current_tab = 'general';
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <!-- タブナビゲーション -->
            <nav class="nav-tab-wrapper">
                <a href="?page=andw-fixed-footer&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('General Settings', 'andw-fixed-footer'); ?>
                </a>
                <a href="?page=andw-fixed-footer&tab=buttons" class="nav-tab <?php echo $current_tab === 'buttons' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Button Settings', 'andw-fixed-footer'); ?>
                </a>
                <a href="?page=andw-fixed-footer&tab=pages" class="nav-tab <?php echo $current_tab === 'pages' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Display Page Settings', 'andw-fixed-footer'); ?>
                </a>
            </nav>

            <!-- タブコンテンツ -->
            <form action="options.php" method="post">
                <?php settings_fields('andwff_settings'); ?>
                <input type="hidden" name="andwff_current_tab" value="<?php echo esc_attr($current_tab); ?>">

                <div class="tab-content tab-content-<?php echo esc_attr($current_tab); ?>">
                    <?php
                    switch ($current_tab) {
                        case 'general':
                            do_settings_sections('andwff_general');
                            break;
                        case 'buttons':
                            do_settings_sections('andwff_buttons');
                            break;
                        case 'pages':
                            do_settings_sections('andwff_pages');
                            break;
                    }
                    ?>
                </div>

                <?php submit_button(__('Save Settings', 'andw-fixed-footer')); ?>
            </form>
        </div>
        <?php
    }

    public function andwff_enqueue_scripts() {
        // 管理画面では読み込まない
        if (is_admin()) {
            return;
        }

        $options = get_option($this->option_name, $this->andwff_get_default_options());

        if (!$options['enabled']) {
            return;
        }

        // 除外ページ判定
        if ($this->andwff_should_exclude_current_page($options)) {
            return;
        }


        wp_enqueue_style(
            'andwff-style',
            ANDWFF_PLUGIN_URL . 'assets/css/andw-fixed-footer.css',
            array(),
            ANDWFF_VERSION
        );

        // 設定値を取得してメディアクエリを動的生成（CSS変数を使わない）
        $max_width = !empty($options['max_screen_width']) ? absint($options['max_screen_width']) : 768;
        $button_label_font_size = !empty($options['button_label_font_size']) ? absint($options['button_label_font_size']) : 14;
        $bottom_text_font_size = !empty($options['bottom_text_font_size']) ? absint($options['bottom_text_font_size']) : 12;
        $custom_css = "
        /* andW Fixed Footer 動的設定 - 固定値でのメディアクエリ */

        /* 設定値以下でのみ表示 */
        @media (max-width: {$max_width}px) {
            #andw-fixed-footer-wrapper {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 9999 !important;
                display: flex !important;
                flex-direction: column !important;
                background: #ffffff !important;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1) !important;
                transition: transform 0.3s ease !important;
            }

            .andw-footer-buttons {
                display: flex !important;
                flex-direction: row !important;
                min-height: 50px !important;
            }

            .andw-footer-button {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
                padding: 8px 4px !important;
                border: none !important;
                cursor: pointer !important;
                transition: opacity 0.2s ease !important;
                font-size: {$button_label_font_size}px !important;
                line-height: 1.2 !important;
                text-align: center !important;
                gap: 4px !important;
                flex-direction: column !important;
            }

            .andw-footer-button:hover {
                opacity: 0.8 !important;
                text-decoration: none !important;
            }

            .andw-button-label {
                font-size: {$button_label_font_size}px !important;
            }

            /* スライド状態の制御 */
            #andw-fixed-footer-wrapper.andw-hide {
                transform: translateY(100%) !important;
            }

            #andw-fixed-footer-wrapper.andw-show {
                transform: translateY(0) !important;
            }

            #andw-fixed-footer-wrapper.andw-closed {
                display: none !important;
            }

            /* 下段住所エリア */
            .andw-footer-bottom {
                padding: 8px 12px !important;
                font-size: {$bottom_text_font_size}px !important;
                line-height: 1.4 !important;
                text-align: center !important;
                background-color: #333333 !important;
                color: #ffffff !important;
            }

            /* 閉じるボタンのスタイル */
            #andw-fixed-footer-wrapper .andw-close-button {
                position: absolute !important;
                top: -12px !important;
                width: 24px !important;
                height: 24px !important;
                min-width: 24px !important;
                max-width: 24px !important;
                background: rgba(0, 0, 0, 0.7) !important;
                color: #ffffff !important;
                border: none !important;
                border-radius: 50% !important;
                cursor: pointer !important;
                font-size: 16px !important;
                line-height: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                z-index: 10000 !important;
                transition: background-color 0.2s ease !important;
                box-sizing: border-box !important;
                flex-shrink: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                flex-grow: 0 !important;
                flex-basis: auto !important;
            }

            #andw-fixed-footer-wrapper .andw-close-button:hover {
                background: rgba(0, 0, 0, 0.5) !important;
            }

            #andw-fixed-footer-wrapper .andw-close-button:focus {
                outline: 2px solid #005a9c !important;
                outline-offset: 2px !important;
            }

            /* 閉じるボタンの位置 */
            #andw-fixed-footer-wrapper.andw-close-left .andw-close-button {
                left: 2px !important;
            }

            #andw-fixed-footer-wrapper.andw-close-right .andw-close-button {
                right: 8px !important;
            }
        }

        /* 設定値より大きい画面では完全に非表示 */
        @media (min-width: " . ($max_width + 1) . "px) {
            #andw-fixed-footer-wrapper {
                display: none !important;
            }
        }
        ";
        wp_add_inline_style('andwff-style', $custom_css);

        wp_enqueue_script(
            'andwff-script',
            ANDWFF_PLUGIN_URL . 'assets/js/andw-fixed-footer.js',
            array(),
            ANDWFF_VERSION,
            true
        );

        // JavaScriptに設定値を渡す
        wp_localize_script('andwff-script', 'andwffSettings', array(
            'maxWidth' => !empty($options['max_screen_width']) ? absint($options['max_screen_width']) : 768,
            'scrollRevealThreshold' => !empty($options['scroll_reveal_threshold']) ? absint($options['scroll_reveal_threshold']) : 150
        ));
    }

    public function andwff_admin_enqueue_scripts($hook) {
        // Only load on the plugin's settings page
        if ($hook !== 'settings_page_andw-fixed-footer') {
            return;
        }

        // Enqueue admin CSS
        wp_enqueue_style(
            'andwff-admin',
            ANDWFF_PLUGIN_URL . 'assets/css/andwff-admin.css',
            array(),
            ANDWFF_VERSION
        );
    }

    public function andwff_output() {
        $options = get_option($this->option_name, $this->andwff_get_default_options());

        if (!$options['enabled']) {
            return;
        }

        // 除外ページ判定
        if ($this->andwff_should_exclude_current_page($options)) {
            return;
        }

        $display_mode = $options['display_mode'];
        $button_height = absint($options['button_height']);

        echo '<div id="andw-fixed-footer-wrapper" class="andw-fixed-footer-wrapper andw-close-' . esc_attr($options['close_button_position']) . '">';

        if ($options['show_close_button']) {
            echo '<button class="andw-close-button" aria-label="' . esc_attr__('Close', 'andw-fixed-footer') . '">&times;</button>';
        }

        echo '<div class="andw-footer-buttons" style="height: ' . esc_attr($button_height) . 'px;">';

        $buttons = $this->andwff_get_active_buttons($options, $display_mode);
        $button_widths = $this->andwff_calculate_widths($options, $display_mode, count($buttons));

        foreach ($buttons as $index => $button) {
            $width = isset($button_widths[$index]) ? $button_widths[$index] : 0;
            if ($width > 0) {
                $this->andwff_render_button($button, $width);
            }
        }

        echo '</div>';

        if (!empty($options['bottom_text'])) {
            $bottom_style = '';
            if (!empty($options['bottom_bg_color'])) {
                $bottom_style .= 'background-color: ' . esc_attr($options['bottom_bg_color']) . ' !important;';
            }
            if (!empty($options['bottom_text_color'])) {
                $bottom_style .= 'color: ' . esc_attr($options['bottom_text_color']) . ' !important;';
            }

            echo '<div class="andw-footer-bottom" style="' . esc_attr($bottom_style) . '">';
            echo wp_kses_post(nl2br(esc_html($options['bottom_text'])));
            echo '</div>';
        }

        echo '</div>';
    }

    private function andwff_get_active_buttons($options, $display_mode) {
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

    private function andwff_calculate_widths($options, $display_mode, $active_count) {
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

    private function andwff_render_button($button, $width) {
        $button_style = '';
        $button_style .= 'flex-basis: ' . esc_attr($width) . '%;';
        if (!empty($button['bg_color'])) {
            $button_style .= 'background-color: ' . esc_attr($button['bg_color']) . ' !important;';
        }
        if (!empty($button['text_color'])) {
            $button_style .= 'color: ' . esc_attr($button['text_color']) . ' !important;';
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

ANDWFF_Fixed_Footer::get_instance();

/*
 * タブ化実装確認手順メモ
 *
 * WordPressの管理画面で以下を確認すること：
 *
 * 1. 「全体設定」タブ
 *    - プラグイン有効/無効チェックボックス
 *    - 表示モード（2分割〜6分割）ラジオボタン
 *    - 上段ボタン高さ入力フィールド
 *    - Font Awesomeについてのセクション
 *
 * 2. 「ボタン設定」タブ
 *    - ボタン1〜6の各設定フィールド（有効/無効、色、アイコン、ラベル、URL）
 *    - 下段帯設定（名前変更確認）
 *    - 下段背景色、文字色、テキストフィールド
 *
 * 3. 「表示ページ設定」タブ
 *    - 除外モード（指定ページで非表示/指定ページのみ表示）
 *    - 各ページタイプの除外チェックボックス
 *    - 除外ページID、除外URLパターンフィールド
 *
 * 4. 機能確認
 *    - 各タブ間の切り替えが正常に動作する
 *    - 設定保存後、入力値が保持される
 *    - フロントエンドでの表示に影響がない
 *
 * 5. 文言確認
 *    - 「下段住所帯設定」が「下段帯設定」に変更されている
 *    - 説明文から"住所"関連の文言が削除されている
 */
