<?php
    include_once('../../includes/mysqlconnection.php');
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // fetch datas needed para sa students para ma-rate
        $studentID = (int)$_POST['student_id'];
        $taskID = (int)$_POST['task_id'];
        $rating = (int)$_POST['rating'];
        $communityID = $_SESSION['UserID'];

        // Fetch the community user ID to verify the task belongs to the community
        $taskQuery = $connection->prepare("
            SELECT CommunityID, Title 
            FROM tasks 
            WHERE TaskID = ?
        ");
        $taskQuery->bind_param("i", $taskID);
        $taskQuery->execute();
        $taskResult = $taskQuery->get_result();

        if ($taskResult->num_rows > 0) {
            $taskData = $taskResult->fetch_assoc();
            $taskTitle = $taskData['Title'];

            // Check if a rating already exists for this task and student
            $checkRatingQuery = $connection->prepare("
                SELECT RatingID 
                FROM taskratings 
                WHERE TaskID = ? AND StudentID = ?
            ");
            $checkRatingQuery->bind_param("ii", $taskID, $studentID);
            $checkRatingQuery->execute();
            $checkRatingResult = $checkRatingQuery->get_result();

            if ($checkRatingResult->num_rows > 0) {
                // Rating already exists
                echo "<script>alert('You have already rated this student for this task.'); window.location.href = 'comm_dashboard.php';</script>";
            } else {
                // Insert the rating into the database
                $rateQuery = $connection->prepare("
                    INSERT INTO taskratings (TaskID, StudentID, Rating, RatedAt)
                    VALUES (?, ?, ?, NOW())
                ");
                $rateQuery->bind_param("iii", $taskID, $studentID, $rating);

                if ($rateQuery->execute()) {
                    // -------------------------------TRUST POINTS-------------------------------
                    // Calculate trust points based on the rating
                    $trustPoints = 0;
                    if ($rating == 5) {
                        $trustPoints = 10;
                    } elseif ($rating == 4) {
                        $trustPoints = 8;
                    } elseif ($rating == 3) {
                        $trustPoints = 5;
                    }

                    // Update the student's trust points in the users table
                    if ($trustPoints > 0) {
                        $updateTrustPointsQuery = $connection->prepare("
                            UPDATE users 
                            SET TrustPoints = TrustPoints + ? 
                            WHERE UserID = ?
                        ");
                        $updateTrustPointsQuery->bind_param("ii", $trustPoints, $studentID);
                        $updateTrustPointsQuery->execute();
                        $updateTrustPointsQuery->close();
                    }

                    // -------------------------------SEND NOTIFICATION-------------------------------
                    // Fetch the community poster's name
                    $communityQuery = $connection->prepare("
                        SELECT CONCAT(FirstName, ' ', LastName) AS CommunityName 
                        FROM users 
                        WHERE UserID = ?
                    ");
                    $communityQuery->bind_param("i", $communityID);
                    $communityQuery->execute();
                    $communityResult = $communityQuery->get_result();
                    $communityName = $communityResult->fetch_assoc()['CommunityName'];

                    // Insert a notification for the student
                    $notificationMessage = "Congratulations for completing the task: $taskTitle. You received $trustPoints trust points.";
                    $insertNotificationQuery = $connection->prepare("
                        INSERT INTO notifications (UserID, Message, IsRead, DateCreated)
                        VALUES (?, ?, 0, NOW())
                    ");
                    $insertNotificationQuery->bind_param("is", $studentID, $notificationMessage);
                    $insertNotificationQuery->execute();
                    $insertNotificationQuery->close();

                    echo "<script>alert('Rating submitted successfully!'); window.location.href = 'comm_dashboard.php';</script>";
                } else {
                    echo "<script>alert('Failed to submit rating. Please try again.'); window.location.href = 'comm_dashboard.php';</script>";
                }

                $rateQuery->close();
            }

            $checkRatingQuery->close();
        } else {
            echo "<script>alert('Task not found.'); window.location.href = 'comm_dashboard.php';</script>";
        }

        $taskQuery->close();
    }
?>