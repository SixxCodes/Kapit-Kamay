<?php
include_once('../../includes/mysqlconnection.php'); // connect database

if (isset($_GET['task_id'])) {
    $taskId = (int)$_GET['task_id'];

    $comments_query = $connection->prepare("
        SELECT c.*, u.FirstName, u.LastName, u.Email, u.Role, u.TrustPoints, u.ProfilePicture,
            (SELECT AVG(r.Rating) FROM taskratings r WHERE r.StudentID = u.UserID) AS AverageRating
        FROM comments c
        JOIN users u ON c.StudentID = u.UserID
        WHERE c.TaskID = ?
        ORDER BY c.DatePosted DESC
    ");
    $comments_query->bind_param("i", $taskId);
    $comments_query->execute();
    $comments_result = $comments_query->get_result();

    if ($comments_result->num_rows > 0) {
        while ($comment = $comments_result->fetch_assoc()) {
            $avgRating = $comment['AverageRating'] ? round($comment['AverageRating']) : 0;
            $profilePicture = !empty($comment['ProfilePicture']) 
                ? "../Student/" . htmlspecialchars($comment['ProfilePicture']) 
                : "../assets/default-avatar.png";
            
            // Display the comment
            echo "<div class='comment-box'>";

                // Profile picture
                echo "<div class='comment-box-profile'>";
                    echo "<img src='" . $profilePicture . "' alt='Profile Picture' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px; cursor: pointer;' onclick='openProfileModal(" . json_encode($comment) . ")'>";
                echo "</div>";

                // name
                echo "<div class='comment-box-content'>";
                    echo "<p><strong>" . htmlspecialchars($comment['FirstName'] . " " . $comment['LastName']) . "</strong></p>";

                    // Comment details
                    echo "<p><em>" . htmlspecialchars($comment['DatePosted']) . "</em></p>";
                    echo "<p>";
                    for ($i = 1; $i <= 5; $i++) {
                        echo "<span class='student-comment-rate'>" . ($i <= $avgRating ? "★" : "☆") . "</span>";
                    }
                    echo "</p>";
                    echo "<p>" . htmlspecialchars($comment['Content']) . "</p>";

                    // Check if the student is already accepted
                    if ($comment['IsAccepted'] == 1) {
                        echo "<p style='color: green; font-weight: bold;'>Student Accepted</p>";
                        echo "<button disabled style='background-color: gray; cursor: not-allowed;'>Accepted</button>";

                        // Check if the student has already been rated
                        $checkRatingQuery = $connection->prepare("
                            SELECT RatingID 
                            FROM taskratings 
                            WHERE TaskID = ? AND StudentID = ?
                        ");
                        $checkRatingQuery->bind_param("ii", $taskId, $comment['StudentID']);
                        $checkRatingQuery->execute();
                        $checkRatingResult = $checkRatingQuery->get_result();

                        if ($checkRatingResult->num_rows > 0) {
                            // Rating already exists
                            echo "<p style='color: blue; font-weight: bold;'>You have already rated this student for this task.</p>";
                        } else {
                            // Add a dropdown for rating the student
                            echo "<form action='rate_student.php' method='POST' style='margin-top: 10px;'>";
                            echo "<input type='hidden' name='student_id' value='" . $comment['StudentID'] . "'>";
                            echo "<input type='hidden' name='task_id' value='" . $taskId . "'>";
                            echo "<label for='rating'>Rate this student:</label>";
                            echo "<select name='rating' required>";
                            echo "<option value='' disabled selected>Select Rating</option>";
                            echo "<option value='1'>1 - Poor</option>";
                            echo "<option value='2'>2 - Fair</option>";
                            echo "<option value='3'>3 - Good</option>";
                            echo "<option value='4'>4 - Very Good</option>";
                            echo "<option value='5'>5 - Excellent</option>";
                            echo "</select>";
                            echo "<br><br>";
                            echo "<button type='submit' style='background-color: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;'>Submit Rating</button>";
                            echo "</form>";
                        }

                        $checkRatingQuery->close();
                    } else {
                        echo "<button class='comment-box-accept-btn' onclick='acceptComment(" . $comment['CommentID'] . ")'>Accept</button>";
                    }
                echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>No comments yet.</p>";
    }

    $comments_query->close();
}
?>