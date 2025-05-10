<?php
    /* 
    About: pang-accept ug comment, pang-hire. 
    pag mupindot ug accept kay ang isAccepted sa database kay mahimog 1
    false = 0
    true = 1
    false by default (meaning: wla pa na-accept) 
    */
    include_once('../../includes/mysqlconnection.php'); // connect database

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];

        // Update IsAccepted column sa comments table to 1
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