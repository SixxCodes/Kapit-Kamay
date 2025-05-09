<!-- 
    unsa gni mabuhat sa student dashboard?
    1. search job
    2. apply / comment job
-->

<!-- 
    TO-DO List:
    1. view post (done)
    2. view post details (done)
    3. comment
    4. search
 -->

 <?php
    session_start();
    include_once('../../includes/mysqlconnection.php'); // Connect to the database

    // Check if user is logged in and has the role "Student"
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Student') {
        echo "Unauthorized access.";
        exit();
    }

    // Store logged-in user's email
    $email = $_SESSION['login_email'];

    // Get Student User Info
    $stmt = $connection->prepare("SELECT UserID, FirstName, LastName, Email, Role, TrustPoints, ProfilePicture FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $studentID = $user['UserID'];
    $firstName = $user['FirstName'];
    $lastName = $user['LastName'];

    // If no match found
    if (!$studentID) {
        echo "Student user not found.";
        exit();
    }

    $taskQuery = $connection->prepare("
        SELECT t.*, u.FirstName, u.LastName, u.ProfilePicture, u.Email AS PosterEmail
        FROM tasks t
        JOIN users u ON t.CommunityID = u.UserID
        WHERE t.Status IN ('Open', 'Ongoing')
        ORDER BY t.DatePosted DESC
    ");
    $taskQuery->execute();
    $result = $taskQuery->get_result();
?>


<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dashboard</title>
        <link rel="stylesheet" href="stud_style.css">
    </head>
    <body>
        <input type="text" id="taskSearch" placeholder="Search by task title..." style="padding: 8px; width: 100%; max-width: 400px; margin-bottom: 20px;">

        <!-- ------------------------------PROFILE------------------------------ -->
        <h1>Kapit-Kamay</h1>
        <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>

        <!-- -------------------PROFILE (ICON)------------------- -->
        <!-- USER INFO MODAL -->
        <div id="userModal" class="userModal">
            <div class="userModal-content">
                <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                    <label>Change Profile Picture:</label>
                    <input type="file" name="profile_picture" accept="image/*" required>
                    <button type="submit">Upload</button>
                </form>
                
                <?php
                    $profileSrc = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : "../assets/default-avatar.png";
                ?>

                <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                    alt="Profile Picture" 
                    style="width:100px; height:100px; border-radius:50%;">

                <span class="userClose" onclick="closeUserModal()">&times;</span>
                <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                <!-- <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['UserID']); ?></p> -->
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['FirstName']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['LastName']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['Role']); ?></p>
                <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($user['TrustPoints']); ?></p>
                <a href="../logout.php">Logout</a>
            </div>
        </div>

        <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
            alt="Profile Picture" 
            id="userIcon" 
            style="width:100px; height:100px; border-radius:50%; cursor: pointer;"
            onclick="openUserModal()">

        <!-- ------------------------------VIEW POSTS------------------------------ -->
        <?php if ($result->num_rows > 0): ?>
        <?php $modalCount = 0; ?>
        <?php while ($task = $result->fetch_assoc()): 
            $taskId = $task['TaskID'];

            // Fetch comments for the current task
            $commentQuery = $connection->prepare("
                SELECT c.*, u.FirstName, u.LastName, u.ProfilePicture
                FROM comments c
                JOIN users u ON c.StudentID = u.UserID
                WHERE c.TaskID = ?
                ORDER BY c.DatePosted DESC
            ");
            $commentQuery->bind_param("i", $taskId);
            $commentQuery->execute();
            $commentResult = $commentQuery->get_result();
            ?>
            <?php $modalId = "modal_" . $modalCount++; ?>
            
            <div class="task-box" onclick="document.getElementById('<?= $modalId ?>').style.display='block'">
                <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                            alt="Profile Picture" 
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                <p class="task-title"><strong>Task Title:</strong> <?= htmlspecialchars($task['Title']) ?></p>
                <p><strong>Location Type:</strong> <?= htmlspecialchars($task['LocationType']) ?></p>
                <p><strong>Completion Date:</strong> <?= htmlspecialchars($task['CompletionDate']) ?></p>
                <p class="posted-time" data-dateposted="<?= $task['DatePosted'] ?>"></p>
                <p><strong>Price:</strong> ₱<?= number_format($task['Price'], 2) ?></p>
            </div>

            <!-- ------------------------------VIEW POSTS (MODAL, DETAILED)------------------------------ -->
            <div id="<?= $modalId ?>" class="modal">
                <div class="modal-content" style="max-height: 75vh; overflow-y: auto;">
                    <span class="close" onclick="document.getElementById('<?= $modalId ?>').style.display='none'">&times;</span>
                    <h2><?= htmlspecialchars($task['Title']) ?></h2>
                    <p><strong>Posted On:</strong> <?= date("F j, Y, g:i a", strtotime($task['DatePosted'])) ?></p>
                    <p class="posted-time" data-dateposted="<?= $task['DatePosted'] ?>"></p>
                    <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                        alt="Profile Picture" 
                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                    <p><strong>Status:</strong> <?= htmlspecialchars($task['Status']) ?></p>
                    <p><strong>Posted by:</strong> <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
                    <!-- <p><strong>Location Type:</strong> <?= htmlspecialchars($task['LocationType']) ?></p> -->
                    <p><strong>Location:</strong> <?= htmlspecialchars($task['Location']) ?></p>
                    <p><strong>Completion Date:</strong> <?= htmlspecialchars($task['CompletionDate']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($task['Category']) ?></p>
                    <p><strong>Estimated Duration:</strong> <?= htmlspecialchars($task['EstimatedDuration']) ?></p>
                    <p><strong>Price:</strong> ₱<?= number_format($task['Price'], 2) ?></p>
                    <p><strong>Task Description:</strong> <?= nl2br(htmlspecialchars($task['Description'])) ?></p>
                    <p><strong>Contact via Email:</strong> <?= htmlspecialchars($task['PosterEmail']) ?></p>
                    <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($task['Notes'])) ?></p>

                    <div style="margin-top: 20px;">
                        <h3>Comments</h3>
                        <?php if ($commentResult->num_rows > 0): ?>
                            <?php while ($comment = $commentResult->fetch_assoc()): ?>
                                <div class="comment-box" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 10px;">
                                    <?php
                                    // Fetch the poster's profile picture
                                    $posterStmt = $connection->prepare("SELECT ProfilePicture FROM users WHERE UserID = ?");
                                    $posterStmt->bind_param("i", $comment['StudentID']);
                                    $posterStmt->execute();
                                    $posterResult = $posterStmt->get_result();
                                    $posterData = $posterResult->fetch_assoc();
                                    ?>
                                    
                                    <!-- Display the profile picture -->
                                    <img src="../Student/<?= htmlspecialchars($posterData['ProfilePicture']) ?>" alt="Poster Profile Picture" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">

                                    <p><strong><?= htmlspecialchars($comment['FirstName'] . " " . $comment['LastName']) ?></strong></p>
                                    
                                    <?php
                                    // Get the average rating for this commenter (if any)
                                    $studentId = $comment['StudentID'];
                                    $ratingStmt = $connection->prepare("
                                        SELECT AVG(Rating) as avg_rating
                                        FROM taskratings
                                        WHERE TaskID = ? AND StudentID = ?
                                    ");
                                    $ratingStmt->bind_param("ii", $taskId, $studentId);
                                    $ratingStmt->execute();
                                    $ratingResult = $ratingStmt->get_result();
                                    $ratingData = $ratingResult->fetch_assoc();
                                    $avgRating = round($ratingData['avg_rating']);
                                    ?>
                                    
                                    <p>Rating:
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span style="color: gold;"><?= $i <= $avgRating ? "★" : "☆" ?></span>
                                        <?php endfor; ?>
                                    </p>
                                    
                                    <p class="posted-time" data-dateposted="<?= $comment['DatePosted'] ?>"></p>
                                    <p><?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No comments yet.</p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 30px;">
                        <h3>Leave a Comment</h3>
                        <form action="submit_comment.php" method="POST">
                            <input type="hidden" name="task_id" value="<?= $taskId ?>">
                            <textarea name="comment_content" rows="4" style="width: 100%;" required placeholder="Write your comment..."></textarea>
                            <br>
                            <button type="submit">Post Comment</button>
                        </form>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
        <?php else: ?>
            <p>No active community posts available.</p>
        <?php endif; ?>
        <script src="stud_script.js"></script>
    </body>
</html>
