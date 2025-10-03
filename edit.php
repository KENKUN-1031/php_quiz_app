<?php
require_once 'db.php';

$pdo = getDB();
$message = '';
$error = '';
$selected_question = null;

// 全問題を取得
$stmt = $pdo->query("SELECT * FROM questions ORDER BY id");
$all_questions = $stmt->fetchAll();

// 読込ボタンが押された場合
if (isset($_POST['load']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $selected_question = $stmt->fetch();

    if (!$selected_question) {
        $error = '指定された番号の問題が見つかりません。';
    }
}

// 修正ボタンが押された場合
if (isset($_POST['update'])) {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $question = isset($_POST['question']) ? trim($_POST['question']) : '';
    $answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';

    if (empty($id)) {
        $error = '番号を入力してください。';
    } elseif (empty($question) || empty($answer)) {
        $error = '問題と答えを入力してください。';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE questions SET question = :question, answer = :answer WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':question', $question, PDO::PARAM_STR);
            $stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $message = '番号' . h($id) . 'の問題を修正しました。';
                // リスト更新
                $stmt = $pdo->query("SELECT * FROM questions ORDER BY id");
                $all_questions = $stmt->fetchAll();
            } else {
                $error = '指定された番号の問題が見つかりません。';
            }
        } catch (PDOException $e) {
            $error = 'データベースエラー: ' . h($e->getMessage());
        }
    }
}

// 削除ボタンが押された場合
if (isset($_POST['delete'])) {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';

    if (empty($id)) {
        $error = '番号を入力してください。';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $message = '番号' . h($id) . 'の問題を削除しました。';
                // リスト更新
                $stmt = $pdo->query("SELECT * FROM questions ORDER BY id");
                $all_questions = $stmt->fetchAll();
            } else {
                $error = '指定された番号の問題が見つかりません。';
            }
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
    <title>クイズ編集</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
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
        h2 {
            color: #555;
            margin-top: 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ff9800;
            padding-bottom: 10px;
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
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #ff9800;
        }
        button {
            background-color: #ff9800;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #e68900;
        }
        button[name="delete"] {
            background-color: #f44336;
        }
        button[name="delete"]:hover {
            background-color: #da190b;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #ff9800;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f5f5f5;
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
        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>クイズ編集</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="id">番号: <span style="color: red;">*</span></label>
                <input type="number" id="id" name="id" value="<?php echo $selected_question ? h($selected_question['id']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="question">問題:</label>
                <input type="text" id="question" name="question" value="<?php echo $selected_question ? h($selected_question['question']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="answer">答え:</label>
                <input type="text" id="answer" name="answer" value="<?php echo $selected_question ? h($selected_question['answer']) : ''; ?>">
            </div>

            <button type="submit" name="load">読込(番号)</button>
            <button type="submit" name="update">修正</button>
            <button type="submit" name="delete">削除</button>
        </form>

        <h2>クイズ一覧</h2>

        <?php if (count($all_questions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>番号</th>
                        <th>問題</th>
                        <th>答え</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_questions as $q): ?>
                        <tr>
                            <td><?php echo h($q['id']); ?></td>
                            <td><?php echo h($q['question']); ?></td>
                            <td><?php echo h($q['answer']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>問題が登録されていません。</p>
        <?php endif; ?>

        <div class="links">
            <a href="index.php">トップ</a>
            <a href="create.php">新規作成</a>
            <a href="quiz.php">出題</a>
        </div>
    </div>
</body>
</html>
