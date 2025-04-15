<?php
// 引入身份驗證檢查
include 'auth_check.php';

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
    <title>轉運圖</title>
    <!-- Favicon設定 -->
    <link rel="icon" href="favicon.php" type="image/svg+xml">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="favicon.php">
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
        /* 登出按鈕樣式 */
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background-color: rgba(255, 255, 255, 0.8);
            color: #333;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .logout-btn i {
            font-size: 16px;
        }

        .user-welcome {
            position: fixed;
            top: 60px;
            right: 20px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-tool {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background-color: rgba(255, 255, 255, 0.8);
            color: #5a55aa;
            border-radius: 50%;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .user-tool:hover {
            background-color: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <!-- 添加登出按鈕 -->
    <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> 登出
    </a>
    
    <?php if (isset($_SESSION['username'])): ?>
    <div class="user-welcome">
        您好，<?php echo htmlspecialchars($_SESSION['username']); ?>
        <a href="generate_hash.php" class="user-tool" title="密碼雜湊工具">
            <i class="fas fa-key"></i>
        </a>
    </div>
    <?php endif; ?>

    <div id="app">
        <!-- 错误信息显示区域 -->
        <div id="error-container" style="display: none;">
            <h3>发生错误</h3>
            <p id="error-message"></p>
        </div>

        <!-- 链接模板将在这里渲染 -->
        <?php include 'link-template.html'; ?>
    </div>

    <script>
        // 初始化 Vue 应用
        new Vue({
            el: '#link-cards',
            data: {
                links: []
            },
            computed: {
                // 首先按照 hostGroup 分组，然后按照 linkOrder 排序链接
                hostGroupedLinks() {
                    const groupsByHostGroup = {};
                    
                    // 先按 hostGroup 分组
                    this.links.forEach(link => {
                        const hostGroup = link.hostGroup || '未分组';
                        if (!groupsByHostGroup[hostGroup]) {
                            groupsByHostGroup[hostGroup] = [];
                        }
                        
                        // 直接添加到对应的 hostGroup 组中
                        groupsByHostGroup[hostGroup].push(link);
                    });
                    
                    // 在每个 hostGroup 内按 linkOrder 排序
                    for (const groupName in groupsByHostGroup) {
                        groupsByHostGroup[groupName].sort((a, b) => {
                            return (a.linkOrder || 999) - (b.linkOrder || 999);
                        });
                    }
                    
                    return groupsByHostGroup;
                }
            },
            created() {
                try {
                    // 解析从 PHP 获取的数据
                    let rawLinks = <?php echo $linksData ? $linksData : '[]'; ?>;
                    
                    // 处理每个链接项的 cellCSS
                    this.links = rawLinks.map(link => {
                        // 添加默认内外网切换标记（根据端口情况决定）
                        if (link.lanport === '0') {
                            // 内网端口为0，只能使用外网
                            link.useOuterLink = true;
                        } else if (link.outerport === '0') {
                            // 外网端口为0，只能使用内网
                            link.useOuterLink = false;
                        } else {
                            // 两者都可用，默认使用内网
                            link.useOuterLink = false;
                        }
                        
                        // 尝试解析 cellCSS JSON
                        if (link.cellCSS && link.cellCSS.trim() !== '') {
                            try {
                                const cssData = JSON.parse(link.cellCSS);
                                link.customStyle = cssData;
                                
                                // 如果有backgroundColor，计算其RGB值用于阴影
                                if (cssData.contentStyle && cssData.contentStyle.backgroundColor) {
                                    // 提取十六进制颜色值
                                    const color = cssData.contentStyle.backgroundColor;
                                    // 将十六进制转换为RGB
                                    const rgb = this.hexToRgb(color);
                                    if (rgb) {
                                        // 添加到自定义样式中
                                        cssData.contentStyle['--card-bg-rgb'] = `${rgb.r}, ${rgb.g}, ${rgb.b}`;
                                    }
                                }
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
                // 将十六进制颜色转换为RGB
                hexToRgb(hex) {
                    // 去掉#号
                    hex = hex.replace(/^#/, '');
                    
                    // 处理简写形式 (#rgb)
                    if (hex.length === 3) {
                        hex = hex.split('').map(char => char + char).join('');
                    }
                    
                    // 转换为RGB
                    const bigint = parseInt(hex, 16);
                    return {
                        r: (bigint >> 16) & 255,
                        g: (bigint >> 8) & 255,
                        b: bigint & 255
                    };
                },
                
                // 获取某一列应该显示的链接
                getLinksForColumn(hostGroups, columnIndex) {
                    if (!hostGroups || !Array.isArray(hostGroups)) return [];
                    
                    // 平铺所有链接，并添加显示索引和主机标签信息
                    const flattenedLinks = [];
                    let globalIndex = 0;
                    
                    hostGroups.forEach(host => {
                        if (!host.links || !host.links.length) return;
                        
                        let firstInHost = true;
                        host.links.forEach((link, index) => {
                            // 添加显示索引和是否显示主机名标志
                            const enhancedLink = {...link};
                            enhancedLink.displayIndex = String(index + 1).padStart(2, '0');
                            enhancedLink.showHostName = firstInHost;
                            firstInHost = false;
                            flattenedLinks.push(enhancedLink);
                            globalIndex++;
                        });
                    });
                    
                    // 分成4列，使用的分配算法让每列链接数量尽量平均
                    const totalLinks = flattenedLinks.length;
                    const linksPerColumn = Math.ceil(totalLinks / 4);
                    
                    const startIndex = columnIndex * linksPerColumn;
                    const endIndex = Math.min(startIndex + linksPerColumn, totalLinks);
                    
                    return flattenedLinks.slice(startIndex, endIndex);
                },
                
                // 获取每列显示的hostGroup
                getHostGroupsForColumn(columnIndex) {
                    // 所有hostGroup分组
                    const allGroups = Object.entries(this.hostGroupedLinks).map(([groupId, links]) => {
                        return { groupId, links };
                    });
                    
                    // 如果没有分组，返回空数组
                    if (allGroups.length === 0) return {};
                    
                    // 根据列数分组，确保每列的hostGroup数量尽量均衡
                    const totalGroups = allGroups.length;
                    
                    // 如果组数小于或等于列数，则平均分配
                    if (totalGroups <= 4) { // 4列布局
                        // 如果当前列索引小于总组数，则返回对应的组
                        if (columnIndex < totalGroups) {
                            const group = allGroups[columnIndex];
                            const result = {};
                            result[group.groupId] = group.links;
                            return result;
                        }
                        return {};
                    }
                    
                    // 如果组数大于列数，则计算每列应该显示多少组
                    const groupsPerColumn = Math.ceil(totalGroups / 4); // 4列布局
                    const startIdx = columnIndex * groupsPerColumn;
                    const endIdx = Math.min(startIdx + groupsPerColumn, totalGroups);
                    
                    // 获取当前列应该显示的组
                    const columnGroups = allGroups.slice(startIdx, endIdx);
                    
                    // 构建结果对象
                    const result = {};
                    columnGroups.forEach(group => {
                        result[group.groupId] = group.links;
                    });
                    
                    return result;
                },
                
                // 获取链接的当前激活URL（内网或外网）
                getActiveUrl(link) {
                    if (link.useOuterLink && link.outerhost) {
                        return (link.outerprotocol || 'http') + '://' + 
                               link.outerhost + 
                               (link.outerport && link.outerport != 80 ? ':' + link.outerport : '') + 
                               (link.outerDir ? '/' + link.outerDir : '');
                    } else {
                        return (link.lanprotocol || 'http') + '://' + 
                               link.host_ip + 
                               (link.lanport && link.lanport != 80 ? ':' + link.lanport : '') + 
                               (link.lanDir ? '/' + link.lanDir : '');
                    }
                },
                
                // 打开链接
                openLink(link) {
                    const url = this.getActiveUrl(link);
                    if (url !== '#') {
                        window.open(url, '_blank');
                    }
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
                },
                
                // 切换网络（内网/外网）
                toggleNetwork(event, link) {
                    // 阻止事件冒泡，避免触发卡片的点击事件
                    event.stopPropagation();
                    
                    // 检查端口是否为0（不可用）
                    if (link.lanport === '0' || link.outerport === '0') {
                        return; // 如果任一端口不可用，则不进行切换
                    }
                    
                    // 切换网络状态
                    link.useOuterLink = !link.useOuterLink;
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