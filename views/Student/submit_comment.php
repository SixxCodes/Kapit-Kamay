<?php
session_start();
include_once('../../includes/mysqlconnection.php'); // connect database

// Check if user is logged in and form is submitted
if (!isset($_SESSION['login_email'])) {
    die("Access denied. Please log in first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['login_email']; // Logged-in user's email
    $taskId = $_POST['task_id'] ?? null;
    $content = trim($_POST['comment_content'] ?? '');

    // Fetch UserID based on email
    $stmt = $connection->prepare("SELECT UserID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($studentId);
        $stmt->fetch();

        if ($taskId && $content !== '') {
            // Insert the comment
            $commentStmt = $connection->prepare("
                INSERT INTO comments (TaskID, StudentID, Content, DatePosted)
                VALUES (?, ?, ?, NOW())
            ");
            $commentStmt->bind_param("iis", $taskId, $studentId, $content);

            if ($commentStmt->execute()) {
                // Fetch task details and CommunityID
                $taskQuery = $connection->prepare("SELECT Title, CommunityID FROM tasks WHERE TaskID = ?");
                $taskQuery->bind_param("i", $taskId);
                $taskQuery->execute();
                $taskResult = $taskQuery->get_result();
                $task = $taskResult->fetch_assoc();
                $taskQuery->close();

                // Fetch student's name
                $studentQuery = $connection->prepare("SELECT FirstName, LastName FROM users WHERE UserID = ?");
                $studentQuery->bind_param("i", $studentId);
                $studentQuery->execute();
                $studentResult = $studentQuery->get_result();
                $student = $studentResult->fetch_assoc();
                $studentQuery->close();

                // Create the notification message
                $message = htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']) . " commented on your task: " . htmlspecialchars($task['Title']);

                // Insert the notification
                $notificationStmt = $connection->prepare("
                    INSERT INTO notifications (UserID, Message, IsRead, DateCreated)
                    VALUES (?, ?, 0, NOW())
                ");
                $notificationStmt->bind_param("is", $task['CommunityID'], $message);
                $notificationStmt->execute();
                $notificationStmt->close();

                // Redirect back to the previous page
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error: " . $commentStmt->error;
            }
        } else {
            echo "Please enter a comment.";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>