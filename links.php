<?php
// 引入身份驗證檢查
include 'auth_check.php';

include 'fetch_links.php';

// 获取所有链接和主机
$linksData = fetchLinks();
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
        // 添加新链接
        if ($_POST['action'] === 'add') {
            $title = $conn->real_escape_string($_POST['title']);
            $linkUrl = $conn->real_escape_string($_POST['link_url']);
            $logoUrl = $conn->real_escape_string($_POST['logo_url']);
            $description = $conn->real_escape_string($_POST['description']);
            $lanhost = (int)$_POST['lanhost'];
            $linkOrder = (int)$_POST['link_order'];
            
            $sql = "INSERT INTO links (title, linkUrl, logoUrl, description, lanhost, linkOrder) 
                    VALUES ('$title', '$linkUrl', '$logoUrl', '$description', $lanhost, $linkOrder)";
            
            if ($conn->query($sql) === TRUE) {
                $message = "新链接添加成功";
                // 刷新链接数据
                $linksData = fetchLinks();
            } else {
                $message = "错误: " . $conn->error;
            }
        }
        
        // 编辑链接
        if ($_POST['action'] === 'edit') {
            $linkId = (int)$_POST['link_id'];
            $title = $conn->real_escape_string($_POST['title']);
            $linkUrl = $conn->real_escape_string($_POST['link_url']);
            $logoUrl = $conn->real_escape_string($_POST['logo_url']);
            $description = $conn->real_escape_string($_POST['description']);
            $lanhost = (int)$_POST['lanhost'];
            $linkOrder = (int)$_POST['link_order'];
            
            $sql = "UPDATE links SET 
                    title = '$title', 
                    linkUrl = '$linkUrl', 
                    logoUrl = '$logoUrl', 
                    description = '$description', 
                    lanhost = $lanhost, 
                    linkOrder = $linkOrder 
                    WHERE link_id = $linkId";
            
            if ($conn->query($sql) === TRUE) {
                $message = "链接更新成功";
                // 刷新链接数据
                $linksData = fetchLinks();
            } else {
                $message = "错误: " . $conn->error;
            }
        }
        
        // 删除链接
        if ($_POST['action'] === 'delete') {
            $linkId = (int)$_POST['link_id'];
            
            $sql = "DELETE FROM links WHERE link_id = $linkId";
            
            if ($conn->query($sql) === TRUE) {
                $message = "链接删除成功";
                // 刷新链接数据
                $linksData = fetchLinks();
            } else {
                $message = "错误: " . $conn->error;
            }
        }
    }
    
    $conn->close();
}

