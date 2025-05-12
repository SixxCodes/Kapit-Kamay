<?php
include_once('../../includes/mysqlconnection.php'); // Correct path to the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userID'])) {
    $userID = (int)$_POST['userID'];

    // Debugging: Log the received userID
    error_log("Received userID: $userID");

    // Update all unread notifications for the user
    $stmt = $connection->prepare("UPDATE notifications SET IsRead = 1 WHERE UserID = ? AND IsRead = 0");
    $stmt->bind_param("i", $userID);

    if ($stmt->execute()) {
        echo "Notifications marked as read.";
    } else {
        echo "Error: " . $stmt->error;
        error_log("Error: " . $stmt->error); // Log the error
    }

    $stmt->close();
} else {
    echo "Invalid request.";
    error_log("Invalid request: " . json_encode($_POST)); // Log invalid requests
}
?>