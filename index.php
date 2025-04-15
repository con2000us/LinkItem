<?php
// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'fetch_links.php';

// 获取链接数据
$linksData = fetchLinks();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>連結轉運圖</title>
    <!-- 加载 Vue.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <!-- 添加 Font Awesome 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }
        .error-container {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- 错误信息显示区域 -->
    <div class="error-container" id="error-container" style="display: none;">
        <h3>发生错误</h3>
        <p id="error-message"></p>
    </div>

    <!-- 链接模板将在这里渲染 -->
    <?php include 'link-template.html'; ?>

    <script>
        // 初始化 Vue 应用
        new Vue({
            el: '#link-cards',
            data: {
                links: []
            },
            computed: {
                // 首先按照 hostGroup 分组，然后在每个组内按照 lanhost (host_id) 分组链接
                hostGroupedLinks() {
                    const groupsByHostGroup = {};
                    
                    // 先按 hostGroup 分组
                    this.links.forEach(link => {
                        const hostGroup = link.hostGroup || '未分组';
                        if (!groupsByHostGroup[hostGroup]) {
                            groupsByHostGroup[hostGroup] = {};
                        }
                        
                        // 然后在每个 hostGroup 内按 lanhost 再次分组
                        const hostId = link.lanhost || 'other';
                        if (!groupsByHostGroup[hostGroup][hostId]) {
                            groupsByHostGroup[hostGroup][hostId] = [];
                        }
                        groupsByHostGroup[hostGroup][hostId].push(link);
                    });
                    
                    // 转换格式为更易于遍历的格式
                    const result = {};
                    for (const [groupName, hosts] of Object.entries(groupsByHostGroup)) {
                        result[groupName] = Object.entries(hosts).map(([hostId, links]) => ({
                            groupKey: hostId,
                            links: links
                        }));
                    }
                    
                    return result;
                }
            },
            created() {
                try {
                    // 解析从 PHP 获取的数据
                    let rawLinks = <?php echo $linksData ? $linksData : '[]'; ?>;
                    
                    // 处理每个链接项的 cellCSS
                    this.links = rawLinks.map(link => {
                        // 尝试解析 cellCSS JSON
                        if (link.cellCSS && link.cellCSS.trim() !== '') {
                            try {
                                const cssData = JSON.parse(link.cellCSS);
                                link.customStyle = cssData;
                            } catch (e) {
                                console.error('解析 cellCSS 时出错:', e);
                                this.showError('解析 cellCSS 时出错: ' + e.message);
                                link.customStyle = null;
                            }
                        }
                        return link;
                    });
                } catch (error) {
                    console.error('初始化数据时出错:', error);
                    this.showError('初始化数据时出错: ' + error.message);
                }
            },
            methods: {
                // 为每列获取主机组
                getHostsForColumn(hosts, columnIndex) {
                    if (!hosts || !Array.isArray(hosts)) return [];
                    
                    // 每列最多显示5个host
                    const hostsPerColumn = 5;
                    const startIndex = columnIndex * hostsPerColumn;
                    const endIndex = startIndex + hostsPerColumn;
                    
                    return hosts.slice(startIndex, endIndex);
                },
                
                // 构建内网链接
                buildLanUrl(link) {
                    if (!link.host_ip) return '#';
                    
                    let url = 'http://' + link.host_ip;
                    if (link.lanport && link.lanport != 80) {
                        url += ':' + link.lanport;
                    }
                    if (link.lanDir) {
                        url += '/' + link.lanDir;
                    }
                    return url;
                },
                
                // 构建外网链接
                buildOuterUrl(link) {
                    let url = 'http://' + link.outerhost;
                    if (link.outerport && link.outerport != 80) {
                        url += ':' + link.outerport;
                    }
                    if (link.outerDir) {
                        url += '/' + link.outerDir;
                    }
                    return url;
                },
                
                // 显示错误信息
                showError(message) {
                    const errorContainer = document.getElementById('error-container');
                    const errorMessage = document.getElementById('error-message');
                    errorMessage.textContent = message;
                    errorContainer.style.display = 'block';
                }
            }
        });
        
        // 捕获全局 JavaScript 错误
        window.onerror = function(message, source, lineno, colno, error) {
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = `JavaScript 错误: ${message} (${source}: ${lineno}:${colno})`;
            errorContainer.style.display = 'block';
            return false;
        };
    </script>
</body>
</html>