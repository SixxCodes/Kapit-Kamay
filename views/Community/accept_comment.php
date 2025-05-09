<?php
include_once('../../includes/mysqlconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $commentId = (int)$_POST['comment_id'];

    // Update the IsAccepted column to 1
    $update_query = $connection->prepare("UPDATE comments SET IsAccepted = 1 WHERE CommentID = ?");
    $update_query->bind_param("i", $commentId);

    if ($update_query->execute()) {
        echo "Comment accepted successfully.";
    } else {
        echo "Error: " . $update_query->error;
    }

    $update_query->close();
} else {
    echo "Invalid request.";
}
?>