# andW Fixed Footer

A responsive fixed footer plugin for WordPress with mobile-first design and scroll-based behavior.

[![WordPress Plugin Version](https://img.shields.io/badge/version-0.2.1-blue.svg)](https://github.com/yasuo3o3/andw-fixed-footer)
[![WordPress Compatibility](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Compatibility](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPLv2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Features

- **Mobile-First Responsive**: Displays only below configurable screen width (default 768px)
- **Scroll-Based Animation**: Shows on scroll down, hides on scroll up
- **Flexible Button Layout**: 2-6 configurable action buttons with custom styling
- **Font Awesome Integration**: Icon support via external Font Awesome plugin
- **Two-Tier Structure**: Button area and optional address text area
- **Close Button**: Temporary hide functionality for users
- **Full Customization**: Colors, fonts, sizes, and behavior fully configurable
- **WordPress.org Ready**: Fully compliant with WordPress.org plugin guidelines

## Screenshots

- Configuration page with comprehensive settings
- Mobile display examples (2-6 button layouts)
- Scroll behavior demonstration
- Font Awesome icon integration

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Font Awesome**: External Font Awesome plugin recommended
- **Mobile Device**: Optimized for mobile/tablet viewing

## Installation

1. **Install Font Awesome** (recommended):
   - Install the [Font Awesome Official Plugin](https://wordpress.org/plugins/font-awesome/)
   - Or ensure Font Awesome is loaded by your theme

2. **Install andW Fixed Footer**:
   - Upload the plugin files to `/wp-content/plugins/andw-fixed-footer/`
   - Activate the plugin through the WordPress admin

3. **Configure Settings**:
   - Go to "Settings > Fixed Footer" in WordPress admin
   - Configure display options, button layouts, and styling
   - Test on mobile devices

## Configuration

### General Settings
- Plugin enable/disable toggle
- Display mode (2-6 button layout)
- Screen width threshold (320-1200px)
- Button height and width distribution
- Close button position and visibility

### Button Configuration (up to 6 buttons)
- Individual enable/disable
- Background and text colors
- Font Awesome icon codes (unicode format)
- Label text and link URLs
- Support for `tel:`, `mailto:`, and `https://` links

### Address Bar (Optional)
- Background and text colors
- Multi-line text with automatic line break conversion

## Technical Specifications

### Security Features
- Input sanitization with `sanitize_text_field()`, `wp_unslash()`
- Output escaping with `esc_html()`, `esc_attr()`, `esc_url()`
- Nonce verification for all form submissions
- Administrator capability checks (`manage_options`)

### WordPress API Compliance
- WordPress Settings API for configuration storage
- Proper CSS/JS enqueuing with `wp_enqueue_style()`, `wp_enqueue_script()`
- Inline styles via `wp_add_inline_style()` for dynamic values
- Text domain `andw-fixed-footer` for internationalization

### Performance Optimization
- CSS/JS loaded only on frontend when plugin is enabled
- Page exclusion rules to prevent unnecessary loading
- Responsive-based loading (no resources on large screens)
- Minimal inline CSS for dynamic configuration

## WordPress.org Submission

This plugin is being prepared for submission to the **WordPress.org Plugin Directory**.

### Compliance Status
- ✅ WordPress Coding Standards (WPCS) compliant
- ✅ Plugin Check tool validation passed
- ✅ Security best practices implemented
- ✅ No external dependencies or premium features
- ✅ Full internationalization support

### Submission Timeline
Currently in **final testing phase** before WordPress.org submission.

## Development

### Code Quality Tools
- **PHPCS**: WordPress Coding Standards validation
- **Plugin Check**: WordPress.org submission validation
- **PHP Lint**: Syntax error checking

### Browser Support
- Modern mobile browsers (iOS Safari, Android Chrome)
- Progressive enhancement for older browsers
- CSS variables with fallback values

---

## 概要（日本語）

スマートフォン向けの固定フッタープラグインです。スクロール方向に応じて表示・非表示が切り替わります。

### 主な特徴

- **モバイルファースト**: 設定可能な画面幅以下でのみ表示（デフォルト768px）
- **スクロール連動**: 下スクロールで表示、上スクロールで非表示
- **柔軟なレイアウト**: 2-6個のボタンレイアウトをカスタマイズ可能
- **Font Awesome対応**: 外部Font Awesomeプラグインでアイコン表示
- **完全カスタマイズ**: 色、フォント、サイズ、動作を細かく設定可能
- **WordPress.org準拠**: WordPress公式プラグインディレクトリのガイドライン完全準拠

### 使用場面

- コーポレートサイトの電話・メール連絡ボタン
- ECサイトの問い合わせ・カート案内
- 店舗サイトの地図・予約リンク
- サービスサイトの資料請求・お問い合わせ

### WordPress.org 提出予定

このプラグインは **WordPress.org プラグインディレクトリ** への提出を予定しています。
現在、最終テスト段階にあり、まもなく公開予定です。

## License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.