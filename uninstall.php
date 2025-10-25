<?php
/**
 * andW Fixed Footer Uninstaller
 *
 * プラグイン削除時に実行されるクリーンアップ処理
 * プラグイン停止では実行されず、削除時のみ発火
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * プラグインが作成したデータを削除
 */
function andw_fixed_footer_uninstall_cleanup() {
    // 管理者権限チェック
    if (!current_user_can('delete_plugins')) {
        return;
    }

    // プラグイン設定オプションを削除
    delete_option('andw_fixed_footer_options');

    // マルチサイト対応：各サイトの設定も削除
    if (is_multisite()) {
        $blog_ids = get_sites(array('number' => 0, 'fields' => 'ids'));

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            delete_option('andw_fixed_footer_options');
            restore_current_blog();
        }
    }

    // 当プラグイン専用のtransientsを削除
    andw_fixed_footer_delete_transients();
}

/**
 * 当プラグイン関連のtransientsを削除
 */
function andw_fixed_footer_delete_transients() {
    global $wpdb;

    // andw_fixed_footer プレフィックスのtransientsを検索・削除
    $transient_keys = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_andw_fixed_footer_%',
            '_transient_timeout_andw_fixed_footer_%'
        )
    );

    foreach ($transient_keys as $key) {
        if (strpos($key, '_transient_timeout_') === 0) {
            $transient_name = str_replace('_transient_timeout_', '', $key);
        } else {
            $transient_name = str_replace('_transient_', '', $key);
        }

        delete_transient($transient_name);
    }

    // マルチサイト対応
    if (is_multisite()) {
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);

            $site_transient_keys = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
                    '_transient_andw_fixed_footer_%',
                    '_transient_timeout_andw_fixed_footer_%'
                )
            );

            foreach ($site_transient_keys as $key) {
                if (strpos($key, '_transient_timeout_') === 0) {
                    $transient_name = str_replace('_transient_timeout_', '', $key);
                } else {
                    $transient_name = str_replace('_transient_', '', $key);
                }

                delete_transient($transient_name);
            }

            restore_current_blog();
        }
    }
}

/**
 * オブジェクトキャッシュから当プラグイン関連キーを削除
 */
function andw_fixed_footer_delete_cache() {
    // 外部オブジェクトキャッシュ（Redis、Memcached等）を使用している場合の対応
    if (function_exists('wp_cache_supports') && wp_cache_supports('flush_group')) {
        wp_cache_flush_group('andw_fixed_footer');
    }

    // 個別キーの削除（具体的なキーがある場合）
    $cache_keys = array(
        'andw_fixed_footer_settings',
        'andw_fixed_footer_buttons',
        'andw_fixed_footer_display_mode'
    );

    foreach ($cache_keys as $key) {
        wp_cache_delete($key, 'andw_fixed_footer');
    }
}

/**
 * ログの書き込み（デバッグ用）
 */
function andw_fixed_footer_log_uninstall() {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('andW Fixed Footer: プラグインが正常にアンインストールされました');
    }
}

// アンインストール処理を実行
try {
    andw_fixed_footer_uninstall_cleanup();
    andw_fixed_footer_delete_cache();
    andw_fixed_footer_log_uninstall();
} catch (Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('andW Fixed Footer アンインストールエラー: ' . $e->getMessage());
    }
}