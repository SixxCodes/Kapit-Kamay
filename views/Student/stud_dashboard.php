<!-- 
    unsa gni mabuhat sa student dashboard?
    1. search job (done)
    2. apply / comment job (done)
-->

<!-- 
    TO-DO List:
    1. view post (done)
    2. view post details (done)
    3. comment (done)
    4. search (done)

    5. ihan-ay ang css

    Student Dashboard
    21. ratings sa profile (done)
    22. task completed profile (done)
    23. notification sa na-accept (done)
    24. sorting date ascending and descending, highest to lowest, lowest to highest price
    25. ma-click ang profile sa community (done)
    28. ongoing tasks (done)
    29. ang search bar, dli mu-follow ug 1 letter (done)
 -->

 <?php
    session_start();
    include_once('../../includes/mysqlconnection.php'); // Connect to the database

    // i-check if user logged in ug ug student ba iyahang role
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Student') {
        echo "Unauthorized access.";
        exit();
    }

    // I-store ang gi-log in na email
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
        <title>Student Dashboard - <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></title>
        <link rel="stylesheet" href="css/stud_style.css">
        <link rel="stylesheet" href="css/mobile_style.css">
        <link rel="shortcut icon" href="../../assets/images/logo1.png" type="image/x-icon">
    </head>
    <body>
        
        <!-- ------------------------------HEADER------------------------------ -->
        <header class="main-header">
            <div class="header-main-logo">
                <img src="../../assets/images/logo1.png" alt="Logo Sample">
                <h1>Kapit-Kamay</h1>
            </div>
            <div class="header-main-role-dashboard">
                <h2>Student Dashboard</h2>
            </div>
            <div class="header-main-tools">
                <!-- ------------------------------NOTIFICATIONS------------------------------ -->
                <!-- Notification Button -->
                <div class="header-main-notifications">
                    <img src="../../assets/images/notification-bell-icon.jpg" alt="Notifications" onclick="toggleNotifications()">
                </div>
                <div class="profile-icon-mobile">
                    <?php
                        $profileSrc = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : "../assets/default-avatar.png";
                    ?>
                    <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                        alt="Profile Picture" 
                        class="user-icon"
                        id="userIcon" 
                        onclick="openUserModal()">
                </div>
            </div>
        </header>

        <?php
            // Fetch notifications for the logged-in student
            $notificationQuery = $connection->prepare("
                SELECT t.Title, t.Location, t.Description, t.Notes, u.FirstName, u.LastName, u.Email
                FROM tasks t
                JOIN comments c ON t.TaskID = c.TaskID
                JOIN users u ON t.CommunityID = u.UserID
                WHERE c.StudentID = ? AND c.IsAccepted = 1
                ORDER BY c.DatePosted DESC
            ");
            $notificationQuery->bind_param("i", $studentID);
            $notificationQuery->execute();
            $notificationResult = $notificationQuery->get_result();

            $notifications = [];
            while ($notification = $notificationResult->fetch_assoc()) {
                $notifications[] = $notification;
            }
        ?>

        <div class="body-container">
            <div class="body-content">
                <div class="body-content-user">
                    <div class="profile-summary-pc">
                        <!-- ------------------------------PROFILE------------------------------ -->
                        <img class="profile-picture" src="<?php echo htmlspecialchars($profileSrc); ?>" 
                            alt="Profile Picture" 
                            id="userIcon" 
                            onclick="openUserModal()">
                        <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>

                        

                        <!-- ------------------------------TASK DETAILS------------------------------ -->
                        <?php
                            // ------------------------------ONGOING TASKS PHP------------------------------ 
                            // Fetch tasks where the student has been accepted and the status is Open or Ongoing
                            $ongoingTasksQuery = $connection->prepare("
                            SELECT t.*, u.FirstName, u.LastName, u.ProfilePicture, u.Email AS PosterEmail
                            FROM tasks t
                            JOIN comments c ON t.TaskID = c.TaskID
                            JOIN users u ON t.CommunityID = u.UserID
                            WHERE c.StudentID = ? AND c.IsAccepted = 1 AND t.Status IN ('Open', 'Ongoing')
                            ORDER BY t.DatePosted DESC
                            ");
                            $ongoingTasksQuery->bind_param("i", $studentID);
                            $ongoingTasksQuery->execute();
                            $ongoingTasksResult = $ongoingTasksQuery->get_result();

                            // Count the number of ongoing tasks
                            $ongoingTaskCount = $ongoingTasksResult->num_rows;

                            // ------------------------------COMPLETED TASKS PHP------------------------------ 
                            // Count tasks where the status is Closed
                            $completedTasksQuery = $connection->prepare("
                                SELECT COUNT(*) AS CompletedCount
                                FROM tasks t
                                JOIN comments c ON t.TaskID = c.TaskID
                                WHERE c.StudentID = ? AND c.IsAccepted = 1 AND t.Status = 'Closed'
                            ");
                            $completedTasksQuery->bind_param("i", $studentID);
                            $completedTasksQuery->execute();
                            $completedTasksResult = $completedTasksQuery->get_result();
                            $completedTasksCount = $completedTasksResult->fetch_assoc()['CompletedCount'];
                        ?>

                        <!-- ------------------------------STUDENT PROFILE SUMMARY------------------------------  -->
                        <!-- Display Trust Points, Rating, and Completed Tasks -->
                        <div class="student-profile-summary-container">
                            <p><strong>Trust Points</strong><br/>
                            <?= htmlspecialchars($user['TrustPoints']) ?> points</p>
                    
                            <?php
                            // Fetch the average rating for the student
                            $ratingQuery = $connection->prepare("
                                SELECT AVG(Rating) AS avg_rating 
                                FROM taskratings 
                                WHERE StudentID = ?
                            ");
                            $ratingQuery->bind_param("i", $studentID);
                            $ratingQuery->execute();
                            $ratingResult = $ratingQuery->get_result();
                            $ratingData = $ratingResult->fetch_assoc();
                            $avgRating = round($ratingData['avg_rating']);
                            $ratingQuery->close();
                            ?>
                            
                            <p class="student-profile-summary-rating"><strong>Rating</strong><br/>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span><?= $i <= $avgRating ? "★" : "☆" ?></span>
                                <?php endfor; ?>
                            </p>
                            
                            <!-- Display Completed Tasks Count -->
                            <p><strong>Completed Tasks</strong><br/>
                            <?= $completedTasksCount ?> Tasks</p>
                        </div>
                        
                    </div>

                    <!-- -------------------PROFILE (ICON)------------------- -->
                    <!-- USER INFO MODAL -->
                    <div id="userModal" class="userModal">
                        <div class="userModal-content">
                            <div class="user-modal-content-profile-picture">
                                <?php
                                    $profileSrc = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : "../assets/default-avatar.png";
                                ?>

                                <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                                    alt="Profile Picture">

                                <span class="userClose" onclick="closeUserModal()">&times;</span>
                                <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                                
                                <!-- <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                                    <label>Change Profile Picture:</label>
                                    <input type="file" name="profile_picture" accept="image/*" required>
                                    <button type="submit">Upload</button>
                                </form> -->
                                <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                                    <!-- Hidden file input -->
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required class="file-input">

                                    <!-- Custom label as pen icon -->
                                    <label for="profile_picture" class="upload-label">🖊️ <small>Select Picture</small></label>

                                    <!-- Styled submit button -->
                                    <button type="submit" class="submit-button">✔️ <small>Upload Picture</small></button>
                                </form>
                            </div>
                            
                            <!-- <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['UserID']); ?></p> -->
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['FirstName']); ?></p>
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['LastName']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['Role']); ?></p>
                            <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($user['TrustPoints']); ?></p>
                            <a href="../logout.php">Logout</a>
                        </div>
                    </div>
                </div>
                    
                <div class="body-content-task">
                    <!-- ------------------------------SEARCH------------------------------ -->
                    <div class="search-bar-container">
                        <input type="text" class="search-bar" id="taskSearch" placeholder="🔍 Search for tasks...">
                        <!-- <input type="text" class="search-bar" id="taskSearchBar" placeholder="🔍 Search your tasks..." onkeyup="filterMyTasks()"> -->
                    </div>

                    <!-- ------------------------------DISPLAY ONGOING TASKS------------------------------  -->
                    <h2>Your Ongoing Tasks</h2>

                    <?php
                        if ($ongoingTaskCount >= 2) {
                            echo "<p class='ongoing-task-limit-warning'><strong>You have reached the limit of 2 ongoing tasks. Complete a task to accept new ones.</strong></p>";
                        }
                    ?>
                    
                    <div>
                        <?php if ($ongoingTasksResult->num_rows > 0): ?>
                            <div class="ongoing-tasks ongoing-task-grid-container">
                                <?php $modalCount = 0; ?>
                                <?php while ($task = $ongoingTasksResult->fetch_assoc()): ?>
                                    <div class="ongoing-task-square">
                                        <?php $modalId = "ongoing-task-modal_" . $modalCount++; ?>
                                        <div class="ongoing-task-box " onclick="document.getElementById('<?= $modalId ?>').style.display='block'">
                                            
                                            <div class="ongoing-task-header">
                                                <h3 class="ongoing-task-title"><?= htmlspecialchars($task['Title']) ?></h3>
                                            </div>

                                            <div class='ongoing-task-content'>

                                                <div class='ongoing-task-profile-pic'>
                                                    <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                                    alt="Profile Picture">
                                                </div>

                                                <div class='ongoing-task-information-container'>
                                                    <div class='ongoing-task-owner'>
                                                        <p>Posted by: <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
                                                    </div>
                                                    <div class='ongoing-task-detail-container'>
                                                        <div class='ongoing-task-detail'>
                                                            <p>📍<?= htmlspecialchars($task['LocationType']) ?></p>
                                                        </div>
                                                        <div class='ongoing-task-detail'>
                                                            <p>📅<?= htmlspecialchars($task['CompletionDate']) ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class='ongoing-task-price'>
                                                    <p><strong>₱<?= number_format($task['Price']) ?></strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    </div>      
                                    <!-- Modal for Ongoing Task Details -->
                                    <div id="<?= $modalId ?>" class="modal">
                                        <div class="modal-content modal-content-ongoing-tasks">
                                            <span class="close" onclick="document.getElementById('<?= $modalId ?>').style.display='none'">&times;</span>
                                            <h2><?= htmlspecialchars($task['Title']) ?></h2>
                                            <p><strong>Posted On:</strong> <?= date("F j, Y, g:i a", strtotime($task['DatePosted'])) ?></p>
                                            <p><strong>Posted:</strong> <?= (new DateTime($task['DatePosted']))->diff(new DateTime())->days ?> day(s) ago</p>
                                            <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                                alt="Profile Picture">
                                            <p><strong>Status:</strong> <?= htmlspecialchars($task['Status']) ?></p>
                                            <p><strong>Posted by:</strong> <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($task['Location']) ?></p>
                                            <p><strong>Completion Date:</strong> <?= htmlspecialchars($task['CompletionDate']) ?></p>
                                            <p><strong>Category:</strong> <?= htmlspecialchars($task['Category']) ?></p>
                                            <p><strong>Estimated Duration:</strong> <?= htmlspecialchars($task['EstimatedDuration']) ?></p>
                                            <p><strong>Price:</strong> ₱<?= number_format($task['Price'], 2) ?></p>
                                            <p><strong>Task Description:</strong> <?= nl2br(htmlspecialchars($task['Description'])) ?></p>
                                            <p><strong>Contact via Email:</strong> <?= htmlspecialchars($task['PosterEmail']) ?></p>
                                            <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($task['Notes'])) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>No ongoing tasks yet.</p>
                        <?php endif; ?>    
                    </div>    

                    <!-- ------------------------------All POSTS------------------------------ -->
                    <h2>All Tasks</h2>
                    <div class="all-post-grid-container">
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
                        <div class="task-box all-post-square" onclick="document.getElementById('<?= $modalId ?>').style.display='block'">
                            <div class="ongoing-task-header">
                                <p class="task-title"><strong><?= htmlspecialchars($task['Title']) ?></strong></p>
                            </div>
                            <div class='ongoing-task-content'>
                                <div class='ongoing-task-profile-pic'>
                                    <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                            alt="Profile Picture">
                                </div>
                                <div class='ongoing-task-information-container'>
                                    <div class='ongoing-task-owner'>
                                        <p>Posted by: <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
                                    </div>
                                    <div class='ongoing-task-detail-container'>
                                        <div class='ongoing-task-detail'>
                                            <p>📍<?= htmlspecialchars($task['LocationType']) ?></p>
                                        </div>
                                        <div class='ongoing-task-detail'>
                                            <p>📅<?= htmlspecialchars($task['CompletionDate']) ?></p>
                                        </div>
                                    </div>
                                    <!-- <p class="posted-time" data-dateposted="<?= $task['DatePosted'] ?>"></p> -->
                                </div>
                                <div class='ongoing-task-price'>
                                    <p><strong>₱<?= number_format($task['Price']) ?></strong> </p>
                                </div>
                            </div>
                        </div>
                    

                        <!-- ------------------------------VIEW POSTS (MODAL, DETAILED)------------------------------ -->
                        <div id="<?= $modalId ?>" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="document.getElementById('<?= $modalId ?>').style.display='none'">&times;</span>
                                <h2><?= htmlspecialchars($task['Title']) ?></h2>
                                <p><strong>Posted On:</strong> <?= date("F j, Y, g:i a", strtotime($task['DatePosted'])) ?></p>
                                <p class="posted-time" data-dateposted="<?= $task['DatePosted'] ?>"></p>

                                <!-- <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                    alt="Profile Picture" 
                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"> 
                                -->

                                <img class="view-post-modal-community-profile-picture" src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                    alt="Profile Picture" 
                                    onclick="openCommunityProfileModal('<?= htmlspecialchars($task['ProfilePicture']) ?>', '<?= htmlspecialchars($task['FirstName'] . ' ' . $task['LastName']) ?>', '<?= htmlspecialchars($task['PosterEmail']) ?>', 'Community')">
                                <p><strong>Posted by:</strong> <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
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

                                <!-- ------------------------------LEAVE A COMMENT------------------------------ -->
                                <div class="leave-a-comment-container">
                                    <h3>Leave a Comment</h3>
                                    <?php if ($ongoingTaskCount >= 2): ?>
                                        <p><strong>You cannot leave a comment because you already have 2 ongoing tasks. Complete a task to comment on new ones.</strong></p>
                                    <?php else: ?>
                                        <form action="submit_comment.php" method="POST">
                                            <input type="hidden" name="task_id" value="<?= $taskId ?>">
                                            <textarea name="comment_content" rows="4" required placeholder="Write your comment..."></textarea>
                                            <br>
                                            <button type="submit">Post Comment</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <!-- ------------------------------COMMENT SECTION------------------------------ -->
                                <div class="view-posts-comments-container">
                                    <h3>Comments</h3>
                                    <?php if ($commentResult->num_rows > 0): ?>
                                        <?php while ($comment = $commentResult->fetch_assoc()): ?>
                                            <div class="comment-box">
                                                <?php
                                                // Fetch task poster's profile picture
                                                $posterStmt = $connection->prepare("SELECT ProfilePicture FROM users WHERE UserID = ?");
                                                $posterStmt->bind_param("i", $comment['StudentID']);
                                                $posterStmt->execute();
                                                $posterResult = $posterStmt->get_result();
                                                $posterData = $posterResult->fetch_assoc();
                                                ?>
                                                
                                                <!-- Display profile picture of the student who commented -->
                                                <img src="../Student/<?= htmlspecialchars($posterData['ProfilePicture']) ?>" alt="Poster Profile Picture">

                                                <p><strong><?= htmlspecialchars($comment['FirstName'] . " " . $comment['LastName']) ?></strong></p>
                                                
                                                <?php
                                                    $ratingStmt = $connection->prepare("
                                                        SELECT AVG(Rating) as avg_rating
                                                        FROM taskratings
                                                        WHERE StudentID = ?
                                                    ");
                                                    $ratingStmt->bind_param("i", $comment['StudentID']);
                                                    $ratingStmt->execute();
                                                    $ratingResult = $ratingStmt->get_result();
                                                    $ratingData = $ratingResult->fetch_assoc();
                                                    $avgRating = isset($ratingData['avg_rating']) ? round($ratingData['avg_rating']) : 0; // Default to 0 if no ratings
                                                ?>
                                                
                                                <p class="student-comment-rating">Rating:
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span><?= $i <= $avgRating ? "★" : "☆" ?></span>
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
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <p>No active community posts available.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Community Profile Modal -->
                    <div id="communityProfileModal" class="modal community-profile-modal-click-on-task">
                        <span class="close" onclick="closeCommunityProfileModal()">&times;</span>
                        <div class="community-profile-modal-click-on-task-content">
                            <img id="communityProfilePicture" src="" alt="Profile Picture">
                            <h3 id="communityFullName"></h3>
                            <p><strong>Email:</strong> <span id="communityEmail"></span></p>
                            <p><strong>Role:</strong> <span id="communityRole"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="stud_script.js"></script>
        <script>
            function openCommunityProfileModal(profilePicture, fullName, email, role) {
                // Populate the modal with the community poster's details
                document.getElementById('communityProfilePicture').src = '../Community/' + profilePicture;
                document.getElementById('communityFullName').textContent = fullName;
                document.getElementById('communityEmail').textContent = email;
                document.getElementById('communityRole').textContent = role;

                // Show the modal
                document.getElementById('communityProfileModal').style.display = 'block';
            }

            function closeCommunityProfileModal() {
                // Hide the modal
                document.getElementById('communityProfileModal').style.display = 'none';
            }

            // Close the task details modal
            function closeTaskDetailsModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    </body>
</html>