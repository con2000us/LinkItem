<?php

include 'fetch_links.php';

// 获取链接数据
$linksData = fetchLinks();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>链接导航</title>
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
    </style>
</head>
<body>
    <!-- 链接模板将在这里渲染 -->
    <?php include 'link-template.html'; ?>

    <script>
        // 初始化 Vue 应用
        new Vue({
            el: '#link-cards',
            data: {
                links: []
            },
            created() {
                // 解析从 PHP 获取的数据
                let rawLinks = <?php echo $linksData; ?>;
                
                // 处理每个链接项的 cellCSS
                this.links = rawLinks.map(link => {
                    // 尝试解析 cellCSS JSON
                    if (link.cellCSS && link.cellCSS.trim() !== '') {
                        try {
                            const cssData = JSON.parse(link.cellCSS);
                            link.customStyle = cssData;
                        } catch (e) {
                            console.error('解析 cellCSS 时出错:', e);
                            link.customStyle = null;
                        }
                    }
                    return link;
                });
            },
            methods: {
                // 构建内网链接
                buildLanUrl(link) {
                    let url = 'http://' + link.lanhost;
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
                }
            }
        });
    </script>
</body>
</html>