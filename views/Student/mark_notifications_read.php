<?php
    include_once('../../includes/mysqlconnection.php'); // Connect to the database

    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check if studentID is provided
    if (!isset($data['studentID'])) {
        echo "Invalid request. Student ID is missing.";
        exit();
    }

    $studentID = (int)$data['studentID'];

    // Update the IsRead column to 1 for all unread notifications
    $update_query = $connection->prepare("
        UPDATE notifications
        SET IsRead = 1
        WHERE UserID = ? AND IsRead = 0
    ");
    $update_query->bind_param("i", $studentID);

    if ($update_query->execute()) {
        echo "Notifications marked as read.";
    } else {
        echo "Error: " . $update_query->error;
    }

    $update_query->close();
?>