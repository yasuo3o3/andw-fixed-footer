=== andW Fixed Footer ===
Contributors: yasuo3o3
Tags: footer, mobile, fixed, responsive, sticky
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A responsive fixed footer plugin for WordPress with mobile-first design and scroll-based behavior.

== Description ==

andW Fixed Footer is a responsive mobile-first fixed footer plugin for WordPress. It displays a customizable footer bar on mobile devices with scroll-based show/hide behavior.

= Key Features =

* **Responsive Design**: Configurable screen width threshold (default: 768px)
* **Scroll Interaction**: Shows on scroll down, hides on scroll up
* **Flexible Layout**: 2-6 button layout options
* **Customizable Styling**: Colors, icons, links, and text fully configurable
* **Font Awesome Support**: Icon display with external Font Awesome plugin
* **Close Button**: Temporary hide functionality for users
* **Two-tier Structure**: Button area and bottom text area

= Configuration Options =

**General Settings**
* Plugin enable/disable
* Display mode (2-6 button layout)
* Button height
* Screen width threshold (320-1200px, default 768px)
* Button width distribution
* Close button display and position
* Font Awesome detection status

**Button Settings (up to 6 buttons)**
* Enable/disable individual buttons
* Background and text colors
* Font Awesome icon codes
* Label text
* Link URLs (tel:, mailto:, https:// supported)

**Bottom Bar**
* Background and text colors
* Multi-line text (automatic line break conversion)

= Use Cases =

* Corporate website phone/email contact
* E-commerce inquiry buttons
* Store location/reservation links
* Service website resource requests

= Security Features =

* Proper input sanitization
* Output escaping
* Nonce authentication for settings protection
* Administrator permission checks


== Installation ==

1. Install Font Awesome plugin first (recommended):
   https://wordpress.org/plugins/font-awesome/
2. Upload and activate andW Fixed Footer plugin
3. Go to Settings > Fixed Footer in the admin panel
4. Check Font Awesome detection status
5. Configure colors, icons, and links for each button
6. Verify display on mobile devices

== Frequently Asked Questions ==

= On which screen sizes is the footer displayed? =

The footer is displayed on devices with screen width at or below the "Display Screen Width" setting configured in the admin panel. Default is 768px or below, and can be freely set in the range of 320px to 1200px.

= Is Font Awesome required? =

Yes. This plugin uses Font Awesome for icon display. Please install it using one of the following methods:
1. Font Awesome Official Plugin (recommended): https://wordpress.org/plugins/font-awesome/
2. Not required if Font Awesome is already loaded by other themes or plugins

You can check Font Awesome detection status in the admin panel.

= What format should icon codes be in? =

Use Font Awesome unicode format. Example: `\f095` (phone icon)

= Can scroll behavior be disabled? =

The current version does not have a fixed display feature. Users can temporarily hide it using the close button.

== Screenshots ==

1. Admin settings page
2. Mobile display example (2 columns)
3. Mobile display example (3 columns)
4. Mobile display example (6 columns)

== Changelog ==

= 0.2.0 =
* Review and code quality improvements
* WordPress.Security warnings resolved (nonce verification comments, wp_unslash processing)
* WordPress plugin policy full compliance
* Function prefix standardization (andw_fixed_footer_ prefix applied)
* WordPress.org submission ready level achieved
* Code quality improvements based on comprehensive review

= 0.1.4 =
* CSS font size conflict issue fixed
* Button label font size setting feature added
* Button text color reflection issue fixed
* Tab setting save issue fixed
* CSS variable media query issue fixed

= 0.1.0 =
* WordPress本番前レビュー実施・対応完了
* CSS表示問題の根本修正（メディアクエリ内CSS変数問題解決）
* セキュリティ強化（REQUEST_URI適切なサニタイズ）
* パフォーマンス改善（管理画面での不要リソース読み込み除外）
* 閉じるボタンUI改善（位置調整・透明度最適化）
* デバッグコード削除・本番環境クリーンアップ
* WordPress.org審査基準準拠

= 0.0.3 =
* Font Awesome依存関係の変更
* ローカルFont Awesomeファイルを削除
* Font Awesome公式プラグイン使用に変更
* Font Awesome検出機能を追加
* 管理画面にFont Awesome検出状況表示
* 設定メッセージの重複表示問題を修正

= 0.0.2 =
* WordPress.org Plugin Check対応
* セキュリティ強化
* コーディング規約準拠

= 0.0.1 =
* 初回リリース
* 基本的な固定フッター機能
* 2〜6分割ボタンレイアウト
* スクロール連動表示/非表示
* Font Awesome対応（別途プラグイン必要）
* 閉じるボタン機能
* レスポンシブ対応強化

== Upgrade Notice ==

= 0.1.0 =
WordPress本番運用に向けた重要な修正版です。CSS表示問題の根本解決、セキュリティ強化、パフォーマンス改善が含まれます。

= 0.0.3 =
Font Awesome依存関係が変更されました。Font Awesome公式プラグインのインストールが推奨されます。

= 0.0.2 =
WordPress.org Plugin Check対応版です。

= 0.0.1 =
初回リリースです。

== Technical Notes ==

= 対応URL形式 =
* tel:086-000-0000
* mailto:info@example.com
* https://example.com
* http://example.com

= CSS Class Names =
* `.andw-fixed-footer-wrapper` - Main container
* `.andw-footer-buttons` - Button area
* `.andw-footer-button` - Individual button
* `.andw-footer-bottom` - Bottom text area

= JavaScript API =
```javascript
// Show
window.andwFixedFooter.show();

// Hide
window.andwFixedFooter.hide();

// Close
window.andwFixedFooter.close();

// Check visibility
window.andwFixedFooter.isVisible();
```