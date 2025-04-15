<?php
session_start();

// 如果用戶已經登入，直接重定向到首頁
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: index.php');
    exit;
}

// 預設的用戶憑據 - 實際使用時請更改
$valid_username = "admin";
// 暫時使用純文本密碼來進行比較
$valid_password = "password";

// 你可以使用以下代碼生成新的密碼雜湊
// echo password_hash("你的密碼", PASSWORD_DEFAULT);

$message = '';
$remember_me = false;

// 檢查是否有保存的cookie
if (!isset($_POST['username']) && isset($_COOKIE['remember_user'])) {
    $saved_token = $_COOKIE['remember_user'];
    // 這裡應該有一個安全的方式來驗證token
    // 簡化版本，不建議在生產環境中使用
    if ($saved_token === hash('sha256', $valid_username . 'salt_value')) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $valid_username;
        header('Location: index.php');
        exit;
    }
}

// 處理登入表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // 使用純文本密碼進行比較
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        
        // 如果勾選了"記住我"
        if ($remember_me) {
            // 為用戶創建一個唯一標識符
            $token = hash('sha256', $username . 'salt_value');
            // 設置cookie，30天有效期
            setcookie('remember_user', $token, time() + (86400 * 30), '/');
        }
        
        header('Location: index.php');
        exit;
    } else {
        $message = '用戶名或密碼不正確';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - 連結轉運圖</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        
        .login-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 380px;
            padding: 35px;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 26px;
            margin: 0;
            position: relative;
        }
        
        .login-header h1:after {
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
        
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        
        .input-group input:focus {
            border-color: #5a55aa;
            box-shadow: 0 0 0 3px rgba(90, 85, 170, 0.15);
            outline: none;
        }
        
        .input-group i {
            position: absolute;
            left: 12px;
            top: 39px;
            color: #aaa;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .remember-me label {
            color: #666;
            font-size: 14px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: #5a55aa;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(90, 85, 170, 0.3);
        }
        
        .submit-btn:hover {
            background-color: #4a4590;
            box-shadow: 0 6px 15px rgba(90, 85, 170, 0.4);
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background-color: #fdf2f2;
            border-left: 4px solid #e74c3c;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>連結轉運圖 登入</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="input-group">
                <label for="username">用戶名</label>
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="input-group">
                <label for="password">密碼</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me" <?php echo $remember_me ? 'checked' : ''; ?>>
                <label for="remember_me">記住我</label>
            </div>
            
            <button type="submit" class="submit-btn">登入</button>
        </form>
    </div>
    
    <script>
        // 簡單的表單驗證
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('請填寫所有必填欄位');
            }
        });
    </script>
</body>
</html> 