// 处理JSON数据
$links = json_decode($linksData);
$hosts = json_decode($hostsData);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>链接管理</title>
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
            max-width: 1200px;
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
        
        input[type="text"], 
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .logo-preview {
            margin-top: 10px;
            max-width: 100px;
            max-height: 100px;
            display: block;
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
        
        .link-item {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .link-logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            object-fit: contain;
        }
        
        .link-info {
            flex-grow: 1;
        }
        
        .link-title {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .link-url {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .link-description {
            color: #555;
        }
        
        .link-host {
            padding: 3px 8px;
            background-color: #e9f7fe;
            color: #3498db;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 8px;
        }
        
        .link-order {
            padding: 3px 8px;
            background-color: #f5f5f5;
            color: #555;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination-item {
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            cursor: pointer;
        }
        
        .pagination-item.active {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container" id="app">
        <div class="navigation">
            <a href="index.php"><i class="fas fa-home"></i> 返回主页</a>
            <a href="hosts.php"><i class="fas fa-server"></i> 主机管理</a>
        </div>
        
        <h1>链接管理</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, '错误') !== false ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- 添加新链接表单 -->
        <div class="card">
            <div class="card-header">
                <h2>添加新链接</h2>
            </div>
            <div class="card-body">
                <form method="post" action="" id="addLinkForm">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="title">标题</label>
                        <input type="text" id="title" name="title" required placeholder="服务名称">
                    </div>
                    <div class="form-group">
                        <label for="link_url">链接地址</label>
                        <input type="text" id="link_url" name="link_url" required placeholder="例如: https://example.com 或 /path">
                    </div>
                    <div class="form-group">
                        <label for="logo_url">图标 URL</label>
                        <input type="text" id="logo_url" name="logo_url" required placeholder="例如: https://example.com/icon.png 或 图标名称 (fa-home)">
                        <div id="logoPreviewContainer" style="display:none">
                            <img id="logoPreview" class="logo-preview" src="" alt="图标预览">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">描述</label>
                        <textarea id="description" name="description" placeholder="服务描述信息"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="lanhost">所属主机</label>
                        <select id="lanhost" name="lanhost" required>
                            <option value="">-- 选择主机 --</option>
                            <?php foreach ($hosts as $host): ?>
                                <option value="<?php echo $host->host_id; ?>">
                                    <?php echo htmlspecialchars($host->host_name); ?> (<?php echo htmlspecialchars($host->host_ip); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="link_order">显示顺序</label>
                        <input type="number" id="link_order" name="link_order" required min="1" value="1">
                    </div>
                    <button type="submit" class="btn btn-primary">添加链接</button>
                </form>
            </div>
        </div>
        
        <!-- 链接列表 -->
        <div class="card">
            <div class="card-header">
                <h2>已有链接</h2>
            </div>
            <div class="card-body">
                <?php if (empty($links)): ?>
                    <p>暂无链接记录</p>
                <?php else: ?>
                    <?php 
                    // 创建主机 ID 到名称的映射
                    $hostMap = [];
                    foreach ($hosts as $host) {
                        $hostMap[$host->host_id] = $host->host_name;
                    }
                    ?>
                    
                    <!-- 一页显示10条记录 -->
                    <div id="linksList">
                        <?php foreach ($links as $index => $link): ?>
                            <div class="link-item">
                                <?php if (strpos($link->logoUrl, 'fa-') === 0): ?>
                                    <i class="fas <?php echo htmlspecialchars($link->logoUrl); ?> fa-3x" style="width: 50px; text-align: center;"></i>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($link->logoUrl); ?>" alt="<?php echo htmlspecialchars($link->title); ?>" class="link-logo">
                                <?php endif; ?>
                                
                                <div class="link-info">
                                    <div class="link-title"><?php echo htmlspecialchars($link->title); ?></div>
                                    <div class="link-url"><?php echo htmlspecialchars($link->linkUrl); ?></div>
                                    <div class="link-description"><?php echo htmlspecialchars($link->description); ?></div>
                                    <div style="margin-top: 8px;">
                                        <span class="link-host">
                                            <?php echo isset($hostMap[$link->lanhost]) ? htmlspecialchars($hostMap[$link->lanhost]) : '未知主机'; ?>
                                        </span>
                                        <span class="link-order">排序: <?php echo $link->linkOrder; ?></span>
                                    </div>
                                </div>
                                
                                <div class="actions">
                                    <button class="btn btn-primary" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($link)); ?>)">编辑</button>
                                    <form method="post" action="" onsubmit="return confirm('确定要删除这个链接吗？');" style="display: inline-block;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="link_id" value="<?php echo $link->link_id; ?>">
                                        <button type="submit" class="btn btn-danger">删除</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 编辑链接表单 (默认隐藏) -->
        <div id="editForm" class="card" style="display: none;">
            <div class="card-header">
                <h2>编辑链接</h2>
            </div>
            <div class="card-body">
                <form method="post" action="" id="editLinkForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_link_id" name="link_id">
                    <div class="form-group">
                        <label for="edit_title">标题</label>
                        <input type="text" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_link_url">链接地址</label>
                        <input type="text" id="edit_link_url" name="link_url" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_logo_url">图标 URL</label>
                        <input type="text" id="edit_logo_url" name="logo_url" required>
                        <div id="editLogoPreviewContainer" style="display:none">
                            <img id="editLogoPreview" class="logo-preview" src="" alt="图标预览">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">描述</label>
                        <textarea id="edit_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_lanhost">所属主机</label>
                        <select id="edit_lanhost" name="lanhost" required>
                            <option value="">-- 选择主机 --</option>
                            <?php foreach ($hosts as $host): ?>
                                <option value="<?php echo $host->host_id; ?>">
                                    <?php echo htmlspecialchars($host->host_name); ?> (<?php echo htmlspecialchars($host->host_ip); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_link_order">显示顺序</label>
                        <input type="number" id="edit_link_order" name="link_order" required min="1">
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <button type="button" class="btn" onclick="hideEditForm()">取消</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // 图标预览功能
        document.getElementById('logo_url').addEventListener('input', function(e) {
            const logoUrl = e.target.value;
            const previewContainer = document.getElementById('logoPreviewContainer');
            const preview = document.getElementById('logoPreview');
            
            if (logoUrl && !logoUrl.includes('fa-')) {
                preview.src = logoUrl;
                previewContainer.style.display = 'block';
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        document.getElementById('edit_logo_url').addEventListener('input', function(e) {
            const logoUrl = e.target.value;
            const previewContainer = document.getElementById('editLogoPreviewContainer');
            const preview = document.getElementById('editLogoPreview');
            
            if (logoUrl && !logoUrl.includes('fa-')) {
                preview.src = logoUrl;
                previewContainer.style.display = 'block';
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        // 显示编辑表单
        function showEditForm(link) {
            document.getElementById('edit_link_id').value = link.link_id;
            document.getElementById('edit_title').value = link.title;
            document.getElementById('edit_link_url').value = link.linkUrl;
            document.getElementById('edit_logo_url').value = link.logoUrl;
            document.getElementById('edit_description').value = link.description;
            document.getElementById('edit_lanhost').value = link.lanhost;
            document.getElementById('edit_link_order').value = link.linkOrder;
            
            // 显示图标预览
            const previewContainer = document.getElementById('editLogoPreviewContainer');
            const preview = document.getElementById('editLogoPreview');
            
            if (link.logoUrl && !link.logoUrl.includes('fa-')) {
                preview.src = link.logoUrl;
                previewContainer.style.display = 'block';
            } else {
                previewContainer.style.display = 'none';
            }
            
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