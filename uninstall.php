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
    // トランジェント削除: このプラグインではトランジェントを使用していないため削除不要

    // マルチサイト対応: このプラグインではトランジェントを使用していないため削除処理なし
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
 * WordPress.org審査のためerror_log削除、将来的にはフック提供を検討
 */
function andw_fixed_footer_log_uninstall() {
    // ログ出力なし（WordPress.org審査対応）
}

// アンインストール処理を実行
try {
    andw_fixed_footer_uninstall_cleanup();
    andw_fixed_footer_delete_cache();
    andw_fixed_footer_log_uninstall();
} catch (Exception $e) {
    // エラーハンドリング（WordPress.org審査のためerror_log削除）
}