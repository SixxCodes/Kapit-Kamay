<?php
    include_once('../../includes/mysqlconnection.php'); // connect database

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];

        // Update IsAccepted column in the comments table to 1
        $update_query = $connection->prepare("UPDATE comments SET IsAccepted = 1 WHERE CommentID = ?");
        $update_query->bind_param("i", $commentId);

        if ($update_query->execute()) {
            // Fetch the student ID, community user details, and task name
            $comment_query = $connection->prepare("
                SELECT c.StudentID, u.FirstName, u.LastName, t.Title AS TaskName
                FROM comments c
                JOIN tasks t ON c.TaskID = t.TaskID
                JOIN users u ON t.CommunityID = u.UserID
                WHERE c.CommentID = ?
            ");
            $comment_query->bind_param("i", $commentId);
            $comment_query->execute();
            $comment_result = $comment_query->get_result();

            if ($comment_result->num_rows > 0) {
                $comment_data = $comment_result->fetch_assoc();
                $studentId = $comment_data['StudentID'];
                $communityName = $comment_data['FirstName'] . ' ' . $comment_data['LastName'];
                $taskName = $comment_data['TaskName'];

                // Insert a notification for the student
                $notification_message = "$communityName has accepted your application on the task: $taskName. See ongoing tasks to view more details.";
                $insert_notification = $connection->prepare("
                    INSERT INTO notifications (UserID, Message, IsRead, DateCreated)
                    VALUES (?, ?, 0, NOW())
                ");
                $insert_notification->bind_param("is", $studentId, $notification_message);
                $insert_notification->execute();
                $insert_notification->close();
            }

            $comment_query->close();
            echo "Comment accepted successfully and notification sent.";
        } else {
            echo "Error: " . $update_query->error;
        }

        $update_query->close();
    } else {
        echo "Invalid request.";
    }
?>