<?php
session_start();
include_once('../../includes/mysqlconnection.php');

// Check if user is logged in and is a community user
if (!isset($_SESSION['login_email']) || $_SESSION['user_role'] !== 'community') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = $_POST['comment_id'] ?? null;

    if ($commentId) {
        // Update the IsAccepted column to 1 (accepted)
        $stmt = $connection->prepare("UPDATE comments SET IsAccepted = 1 WHERE CommentID = ?");
        $stmt->bind_param("i", $commentId);

        if ($stmt->execute()) {
            // Redirect back to the task details page
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid request.";
    }
}
?>
