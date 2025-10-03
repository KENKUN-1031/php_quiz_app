-- SQLite用セットアップファイル

-- questionsテーブル作成
CREATE TABLE IF NOT EXISTS questions (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    question VARCHAR(256) NOT NULL,
    answer VARCHAR(256) NOT NULL
);

-- サンプルデータ挿入
INSERT INTO questions (question, answer) VALUES
('サザエさんの弟は?(カタカナ)', 'カツオ'),
('ドラえもんの好物は?(ひらがな)', 'どらやき'),
('「親人中?小」?に入る漢字は何?', '薬'),
('「雨+二+ム=?」?に入る漢字は何?', '雲');
