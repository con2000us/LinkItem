<?php
session_start();

// 檢查用戶是否已登入，只允許已登入用戶訪問此頁面
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$hash_result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (strlen($password) < 6) {
        $message = '密碼應至少為6個字符';
    } else {
        // 使用 PASSWORD_DEFAULT 算法生成雜湊值
        $hash_result = password_hash($password, PASSWORD_DEFAULT);
        $message = '密碼雜湊生成成功！';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>密碼雜湊生成器</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: #5a55aa;
            border-radius: 3px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        
        input[type="password"]:focus, input[type="text"]:focus {
            border-color: #5a55aa;
            box-shadow: 0 0 0 3px rgba(90, 85, 170, 0.15);
            outline: none;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #5a55aa;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #4a4590;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(90, 85, 170, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .result-container {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #5a55aa;
        }
        
        .result-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .result-value {
            word-break: break-all;
            font-family: monospace;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow-x: auto;
        }
        
        .instruction {
            background-color: #e9f7fe;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
            border-left: 4px solid #3498db;
        }
        
        .instruction h3 {
            margin-top: 0;
            color: #3498db;
        }
        
        .instruction code {
            background-color: #f8f9fa;
            padding: 3px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .show-password {
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        
        .show-password input {
            margin-right: 8px;
        }
        
        .copy-btn {
            padding: 8px 12px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .copy-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>密碼雜湊生成器</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '成功') !== false ? 'alert-success' : 'alert-warning'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="password">輸入需要雜湊的密碼：</label>
                <input type="password" id="password" name="password" required minlength="6">
                
                <div class="show-password">
                    <input type="checkbox" id="show_password" onchange="togglePasswordVisibility()">
                    <label for="show_password">顯示密碼</label>
                </div>
            </div>
            
            <button type="submit" class="btn">生成雜湊</button>
        </form>
        
        <?php if ($hash_result): ?>
            <div class="result-container">
                <div class="result-label">密碼雜湊結果：</div>
                <div class="result-value" id="hash-value"><?php echo $hash_result; ?></div>
                <button class="copy-btn" onclick="copyToClipboard()">複製雜湊值</button>
            </div>
            
            <div class="instruction">
                <h3>如何使用此雜湊值?</h3>
                <p>請在 login.php 文件中找到以下代碼：</p>
                <pre><code>// 暫時使用純文本密碼來進行比較
$valid_password = "password";</code></pre>
                <p>將其替換為：</p>
                <pre><code>// 使用密碼雜湊存儲密碼
$valid_password_hash = '<?php echo $hash_result; ?>';</code></pre>
                <p>然後將驗證代碼：</p>
                <pre><code>// 使用純文本密碼進行比較
if ($username === $valid_username && $password === $valid_password) {</code></pre>
                <p>替換為：</p>
                <pre><code>if ($username === $valid_username && password_verify($password, $valid_password_hash)) {</code></pre>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-secondary">返回首頁</a>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
        
        function copyToClipboard() {
            const hashValue = document.getElementById('hash-value').textContent;
            navigator.clipboard.writeText(hashValue)
                .then(() => {
                    alert('雜湊值已複製到剪貼板');
                })
                .catch(err => {
                    console.error('複製失敗: ', err);
                    alert('複製失敗，請手動選擇並複製');
                });
        }
    </script>
</body>
</html> 