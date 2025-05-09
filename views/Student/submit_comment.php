<?php
    session_start();
    include_once('../../includes/mysqlconnection.php');

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
                    // Redirect back sa previous page
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
