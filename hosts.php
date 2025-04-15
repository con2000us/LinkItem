<?php
include 'fetch_links.php';

// 获取所有主机
$hostsData = fetchHosts();

// 处理表单提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = include 'config.php';
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    if (isset($_POST['action'])) {
        // 添加新主机
        if ($_POST['action'] === 'add') {
            $hostName = $conn->real_escape_string($_POST['host_name']);
            $hostIP = $conn->real_escape_string($_POST['host_ip']);
            
            $sql = "INSERT INTO hosts (host_name, host_ip) VALUES ('$hostName', '$hostIP')";
            
            if ($conn->query($sql) === TRUE) {
                $message = "新主机添加成功";
                // 刷新主机数据
                $hostsData = fetchHosts();
            } else {
                $message = "错误: " . $conn->error;
            }
        }
        
        // 编辑主机
        if ($_POST['action'] === 'edit') {
            $hostId = (int)$_POST['host_id'];
            $hostName = $conn->real_escape_string($_POST['host_name']);
            $hostIP = $conn->real_escape_string($_POST['host_ip']);
            
            $sql = "UPDATE hosts SET host_name = '$hostName', host_ip = '$hostIP' WHERE host_id = $hostId";
            
            if ($conn->query($sql) === TRUE) {
                $message = "主机更新成功";
                // 刷新主机数据
                $hostsData = fetchHosts();
            } else {
                $message = "错误: " . $conn->error;
            }
        }
        
        // 删除主机
        if ($_POST['action'] === 'delete') {
            $hostId = (int)$_POST['host_id'];
            
            // 先检查是否有链接使用此主机
            $checkSql = "SELECT COUNT(*) as count FROM links WHERE lanhost = $hostId";
            $checkResult = $conn->query($checkSql);
            $checkData = $checkResult->fetch_assoc();
            
            if ($checkData['count'] > 0) {
                $message = "无法删除：此主机仍被 " . $checkData['count'] . " 个链接使用";
            } else {
                $sql = "DELETE FROM hosts WHERE host_id = $hostId";
                
                if ($conn->query($sql) === TRUE) {
                    $message = "主机删除成功";
                    // 刷新主机数据
                    $hostsData = fetchHosts();
                } else {
                    $message = "错误: " . $conn->error;
                }
            }
        }
    }
    
    $conn->close();
}

// 处理JSON数据
$hosts = json_decode($hostsData);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>主机管理</title>
    <!-- 加载 Vue.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <!-- 添加 Font Awesome 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .navigation a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            text-decoration: none;
            color: #3498db;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .navigation a:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="index.php"><i class="fas fa-home"></i> 返回主页</a>
        </div>
        
        <h1>主机管理</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, '错误') !== false || strpos($message, '无法') !== false ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- 添加新主机表单 -->
        <div class="card">
            <div class="card-header">
                <h2>添加新主机</h2>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="host_name">主机名称</label>
                        <input type="text" id="host_name" name="host_name" required placeholder="例如: Unraid服务器">
                    </div>
                    <div class="form-group">
                        <label for="host_ip">IP地址</label>
                        <input type="text" id="host_ip" name="host_ip" required placeholder="例如: 192.168.1.100">
                    </div>
                    <button type="submit" class="btn btn-primary">添加主机</button>
                </form>
            </div>
        </div>
        
        <!-- 主机列表 -->
        <div class="card">
            <div class="card-header">
                <h2>已有主机</h2>
            </div>
            <div class="card-body">
                <?php if (empty($hosts)): ?>
                    <p>暂无主机记录</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>主机名称</th>
                                <th>IP地址</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hosts as $host): ?>
                                <tr>
                                    <td><?php echo $host->host_id; ?></td>
                                    <td><?php echo htmlspecialchars($host->host_name); ?></td>
                                    <td><?php echo htmlspecialchars($host->host_ip); ?></td>
                                    <td class="actions">
                                        <button class="btn btn-primary" onclick="showEditForm(<?php echo $host->host_id; ?>, '<?php echo htmlspecialchars($host->host_name); ?>', '<?php echo htmlspecialchars($host->host_ip); ?>')">编辑</button>
                                        <form method="post" action="" onsubmit="return confirm('确定要删除这个主机吗？');" style="display: inline-block;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="host_id" value="<?php echo $host->host_id; ?>">
                                            <button type="submit" class="btn btn-danger">删除</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 编辑主机表单 (默认隐藏) -->
        <div id="editForm" class="card" style="display: none;">
            <div class="card-header">
                <h2>编辑主机</h2>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_host_id" name="host_id">
                    <div class="form-group">
                        <label for="edit_host_name">主机名称</label>
                        <input type="text" id="edit_host_name" name="host_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_host_ip">IP地址</label>
                        <input type="text" id="edit_host_ip" name="host_ip" required>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <button type="button" class="btn" onclick="hideEditForm()">取消</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // 显示编辑表单
        function showEditForm(hostId, hostName, hostIP) {
            document.getElementById('edit_host_id').value = hostId;
            document.getElementById('edit_host_name').value = hostName;
            document.getElementById('edit_host_ip').value = hostIP;
            document.getElementById('editForm').style.display = 'block';
            
            // 滚动到表单位置
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        // 隐藏编辑表单
        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
</body>
</html> 