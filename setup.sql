-- クイズアプリケーション用データベース作成

-- データベース作成（SQLiteの場合は不要）
CREATE DATABASE IF NOT EXISTS quiz_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE quiz_db;

-- questionsテーブル作成
CREATE TABLE IF NOT EXISTS questions (
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(256) NOT NULL,
    answer VARCHAR(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- サンプルデータ挿入
INSERT INTO questions (question, answer) VALUES
('サザエさんの弟は?(カタカナ)', 'カツオ'),
('ドラえもんの好物は?(ひらがな)', 'どらやき'),
('「親人中?小」?に入る漢字は何?', '薬'),
('「雨+二+ム=?」?に入る漢字は何?', '雲');
