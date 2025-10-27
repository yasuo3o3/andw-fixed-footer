# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2025-10-28

### Changed
- Review and code quality improvements implemented
- WordPress.Security warnings resolved (nonce verification comments, wp_unslash processing)
- Code review-based quality improvements
- WordPress plugin policy full compliance
- Function prefix standardization (andw_fixed_footer_ prefix applied)
- WordPress.org submission ready level achieved

### Security
- WordPress.Security.NonceVerification.Recommended warning resolved for tab processing
- WordPress.Security.ValidatedSanitizedInput.MissingUnslash warning resolved

### Code Quality
- Function prefix naming convention compliance (andw_fixed_footer_ prefix standardized)
- WordPress Coding Standards full compliance
- All functions now properly prefixed to avoid naming conflicts

## [0.1.4] - 2025-10-27

### Fixed
- CSSファイルのフォントサイズ競合問題を修正
- ボタンラベルフォントサイズ設定機能の追加
- ボタン文字色が反映されない問題の修正
- タブ間設定保存問題の修正
- CSS変数メディアクエリ問題の修正

## [0.1.0] - 2025-10-26

### Added
- WordPress本番前レビュー実施・対応完了
- CSS表示問題の根本修正（メディアクエリ内CSS変数問題解決）
- セキュリティ強化（REQUEST_URI適切なサニタイズ）
- パフォーマンス改善（管理画面での不要リソース読み込み除外）
- 閉じるボタンUI改善（位置調整・透明度最適化）
- デバッグコード削除・本番環境クリーンアップ
- WordPress.org審査基準準拠

### Changed
- Font Awesome依存関係の変更
- ローカルFont Awesomeファイルを削除
- Font Awesome公式プラグイン使用に変更

### Fixed
- Font Awesome検出機能を追加
- 管理画面にFont Awesome検出状況表示
- 設定メッセージの重複表示問題を修正

## [0.0.3] - 2025-10-25

### Changed
- Font Awesome依存関係の変更
- ローカルFont Awesomeファイルを削除
- Font Awesome公式プラグイン使用に変更

### Added
- Font Awesome検出機能を追加
- 管理画面にFont Awesome検出状況表示

### Fixed
- 設定メッセージの重複表示問題を修正

## [0.0.2] - 2025-10-24

### Security
- WordPress.org Plugin Check対応
- セキュリティ強化
- コーディング規約準拠

## [0.0.1] - 2025-10-23

### Added
- 初回リリース
- 基本的な固定フッター機能
- 2〜6分割ボタンレイアウト
- スクロール連動表示/非表示
- Font Awesome対応（別途プラグイン必要）
- 閉じるボタン機能
- レスポンシブ対応強化