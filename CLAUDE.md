# CLAUDE.md

このファイルはリポジトリ内のコードを扱う際に Claude Code (claude.ai/code) へ提供するガイダンスです。

## コマンド

```bash
# 全開発サービス起動（Laravelサーバー + キュー + ログ + Vite）
composer dev

# テスト実行
composer test

# 単一テストファイルの実行
php artisan test --filter=TestClassName

# マイグレーション実行
php artisan migrate

# フロントエンドビルド
npm run build

# PHPフォーマット（Laravel Pint）
vendor/bin/pint
```

アプリはMAMP経由でlocalhostで動作する。初回セットアップ後に `php artisan storage:link` を一度実行し、アバター画像を `/storage/avatars/` で配信できるようにする。

## アーキテクチャ

**Laravel 12 + Blade + Tailwind CSS + Vite** — Inertia・SPAフレームワークは使用していない。フロントエンドは `public/js/` に置かれた静的JavaScriptファイルで構成されており、Viteのエントリーポイント（`resources/js/app.js`、Axiosのbootstrapのみ）とは独立している。

### リクエストフロー

```
Browser → routes/web.php （HTMLページ、認証）
        → routes/api.php  （JSON、タスクCRUD + AI）
```

`routes/api.php` の全APIルートは `['web', 'auth']` ミドルウェアを使用しており、セッションベースのCSRFクッキーを共有する（トークン認証ではない）。

### 主要ファイル

| ファイル | 役割 |
|---------|------|
| `app/Http/Controllers/TaskController.php` | タスクCRUD、AI解析（GPT-5）、Whisper文字起こし |
| `app/Http/Controllers/AuthController.php` | ログイン、新規登録、パスワードリセット、アバターアップロード |
| `app/Models/Task.php` | SoftDeletes使用；テーブル名 `ai_tasks_T_tasks` |
| `app/Models/User.php` | テーブル名 `ai_tasks_M_users`；アバターは `storage/app/public/avatars/` に保存 |
| `app/Models/Priority.php` | テーブル名 `ai_tasks_M_priorities`；初期データ: 高/中/低/指定なし |
| `public/js/tasks.js` | タスク一覧UI、モーダル、フィルター状態（プレーンJSグローバル状態） |
| `public/js/voice-input.js` | Web Speech API による音声入力 |
| `public/js/auth.js` | Cropper.js を使ったアバター切り抜き・アップロード |
| `resources/views/tasks/index.blade.php` | メインのタスク画面 |
| `resources/views/layouts/app.blade.php` | 共通レイアウト |

### DBテーブル命名規則

テーブル名はプレフィックス付きの命名規則を採用: `ai_tasks_T_*`（トランザクション系）・`ai_tasks_M_*`（マスター系）。各モデルで `$table` プロパティにハードコードされている。

### AI連携

- `POST /api/tasks/analyze` — ユーザー入力テキストをOpenAI（gpt-5）へ送信し構造化プロンプトで解析。`aiTask`・`date`・`assignee`・`time`・`priority` フィールドのJSONを返す。不明・未指定の項目は `"指定なし"` を返す（nullではない）。
- `POST /api/tasks/transcribe` — 音声ファイルをOpenAI Whisper API（`whisper-1`）へ送信し文字起こしテキストを返す。
- `.env` に `OPENAI_API_KEY` の設定が必要。

### タスクフィルタリング

`TaskController::getFilteredTasks()` が5つのフィルターモードを処理: `self`・`member`・`unassigned`・`completed`・`all`。その上にサブフィルター（`assignee_id`・`priority_id`・`due`）が重なる形で適用される。`unassigned` は担当者なしのタスクを対象とし、`member` は現在のログインユーザーのタスクを除外する。

### アバターアップロードフロー

2つのエンドポイントが存在する:
- `POST /account/avatar` — Cropper.jsでクライアント側でトリミング後にblobをPOST。保存前に古い画像を削除する。
- `DELETE /account/avatar` — ストレージからファイルを削除しDBカラムをnullにする。

ファイルは `storage/app/public/avatars/{userId}_{timestamp}.{ext}` に保存され、`public` シンボリックリンク経由で配信される。

### フロントエンドJavaScript

`public/js/` 内のファイルは **Viteで処理されない** — Bladeビューから `<script src="/js/tasks.js">` で直接読み込まれる静的ファイル。`tasks.js` のグローバル状態はモジュールレベルの `let` 変数で管理される。サーバーデータはBladeテンプレートで設定した `window.MEMBERS`・`window.CURRENT_USER` 経由で渡される。
