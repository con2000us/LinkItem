<?php

include 'fetch_links.php';

// 获取链接数据
$linksData = fetchLinks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Link Manager</h1>
        <div id="app">
            <div v-if="links.length === 0">Loading...</div>
            <div v-else v-for="(group, host) in groupedLinks" :key="host">
                <div v-if="group.length > 0">
                    <?php include 'link-template.html'; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    new Vue({
        el: '#app',
        data: {
            links: []
        },
        created() {
            // Fetch links data when Vue instance is created
            fetch('fetch_links.php')
                .then(response => response.json())
                .then(data => {
                    this.links = data;
                    console.log('Data loaded:', this.links); // Debug output
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    // Display raw response if there's an error
                    fetch('fetch_links.php')
                        .then(response => response.text())
                        .then(text => {
                            console.log('Raw response:', text);
                        });
                });
        },
        computed: {
            groupedLinks() {
                // Group links by host
                const groups = {};
                
                this.links.forEach(link => {
                    // 確定主機名稱 - 優先使用 host_name，然後是 host_ip，最後是 lanhost
                    const hostName = link.host_name || link.host_ip || link.lanhost || 'Unknown';
                    
                    // 確定主機 IP - 優先使用 host_ip，然後是 lanhost
                    link.effectiveHostIp = link.host_ip || link.lanhost;
                    
                    if (!groups[hostName]) {
                        groups[hostName] = [];
                    }
                    groups[hostName].push(link);
                });
                
                return groups;
            }
        },
        methods: {
            buildLanUrl(link) {
                // 構建內部 URL
                if (!link.effectiveHostIp) return '#';
                
                let host = link.effectiveHostIp;
                let port = link.lanport ? `:${link.lanport}` : '';
                let path = link.lanpath || '';
                
                // 確保路徑開始有斜杠
                if (path && !path.startsWith('/')) {
                    path = '/' + path;
                }
                
                return `http://${host}${port}${path}`;
            },
            buildOuterUrl(link) {
                // 構建外部 URL
                if (!link.outerhost) return '#';
                
                let host = link.outerhost;
                let port = link.outerport ? `:${link.outerport}` : '';
                let path = link.outerpath || '';
                
                // 確保路徑開始有斜杠
                if (path && !path.startsWith('/')) {
                    path = '/' + path;
                }
                
                return `http://${host}${port}${path}`;
            }
        }
    });
    </script>
</body>
</html>