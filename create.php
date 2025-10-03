<?php
require_once 'db.php';

$message = '';
$error = '';

// POSTリクエスト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = isset($_POST['question']) ? trim($_POST['question']) : '';
    $answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';

    // バリデーション
    if (empty($question) || empty($answer)) {
        $error = 'このフィールドを入力してください。';
    } else {
        try {
            $pdo = getDB();
            // プリペアードステートメントを使用してSQLインジェクション対策
            $stmt = $pdo->prepare("INSERT INTO questions (question, answer) VALUES (:question, :answer)");
            $stmt->bindParam(':question', $question, PDO::PARAM_STR);
            $stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
            $stmt->execute();

            $message = '保存できました。';
            // 保存後、入力欄をクリア
            $question = '';
            $answer = '';
        } catch (PDOException $e) {
            $error = 'データベースエラー: ' . h($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ新規作成</title>
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
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .links {
            margin-top: 30px;
        }
        .links a {
            color: #2196F3;
            text-decoration: none;
            margin-right: 20px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>クイズ新規作成</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="question">問題:</label>
                <input type="text" id="question" name="question" value="<?php echo isset($_POST['question']) ? h($_POST['question']) : ''; ?>" placeholder="サザエさんの弟は?(カタカナ)" required>
            </div>

            <div class="form-group">
                <label for="answer">答え:</label>
                <input type="text" id="answer" name="answer" value="<?php echo isset($_POST['answer']) && !$message ? h($_POST['answer']) : ''; ?>" placeholder="カツオ" required>
            </div>

            <button type="submit">保存</button>
        </form>

        <div class="links">
            <a href="index.php">トップ</a>
            <a href="edit.php">編集</a>
            <a href="quiz.php">出題</a>
        </div>
    </div>
</body>
</html>
