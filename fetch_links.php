<?php

$config = include 'config.php';

function fetchLinks() {
    global $config;
    $servername = $config['servername'];
    $username = $config['username'];
    $password = $config['password'];
    $dbname = $config['dbname'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 檢查 hosts 表是否存在
    $checkHostsTable = "SHOW TABLES LIKE 'hosts'";
    $hostsTableExists = $conn->query($checkHostsTable)->num_rows > 0;

    // SQL query based on table existence
    if ($hostsTableExists) {
        // 使用 JOIN 查詢 (適用於新版數據庫結構)
        $sql = "SELECT l.*, h.host_name, h.host_ip 
                FROM links l 
                LEFT JOIN hosts h ON l.lanhost = h.host_id 
                ORDER BY l.linkOrder ASC";
    } else {
        // 使用簡單查詢 (適用於舊版數據庫結構)
        $sql = "SELECT *, lanhost as host_ip FROM links ORDER BY linkOrder ASC";
    }

    // Debug information
    $debug = false; // 設置為 true 顯示調試信息
    $debugInfo = [];
    
    if ($debug) {
        $debugInfo['sql'] = $sql;
        $debugInfo['hosts_table_exists'] = $hostsTableExists;
    }
    
    $result = $conn->query($sql);

    // Check for SQL errors
    if (!$result) {
        if ($debug) {
            $debugInfo['error'] = $conn->error;
            return json_encode($debugInfo);
        }
        return "[]";
    }

    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch all data as an associative array
        $links = $result->fetch_all(MYSQLI_ASSOC);

        if ($debug) {
            $debugInfo['rows_count'] = count($links);
            $debugInfo['sample_data'] = array_slice($links, 0, 1);
        }

        // 確保每個記錄都有 host_ip 屬性
        foreach ($links as &$link) {
            if (!isset($link['host_ip']) && isset($link['lanhost'])) {
                $link['host_ip'] = $link['lanhost'];
            }
        }
        
        // Convert to object and return JSON
        $linksObject = json_decode(json_encode($links));
        
        if ($debug) {
            $debugInfo['data'] = $linksObject;
            return json_encode($debugInfo);
        }
        
        return json_encode($linksObject);
    } else {
        if ($debug) {
            $debugInfo['message'] = "No results found";
            return json_encode($debugInfo);
        }
        return "[]";
    }

    // Close connection
    $conn->close();
}

?> 