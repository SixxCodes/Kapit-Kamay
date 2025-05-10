<?php
include_once('../../includes/mysqlconnection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = (int)$_POST['student_id'];
    $taskID = (int)$_POST['task_id'];
    $rating = (int)$_POST['rating'];
    $communityID = $_SESSION['UserID']; // Assuming the community ID is stored in the session

    // Fetch the community user ID to verify the task belongs to the community
    $taskQuery = $connection->prepare("
        SELECT CommunityID 
        FROM tasks 
        WHERE TaskID = ?
    ");
    $taskQuery->bind_param("i", $taskID);
    $taskQuery->execute();
    $taskResult = $taskQuery->get_result();

    if ($taskResult->num_rows > 0) {
        $taskData = $taskResult->fetch_assoc();

        // Check if the task belongs to the logged-in community
        
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