<?php
session_start();
require_once 'db.php';

$pdo = getDB();
$result_message = '';
$user_answer = '';
$correct_answer = '';

// 新しい問題を取得
if (!isset($_SESSION['current_question']) || isset($_POST['next'])) {
    // ランダムに問題を取得
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY RANDOM() LIMIT 1");
    $question = $stmt->fetch();

    if ($question) {
        $_SESSION['current_question'] = $question;
    } else {
        $_SESSION['current_question'] = null;
    }
    unset($_SESSION['result']);
}

// 解答の判定
if (isset($_POST['answer']) && isset($_SESSION['current_question'])) {
    $user_answer = trim($_POST['answer']);
    $correct_answer = $_SESSION['current_question']['answer'];

    if ($user_answer === $correct_answer) {
        $_SESSION['result'] = '正解です';
    } else {
        $_SESSION['result'] = '不正解です(正解: ' . h($correct_answer) . ')';
    }
}

$current_question = $_SESSION['current_question'] ?? null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ出題</title>
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
        .question-box {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 18px;
            color: #1565c0;
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
            border-color: #2196F3;
        }
        button {
            background-color: #2196F3;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #0b7dda;
        }
        .result {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .result.correct {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .result.incorrect {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .no-questions {
            background-color: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            font-size: 18px;
            border: 1px solid #ffeaa7;
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
        <h1>クイズ出題</h1>

        <?php if ($current_question): ?>
            <div class="question-box">
                問題: <?php echo h($current_question['question']); ?>
            </div>

            <?php if (isset($_SESSION['result'])): ?>
                <div class="result <?php echo strpos($_SESSION['result'], '正解') !== false ? 'correct' : 'incorrect'; ?>">
                    結果表示<br>
                    <?php echo h($_SESSION['result']); ?>
                </div>
                <form method="POST" action="">
                    <button type="submit" name="next" value="1">次の問題</button>
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="answer">答え:</label>
                        <input type="text" id="answer" name="answer" required autofocus>
                    </div>
                    <button type="submit">解答</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-questions">
                問題がありません。
            </div>
        <?php endif; ?>

        <div class="links">
            <a href="index.php">トップ</a>
            <a href="edit.php">編集</a>
            <a href="create.php">新規作成</a>
        </div>
    </div>
</body>
</html>
