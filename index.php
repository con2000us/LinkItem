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
                        // 添加默认内外网切换标记（默认使用内网）
                        link.useOuterLink = false;
                        
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
                    const allGroups = Object.entries(this.hostGroupedLinks).map(([groupName, hosts]) => {
                        return { groupName, hosts };
                    });
                    
                    // 如果没有分组，返回空数组
                    if (allGroups.length === 0) return {};
                    
                    // 根据列数分组，确保每列的hostGroup数量尽量均衡
                    const totalGroups = allGroups.length;
                    
                    // 如果组数小于或等于列数，则平均分配
                    if (totalGroups <= 3) { // 改为3列
                        // 如果当前列索引小于总组数，则返回对应的组
                        if (columnIndex < totalGroups) {
                            const group = allGroups[columnIndex];
                            const result = {};
                            result[group.groupName] = group.hosts;
                            return result;
                        }
                        return {};
                    }
                    
                    // 如果组数大于列数，则计算每列应该显示多少组
                    const groupsPerColumn = Math.ceil(totalGroups / 3); // 改为3列
                    const startIdx = columnIndex * groupsPerColumn;
                    const endIdx = Math.min(startIdx + groupsPerColumn, totalGroups);
                    
                    // 获取当前列应该显示的组
                    const columnGroups = allGroups.slice(startIdx, endIdx);
                    
                    // 构建结果对象
                    const result = {};
                    columnGroups.forEach(group => {
                        result[group.groupName] = group.hosts;
                    });
                    
                    return result;
                },
                
                // 获取链接的当前激活URL（内网或外网）
                getActiveUrl(link) {
                    // 默认使用内网链接，除非useOuterLink为true且存在外网链接
                    if (link.useOuterLink && link.outerhost) {
                        return this.buildOuterUrl(link);
                    } else if (link.host_ip) {
                        return this.buildLanUrl(link);
                    } else if (link.outerhost) {
                        // 如果只有外网链接则使用外网
                        return this.buildOuterUrl(link);
                    }
                    return '#';
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