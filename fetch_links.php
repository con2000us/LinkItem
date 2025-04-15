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

    // SQL query with JOIN to get host information along with links
    $sql = "SELECT l.*, h.host_name, h.host_ip
            FROM links l
            LEFT JOIN hosts h ON l.lanhost = h.host_id
            ORDER BY l.hostGroup ASC, l.linkOrder ASC";
    $result = $conn->query($sql);

    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch all data as an associative array
        $links = $result->fetch_all(MYSQLI_ASSOC);
        // Convert to object
        $linksObject = json_decode(json_encode($links));
        // Return the object
        return json_encode($linksObject);
    } else {
        return "[]";
    }

    // Close connection
    $conn->close();
}

// Helper function to fetch all hosts
function fetchHosts() {
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

    // SQL query to select all hosts
    $sql = "SELECT * FROM hosts ORDER BY host_name ASC";
    $result = $conn->query($sql);

    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch all data as an associative array
        $hosts = $result->fetch_all(MYSQLI_ASSOC);
        // Convert to object
        $hostsObject = json_decode(json_encode($hosts));
        // Return the object
        return json_encode($hostsObject);
    } else {
        return "[]";
    }

    // Close connection
    $conn->close();
}

?> 