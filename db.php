<?php
// データベース接続設定

// 使用するデータベースの種類を選択 ('mysql' または 'sqlite')
define('DB_TYPE', 'sqlite');

// データベース接続情報
if (DB_TYPE === 'mysql') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'quiz_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // SQLiteの場合
    define('DB_FILE', __DIR__ . '/quiz.db');
}

/**
 * データベース接続を取得
 * @return PDO
 */
function getDB() {
    try {
        if (DB_TYPE === 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            // SQLite接続
            $dsn = "sqlite:" . DB_FILE;
            $pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // SQLiteデータベースが存在しない場合、テーブルを作成
            $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                question VARCHAR(256) NOT NULL,
                answer VARCHAR(256) NOT NULL
            )");
        }
        return $pdo;
    } catch (PDOException $e) {
        die('データベース接続エラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * HTMLエスケープ関数
 * @param string $str
 * @return string
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
