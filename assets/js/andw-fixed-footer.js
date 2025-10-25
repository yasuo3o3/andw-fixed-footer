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

        // 画面幅チェック（480px以下でのみ動作）
        if (window.innerWidth > 480) {
            return;
        }

        // 初期表示状態の設定
        footerWrapper.classList.add('andw-loaded');
        footerWrapper.classList.add('andw-show');

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
            // 画面幅が480pxを超えた場合は非表示
            if (window.innerWidth > 480) {
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

        // ページトップ付近では常に表示
        if (currentScrollTop < 50) {
            showFooter();
        } else {
            // スクロール方向による表示制御
            if (currentScrollTop > lastScrollTop) {
                // 下方向スクロール → 非表示
                hideFooter();
            } else {
                // 上方向スクロール → 表示
                showFooter();
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
     * モジュールの初期化実行
     */
    init();

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