=== andW Fixed Footer ===
Contributors: yasuo3o3
Tags: footer, mobile, fixed, responsive, sticky
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

スマホ向けの固定フッターバーを表示・管理するプラグイン。スクロール方向に応じてスライド表示されます。

== Description ==

andW Fixed Footerは、設定可能な画面幅以下で固定フッターバーを表示するプラグインです。デフォルトは768px以下で表示されます。

= 主な機能 =

* **レスポンシブ対応**: 自由に設定可能な画面幅以下で表示（デフォルト768px）
* **スクロール連動**: 下スクロールで表示、上スクロールで非表示
* **2段構成**: 上段ボタンエリア、下段住所テキストエリア
* **柔軟なレイアウト**: 2〜6分割ボタンレイアウト
* **カスタマイズ**: 色、アイコン、リンク先を自由に設定
* **Font Awesome対応**: アイコン表示をサポート（別途Font Awesomeプラグインが必要）
* **閉じるボタン**: ユーザーが一時的に非表示にできる機能

= 設定項目 =

**全体設定**
* プラグインの有効/無効
* 表示モード（2〜6分割）
* ボタン高さ
* 表示画面幅上限（320〜1200px、デフォルト768px）
* ボタン幅配分
* 閉じるボタンの表示・位置
* Font Awesomeの検出状況表示

**ボタン設定（最大6個）**
* 有効/無効
* 背景色・文字色
* Font Awesomeアイコンコード
* ラベルテキスト
* リンク先URL（tel:、mailto:、https:// 対応）

**下段住所帯**
* 背景色・文字色
* 複数行テキスト（改行自動変換）

= 使用場面 =

* コーポレートサイトの電話・メール連絡
* ECサイトの問い合わせボタン
* 店舗サイトの地図・予約リンク
* サービスサイトの資料請求

= セキュリティ =

* 入力値の適切なサニタイズ
* 出力時のエスケープ処理
* nonce認証による設定保護
* 管理者権限チェック

== Installation ==

1. Font Awesomeプラグインを先にインストールします（推奨）:
   https://ja.wordpress.org/plugins/font-awesome/
2. andW Fixed Footerプラグインをアップロードし、有効化してください
3. 管理画面の「設定 > 固定フッター」から設定を行います
4. Font Awesomeの検出状況を確認します
5. 各ボタンの色、アイコン、リンク先を設定します
6. モバイル端末で表示を確認してください

== Frequently Asked Questions ==

= どの画面サイズで表示されますか？ =

管理画面で設定した「表示画面幅上限」以下のデバイスで表示されます。デフォルトは768px以下で、320px〜1200pxの範囲で自由に設定できます。

= Font Awesomeが必要ですか？ =

はい。このプラグインはアイコン表示にFont Awesomeを使用します。以下の方法で導入してください：
1. Font Awesome公式プラグイン（推奨）: https://ja.wordpress.org/plugins/font-awesome/
2. 他のテーマやプラグインで既に読み込まれている場合は不要です

管理画面でFont Awesomeの検出状況を確認できます。

= アイコンコードの形式は？ =

Font Awesomeのunicodeを使用します。例：`\f095`（電話アイコン）

= スクロール動作を無効にできますか？ =

現在のバージョンでは固定機能はありません。閉じるボタンで一時的に非表示にできます。

== Screenshots ==

1. 管理画面の設定ページ
2. スマホでの表示例（2分割）
3. スマホでの表示例（3分割）
4. スマホでの表示例（6分割）

== Changelog ==

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
* `.andw-fixed-footer-wrapper` - メインコンテナ
* `.andw-footer-buttons` - ボタンエリア
* `.andw-footer-button` - 個別ボタン
* `.andw-footer-bottom` - 下段住所エリア

= JavaScript API =
```javascript
// 表示
window.andwFixedFooter.show();

// 非表示
window.andwFixedFooter.hide();

// 閉じる
window.andwFixedFooter.close();

// 表示状態確認
window.andwFixedFooter.isVisible();
```