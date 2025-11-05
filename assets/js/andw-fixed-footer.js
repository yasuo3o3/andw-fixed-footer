/**
 * andW Fixed Footer JavaScript
 * スクロール方向に応じてフッターの表示/非表示を制御
 */

(function() {
    'use strict';

    // グローバル変数
    let footerWrapper = null;
    let closeButton = null;
    let lastScrollTop = 0;
    let scrollThreshold = 5; // スクロール感度
    let isScrolling = false;
    let scrollTimer = null;
    let hasBeenRevealed = false; // 初回表示フラグ
    let scrollRevealThreshold = 150; // 初回表示開始位置（デフォルト値）

    /**
     * 初期化処理
     */
    function init() {
        // DOM読み込み完了後に実行
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFooter);
        } else {
            setupFooter();
        }
    }

    /**
     * フッターのセットアップ
     */
    function setupFooter() {
        footerWrapper = document.getElementById('andw-fixed-footer-wrapper');

        if (!footerWrapper) {
            return;
        }

        // 画面幅チェック（設定値以下でのみ動作）
        var maxWidth = (typeof andwffSettings !== 'undefined' && andwffSettings.maxWidth) ? andwffSettings.maxWidth : 768;
        if (window.innerWidth > maxWidth) {
            return;
        }

        // スクロール設定値を読み込み
        if (typeof andwffSettings !== 'undefined' && andwffSettings.scrollRevealThreshold) {
            scrollRevealThreshold = parseInt(andwffSettings.scrollRevealThreshold, 10);
        }

        // CSS変数の動作確認とフォールバック適用
        checkCSSVariablesAndApplyFallback();

        // 初期表示状態の設定（非表示で開始）
        footerWrapper.classList.add('andw-loaded');
        footerWrapper.classList.add('andw-hide');

        // アイコンの設定
        setupIcons();

        // 閉じるボタンの設定
        setupCloseButton();

        // スクロールイベントの設定
        setupScrollEvent();

        // リサイズイベントの設定
        setupResizeEvent();
    }

    /**
     * CSS変数の動作確認とフォールバック適用
     */
    function checkCSSVariablesAndApplyFallback() {
        // CSS変数の対応チェック
        const supportsCSS = window.CSS && CSS.supports && CSS.supports('(--fake-var: 0)');

        if (!supportsCSS) {
            console.warn('andW Fixed Footer: CSS Variables not supported, applying emergency fallback');
            footerWrapper.classList.add('andw-emergency-fallback');
            return;
        }

        // CSS変数が実際に適用されているかチェック
        const computedStyle = window.getComputedStyle(footerWrapper);
        const position = computedStyle.getPropertyValue('position');

        // 100ms後に位置をチェック（CSS読み込み完了待ち）
        setTimeout(function() {
            const recomputedStyle = window.getComputedStyle(footerWrapper);
            const newPosition = recomputedStyle.getPropertyValue('position');

            if (newPosition !== 'fixed') {
                console.warn('andW Fixed Footer: CSS not applying correctly, activating emergency fallback');
                footerWrapper.classList.add('andw-emergency-fallback');
            }
        }, 100);
    }

    /**
     * Font Awesomeアイコンの設定
     */
    function setupIcons() {
        const iconElements = footerWrapper.querySelectorAll('.andw-button-icon');

        iconElements.forEach(function(element) {
            const iconCode = element.getAttribute('data-icon');
            if (iconCode && iconCode.trim()) {
                // \f095 形式のアイコンコードをCSS変数に設定
                // バックスラッシュを適切にエスケープしてCSS contentで使用可能な形式に変換
                const cssContent = '"' + iconCode + '"';
                element.style.setProperty('--icon-content', cssContent);
            }
        });
    }

    /**
     * 閉じるボタンの設定
     */
    function setupCloseButton() {
        closeButton = footerWrapper.querySelector('.andw-close-button');

        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeFooter();
            });

            // キーボードアクセシビリティ
            closeButton.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    closeFooter();
                }
            });
        }
    }

    /**
     * スクロールイベントの設定
     */
    function setupScrollEvent() {
        // スクロール位置の初期化
        lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // パフォーマンス向上のためのスロットリング機能付きスクロールイベント
        window.addEventListener('scroll', throttleScrollHandler, { passive: true });

        // タッチデバイス向けの追加イベント
        if ('ontouchstart' in window) {
            window.addEventListener('touchmove', throttleScrollHandler, { passive: true });
        }
    }

    /**
     * リサイズイベントの設定
     */
    function setupResizeEvent() {
        window.addEventListener('resize', debounce(function() {
            // 画面幅が設定値を超えた場合は非表示
            var maxWidth = (typeof andwffSettings !== 'undefined' && andwffSettings.maxWidth) ? andwffSettings.maxWidth : 768;
            if (window.innerWidth > maxWidth) {
                if (footerWrapper) {
                    footerWrapper.style.display = 'none';
                }
            } else {
                if (footerWrapper) {
                    footerWrapper.style.display = '';
                }
            }
        }, 250));
    }

    /**
     * スクロールハンドラ（スロットリング付き）
     */
    function throttleScrollHandler() {
        if (!isScrolling) {
            window.requestAnimationFrame(handleScroll);
            isScrolling = true;
        }
    }

    /**
     * スクロール処理のメインロジック
     */
    function handleScroll() {
        if (!footerWrapper || footerWrapper.classList.contains('andw-closed')) {
            isScrolling = false;
            return;
        }

        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollDifference = Math.abs(currentScrollTop - lastScrollTop);

        // スクロール量が閾値を超えた場合のみ処理
        if (scrollDifference < scrollThreshold) {
            isScrolling = false;
            return;
        }

        // 初回表示判定（設定された閾値でフッターを初回表示）
        if (!hasBeenRevealed && currentScrollTop >= scrollRevealThreshold) {
            hasBeenRevealed = true;
            showFooter();
        } else if (hasBeenRevealed) {
            // 初回表示後は上下スクロールで制御
            if (currentScrollTop > lastScrollTop) {
                // 下方向スクロール → 表示（アクション促進）
                showFooter();
            } else {
                // 上方向スクロール → 非表示（コンテンツ閲覧優先）
                hideFooter();
            }
        }

        lastScrollTop = currentScrollTop;
        isScrolling = false;
    }

    /**
     * フッターを表示
     */
    function showFooter() {
        if (footerWrapper && !footerWrapper.classList.contains('andw-closed')) {
            footerWrapper.classList.remove('andw-hide');
            footerWrapper.classList.add('andw-show');
        }
    }

    /**
     * フッターを非表示
     */
    function hideFooter() {
        if (footerWrapper && !footerWrapper.classList.contains('andw-closed')) {
            footerWrapper.classList.remove('andw-show');
            footerWrapper.classList.add('andw-hide');
        }
    }

    /**
     * フッターを閉じる（セッション中は非表示）
     */
    function closeFooter() {
        if (footerWrapper) {
            footerWrapper.classList.add('andw-closed');

            // アニメーション後に完全に非表示
            setTimeout(function() {
                if (footerWrapper) {
                    footerWrapper.style.display = 'none';
                }
            }, 300);
        }
    }

    /**
     * デバウンス関数
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * ページ離脱時のクリーンアップ
     */
    function cleanup() {
        if (scrollTimer) {
            clearTimeout(scrollTimer);
        }

        window.removeEventListener('scroll', throttleScrollHandler);
        window.removeEventListener('touchmove', throttleScrollHandler);

        if (closeButton) {
            closeButton.removeEventListener('click', closeFooter);
            closeButton.removeEventListener('keydown', closeFooter);
        }
    }

    /**
     * ページ離脱時のイベント設定
     */
    window.addEventListener('beforeunload', cleanup);

    /**
     * エラーハンドリング
     */
    window.addEventListener('error', function(e) {
        if (e.filename && e.filename.includes('andw-fixed-footer.js')) {
            console.warn('andW Fixed Footer: JavaScript error occurred', e.message);
        }
    });

    /**
     * デバッグ情報の出力（本番環境では無効化）
     */
    function logDebugInfo() {
        // Debug output disabled for production
    }

    /**
     * モジュールの初期化実行
     */
    init();

    // デバッグ情報を3秒後に出力（読み込み完了後）
    setTimeout(logDebugInfo, 3000);

    /**
     * 外部から制御するためのAPI（必要に応じて）
     */
    window.andwFixedFooter = {
        show: showFooter,
        hide: hideFooter,
        close: closeFooter,
        isVisible: function() {
            return footerWrapper && footerWrapper.classList.contains('andw-show') && !footerWrapper.classList.contains('andw-closed');
        }
    };

})();