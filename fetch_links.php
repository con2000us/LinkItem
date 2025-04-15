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

    // SQL query to select all data from links table
    $sql = "SELECT * FROM links";
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
        return "0 results";
    }

    // Close connection
    $conn->close();
}

?> 