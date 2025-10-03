<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ作成と出題</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .menu a {
            display: block;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        .menu a:hover {
            background-color: #45a049;
        }
        .menu a:nth-child(2) {
            background-color: #2196F3;
        }
        .menu a:nth-child(2):hover {
            background-color: #0b7dda;
        }
        .menu a:nth-child(3) {
            background-color: #ff9800;
        }
        .menu a:nth-child(3):hover {
            background-color: #e68900;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>クイズ作成と出題</h1>
        <p class="subtitle">クイズを作成保存し、保存したクイズを出題します。</p>

        <div class="menu">
            <a href="create.php">新規作成</a>
            <a href="edit.php">編集</a>
            <a href="quiz.php">出題</a>
        </div>
    </div>
</body>
</html>
