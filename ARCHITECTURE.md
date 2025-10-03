# クイズアプリケーション - アーキテクチャドキュメント

## 📋 目次

1. [システム概要](#システム概要)
2. [アーキテクチャ設計](#アーキテクチャ設計)
3. [ディレクトリ構成](#ディレクトリ構成)
4. [データベース設計](#データベース設計)
5. [画面遷移図](#画面遷移図)
6. [セキュリティアーキテクチャ](#セキュリティアーキテクチャ)
7. [コマンド集](#コマンド集)

---

## システム概要

### 目的
クイズの作成・管理・出題を行うWebアプリケーション

### 技術スタック
- **言語**: PHP 7.4+
- **データベース**: SQLite（またはMySQL）
- **フロントエンド**: HTML5, CSS3
- **セッション管理**: PHP Session

---

## アーキテクチャ設計

### アーキテクチャパターン
**3層アーキテクチャ（簡易版）**

```
┌─────────────────────────────────┐
│   プレゼンテーション層          │
│   (HTML/CSS/Form)               │
│   - index.php                   │
│   - create.php                  │
│   - quiz.php                    │
│   - edit.php                    │
└─────────────────────────────────┘
              ↓
┌─────────────────────────────────┐
│   ビジネスロジック層            │
│   (PHP処理)                     │
│   - バリデーション              │
│   - データ処理                  │
│   - セッション管理              │
└─────────────────────────────────┘
              ↓
┌─────────────────────────────────┐
│   データアクセス層              │
│   (db.php + PDO)                │
│   - データベース接続            │
│   - CRUD操作                    │
└─────────────────────────────────┘
              ↓
┌─────────────────────────────────┐
│   データストア                  │
│   (SQLite/MySQL)                │
│   - questions テーブル          │
└─────────────────────────────────┘
```

### コンポーネント図

```
┌──────────────┐
│  index.php   │ トップページ
└──────────────┘
       ↓
   ┌───┴───┬────────┐
   ↓       ↓        ↓
┌─────┐ ┌─────┐ ┌─────┐
│create│ │quiz │ │edit │
└─────┘ └─────┘ └─────┘
   ↓       ↓        ↓
   └───┬───┴────────┘
       ↓
   ┌──────┐
   │db.php│ 共通DB接続
   └──────┘
       ↓
   ┌──────────┐
   │Database  │
   └──────────┘
```

---

## ディレクトリ構成

```
quiz_app/
├── index.php              # トップページ（メニュー画面）
├── create.php             # クイズ新規作成画面
├── quiz.php               # クイズ出題画面
├── edit.php               # クイズ編集画面（一覧・修正・削除）
├── db.php                 # データベース接続・共通関数
├── setup.sql              # MySQL用セットアップSQL
├── setup_sqlite.sql       # SQLite用セットアップSQL
├── quiz.db                # SQLiteデータベースファイル（自動生成）
├── README.md              # 使用方法
└── ARCHITECTURE.md        # このファイル
```

### ファイル責務

| ファイル | 責務 | 主要機能 |
|---------|------|----------|
| `index.php` | エントリーポイント | メニュー表示、ナビゲーション |
| `create.php` | クイズ作成 | 問題・答えの入力、DB保存 |
| `quiz.php` | クイズ出題 | ランダム出題、解答判定 |
| `edit.php` | クイズ管理 | 一覧表示、読込、修正、削除 |
| `db.php` | データアクセス | DB接続、h()関数 |

---

## データベース設計

### ER図

```
┌─────────────────────────┐
│      questions          │
├─────────────────────────┤
│ id (PK)    INTEGER      │
│ question   VARCHAR(256) │
│ answer     VARCHAR(256) │
└─────────────────────────┘
```

### テーブル定義

#### questionsテーブル

| カラム名 | データ型 | 制約 | 説明 |
|---------|---------|------|------|
| id | INTEGER | PRIMARY KEY, AUTO_INCREMENT | 問題ID（自動採番） |
| question | VARCHAR(256) | NOT NULL | 問題文 |
| answer | VARCHAR(256) | NOT NULL | 正解 |

### DDL

**SQLite版**
```sql
CREATE TABLE IF NOT EXISTS questions (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    question VARCHAR(256) NOT NULL,
    answer VARCHAR(256) NOT NULL
);
```

**MySQL版**
```sql
CREATE TABLE IF NOT EXISTS questions (
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(256) NOT NULL,
    answer VARCHAR(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 画面遷移図

```
                    ┌─────────────┐
                    │  index.php  │
                    │  (トップ)    │
                    └─────────────┘
                           │
          ┌────────────────┼────────────────┐
          ↓                ↓                ↓
    ┌──────────┐     ┌──────────┐     ┌──────────┐
    │create.php│     │ quiz.php │     │ edit.php │
    │(新規作成)│     │  (出題)  │     │  (編集)  │
    └──────────┘     └──────────┘     └──────────┘
          │                │                │
          └────────────────┴────────────────┘
                           │
                    相互リンク可能
```

### 画面フロー詳細

#### 1. 新規作成フロー
```
[create.php] → 入力 → 保存ボタン → バリデーション
                                        ↓
                              ┌─────────┴─────────┐
                              ↓                   ↓
                           成功                  失敗
                              ↓                   ↓
                        DB保存完了           エラー表示
                              ↓
                      「保存できました」表示
```

#### 2. 出題フロー
```
[quiz.php] → ランダム問題取得 → 問題表示
                                    ↓
                              答え入力 → 解答ボタン
                                    ↓
                            ┌───────┴───────┐
                            ↓               ↓
                          正解            不正解
                            ↓               ↓
                     「正解です」    「不正解(正解: XX)」
                            │               │
                            └───────┬───────┘
                                    ↓
                              次の問題ボタン
                                    ↓
                            新しい問題を表示
```

#### 3. 編集フロー
```
[edit.php] → 一覧表示
                ↓
        ┌───────┼───────┐
        ↓       ↓       ↓
      読込     修正     削除
        ↓       ↓       ↓
    フォーム  DB更新  DB削除
    に表示    成功    成功
```

---

## セキュリティアーキテクチャ

### 脅威モデル

| 脅威 | 対策 | 実装箇所 |
|------|------|----------|
| XSS攻撃 | HTMLエスケープ | 全出力時にh()関数使用 |
| SQLインジェクション | プリペアードステートメント | 全SQL実行時 |
| CSRF | （未実装）トークン検証推奨 | - |
| セッションハイジャック | （未実装）セッション再生成推奨 | - |

### XSS対策の実装

```php
// db.php
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 使用例
echo h($user_input);
```

**対策ポイント**:
- 全ての動的出力に`h()`関数を適用
- `ENT_QUOTES`でシングルクォートもエスケープ
- UTF-8エンコーディングを明示

### SQLインジェクション対策の実装

```php
// プリペアードステートメントの使用
$stmt = $pdo->prepare("INSERT INTO questions (question, answer) VALUES (:question, :answer)");
$stmt->bindParam(':question', $question, PDO::PARAM_STR);
$stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
$stmt->execute();
```

**対策ポイント**:
- SQL文にプレースホルダ（`:param`）を使用
- `bindParam()`で型指定してバインド
- 直接文字列結合でSQL構築しない

### データフロー図（セキュリティ観点）

```
ユーザー入力
    ↓
[trim()] ← 前後空白削除
    ↓
[バリデーション] ← 必須チェック
    ↓
[bindParam()] ← SQLインジェクション対策
    ↓
データベース
    ↓
[fetch()] ← データ取得
    ↓
[h()] ← XSS対策
    ↓
HTML出力
```

---

## コマンド集

### 開発・デプロイコマンド

#### 1. プロジェクトのセットアップ

```bash
# プロジェクトディレクトリに移動
cd /path/to/quiz_app

# SQLiteの場合（追加作業不要、自動的にquiz.dbが作成される）

# MySQLの場合
mysql -u root -p < setup.sql
```

#### 2. ローカル開発サーバーの起動

```bash
# PHP組み込みサーバーを起動
php -S localhost:8000

# または特定のIPで起動
php -S 0.0.0.0:8000

# バックグラウンドで起動
php -S localhost:8000 &
```

#### 3. データベース操作

**SQLiteの場合**

```bash
# SQLiteに接続
sqlite3 quiz.db

# テーブル一覧を表示
.tables

# テーブル構造を確認
.schema questions

# データを確認
SELECT * FROM questions;

# SQLiteを終了
.quit
```

**MySQLの場合**

```bash
# MySQLに接続
mysql -u root -p quiz_db

# テーブル一覧
SHOW TABLES;

# テーブル構造を確認
DESCRIBE questions;

# データを確認
SELECT * FROM questions;

# 終了
EXIT;
```

#### 4. データベースのバックアップ

**SQLiteの場合**

```bash
# バックアップ作成
cp quiz.db quiz_backup_$(date +%Y%m%d).db

# または
sqlite3 quiz.db ".backup quiz_backup.db"
```

**MySQLの場合**

```bash
# バックアップ作成
mysqldump -u root -p quiz_db > quiz_backup_$(date +%Y%m%d).sql

# バックアップから復元
mysql -u root -p quiz_db < quiz_backup.sql
```

#### 5. データベースのリセット

**SQLiteの場合**

```bash
# データベースファイルを削除（再起動で自動作成）
rm quiz.db

# または、テーブルのみクリア
sqlite3 quiz.db "DELETE FROM questions;"
```

**MySQLの場合**

```bash
# テーブルをドロップして再作成
mysql -u root -p quiz_db -e "DROP TABLE IF EXISTS questions;"
mysql -u root -p quiz_db < setup.sql
```

#### 6. サンプルデータの投入

```bash
# SQLiteの場合
sqlite3 quiz.db < setup_sqlite.sql

# MySQLの場合（データベースが既に存在する場合）
mysql -u root -p quiz_db -e "
INSERT INTO questions (question, answer) VALUES
('サザエさんの弟は?(カタカナ)', 'カツオ'),
('ドラえもんの好物は?(ひらがな)', 'どらやき'),
('「親人中?小」?に入る漢字は何?', '薬'),
('「雨+二+ム=?」?に入る漢字は何?', '雲');
"
```

### デバッグコマンド

#### 1. PHPのエラー表示を有効化

```bash
# php.iniの設定を確認
php --ini

# エラー表示を有効にして起動
php -d display_errors=1 -d error_reporting=E_ALL -S localhost:8000
```

#### 2. ログの確認

```bash
# PHPのエラーログを確認
tail -f /var/log/php_errors.log

# Apacheのエラーログ
tail -f /var/log/apache2/error.log
```

#### 3. データベースクエリのデバッグ

PHPファイルに以下を追加:

```php
// db.phpに追加してクエリログを有効化
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// クエリ実行前後でデバッグ
var_dump($stmt->queryString);
var_dump($stmt->debugDumpParams());
```

### テスト・検証コマンド

#### 1. セキュリティテスト

```bash
# XSSテスト用文字列
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>

# SQLインジェクションテスト用文字列
' OR '1'='1
0;DELETE FROM questions
```

#### 2. パーミッションの確認と設定

```bash
# ファイルの権限確認
ls -la

# SQLiteデータベースファイルに書き込み権限を付与
chmod 666 quiz.db
chmod 777 .  # ディレクトリにも書き込み権限が必要

# Webサーバーのユーザーを確認
ps aux | grep apache
ps aux | grep nginx
```

### デプロイコマンド

#### 1. 本番環境へのデプロイ

```bash
# ファイルを圧縮
tar -czf quiz_app.tar.gz quiz_app/

# サーバーにアップロード（SCPの例）
scp quiz_app.tar.gz user@server:/var/www/html/

# サーバーで解凍
ssh user@server
cd /var/www/html
tar -xzf quiz_app.tar.gz

# パーミッション設定
chmod -R 755 quiz_app
chmod 666 quiz_app/quiz.db
```

#### 2. Git管理

```bash
# リポジトリの初期化
git init

# .gitignoreの作成
echo "quiz.db" > .gitignore
echo "*.log" >> .gitignore

# 初回コミット
git add .
git commit -m "Initial commit: クイズアプリケーション"

# リモートリポジトリに接続
git remote add origin https://github.com/username/quiz_app.git
git push -u origin main
```

### メンテナンスコマンド

#### 1. データベースの最適化

**SQLiteの場合**

```bash
# データベースを最適化（VACUUM）
sqlite3 quiz.db "VACUUM;"

# 統計情報の更新
sqlite3 quiz.db "ANALYZE;"
```

**MySQLの場合**

```bash
# テーブルを最適化
mysql -u root -p quiz_db -e "OPTIMIZE TABLE questions;"

# テーブルを修復
mysql -u root -p quiz_db -e "REPAIR TABLE questions;"
```

#### 2. キャッシュのクリア

```bash
# PHPのOPcacheをクリア（php.ini設定が必要）
# Webサーバーを再起動
sudo systemctl restart apache2
# または
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

### ユーティリティコマンド

#### 1. データのエクスポート

```bash
# SQLiteからCSV出力
sqlite3 quiz.db <<EOF
.headers on
.mode csv
.output questions.csv
SELECT * FROM questions;
.quit
EOF

# MySQLからCSV出力
mysql -u root -p quiz_db -e "SELECT * FROM questions" | sed 's/\t/,/g' > questions.csv
```

#### 2. データのインポート

```bash
# CSVからSQLiteへインポート
sqlite3 quiz.db <<EOF
.mode csv
.import questions.csv questions
EOF

# CSVからMySQLへインポート
mysql -u root -p quiz_db -e "
LOAD DATA LOCAL INFILE 'questions.csv'
INTO TABLE questions
FIELDS TERMINATED BY ','
ENCLOSED BY '\"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;
"
```

---

## トラブルシューティング

### よくある問題と解決方法

| 問題 | 原因 | 解決方法 |
|------|------|----------|
| データベースに書き込めない | パーミッション不足 | `chmod 666 quiz.db` |
| 問題が表示されない | データが空 | サンプルデータを投入 |
| セッションエラー | セッション保存先の権限 | `/tmp`の権限確認 |
| 文字化け | 文字コード不一致 | `mb_internal_encoding('UTF-8')` |

---

## パフォーマンス最適化

### 推奨設定

```php
// db.php に追加
$pdo->setAttribute(PDO::ATTR_PERSISTENT, true); // 持続的接続

// インデックスの追加（大量データ時）
// MySQL
CREATE INDEX idx_question ON questions(question);

// SQLite
CREATE INDEX idx_question ON questions(question);
```

---

## 参考資料

- [PHP公式ドキュメント](https://www.php.net/manual/ja/)
- [PDO公式ドキュメント](https://www.php.net/manual/ja/book.pdo.php)
- [SQLite公式サイト](https://www.sqlite.org/)
- [MySQL公式ドキュメント](https://dev.mysql.com/doc/)
