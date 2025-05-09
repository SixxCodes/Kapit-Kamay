<?php
include_once('../../includes/mysqlconnection.php');

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
            echo "<div class='comment-box' style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
            echo "<div style='display: flex; align-items: center; margin-bottom: 10px;'>";
            echo "<img src='" . $profilePicture . "' alt='Profile Picture' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px; cursor: pointer;' onclick='openProfileModal(" . json_encode($comment) . ")'>";
            echo "<p><strong>" . htmlspecialchars($comment['FirstName'] . " " . $comment['LastName']) . "</strong></p>";
            echo "</div>";
            echo "<p><em>Posted on " . htmlspecialchars($comment['DatePosted']) . "</em></p>";
            echo "<p><strong>Rating:</strong> ";
            for ($i = 1; $i <= 5; $i++) {
                echo "<span style='color: gold;'>" . ($i <= $avgRating ? "★" : "☆") . "</span>";
            }
            echo "</p>";
            echo "<p>" . htmlspecialchars($comment['Content']) . "</p>";
            echo "<button onclick='acceptComment(" . $comment['CommentID'] . ")'>Accept</button>";
            echo "</div>";
        }
    } else {
        echo "<p>No comments yet.</p>";
    }

    $comments_query->close();
}
?>