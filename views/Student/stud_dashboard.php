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
    21. ratings sa profile
    22. task completed profile
    23. notification sa na-accept
    24. sorting date ascending and descending, highest to lowest, lowest to highest price
    25. ma-click ang profile sa community
    28. ongoing tasks
    29. ang search bar, dli mu-follow ug 1 letter
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
        <title>Student Dashboard</title>
        <link rel="stylesheet" href="stud_style.css">
    </head>
    <body>
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <input type="text" id="taskSearch" placeholder="Search by task title..." style="padding: 8px; width: 100%; max-width: 400px; margin-bottom: 20px;">

            <!-- Notification Button -->
            <div id="notif-container" style="position: relative; margin-left: 20px;">
                <button id="notificationButton" onclick="toggleNotificationModal()" style="position: relative; background: none; border: none; cursor: pointer;">
                    <!-- Notification Icon -->
                    <img src="../../assets/images/notification-bell-icon.jpg" alt="Notifications" style="width: 30px; height: 30px;">
                    <!-- Notification Red na ! -->
                    <span id="notificationBadge" style="display: none; position: absolute; top: 0; right: 0; background: red; color: white; border-radius: 50%; width: 15px; height: 15px; font-size: 12px; display: flex; align-items: center; justify-content: center;">!</span>
                </button>

                <!-- Notification Modal -->
                <div id="notificationModal" style="display: none; position: absolute; top: 40px; right: 0; background: white; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 300px; z-index: 1000;">
                    <div style="padding: 10px; max-height: 300px; overflow-y: auto;">
                        <h4>Notifications</h4>
                        <ul id="notificationList" style="list-style: none; padding: 0; margin: 0;">
                            <!-- Diri ma-add ang notifications -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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
        
        <!-- ------------------------------Ongoing Tasks------------------------------ -->
        <h3>Your Ongoing Tasks</h3>
        <!-- ------------------------------ONGOING TASKS------------------------------ -->
        <?php
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

// Check if the student has reached the limit of 2 ongoing tasks
if ($ongoingTaskCount >= 2) {
echo "<p style='color: red;'><strong>You have reached the limit of 2 ongoing tasks. Complete a task to accept new ones.</strong></p>";
}

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

        <!-- Display Completed Tasks Count -->
        <p><strong>Completed task(s):</strong> <?= $completedTasksCount ?></p>

        <?php if ($ongoingTasksResult->num_rows > 0): ?>
            <div class="ongoing-tasks">
                <?php $modalCount = 0; ?>
                <?php while ($task = $ongoingTasksResult->fetch_assoc()): ?>
                    <?php $modalId = "ongoing-task-modal_" . $modalCount++; ?>
                    <div class="ongoing-task-box" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; cursor: pointer;" onclick="document.getElementById('<?= $modalId ?>').style.display='block'">
                        <h4 class="ongoing-task-title"><?= htmlspecialchars($task['Title']) ?></h4>
                        <p><strong>Posted By:</strong> <?= htmlspecialchars($task['FirstName'] . " " . $task['LastName']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($task['Location']) ?></p>
                        <p><strong>Completion Date:</strong> <?= htmlspecialchars($task['CompletionDate']) ?></p>
                        <p><strong>Price:</strong> ₱<?= number_format($task['Price'], 2) ?></p>
                    </div>

                    <!-- Modal for Ongoing Task Details -->
                    <div id="<?= $modalId ?>" class="modal">
                        <div class="modal-content" style="max-height: 75vh; overflow-y: auto;">
                            <span class="close" onclick="document.getElementById('<?= $modalId ?>').style.display='none'">&times;</span>
                            <h2><?= htmlspecialchars($task['Title']) ?></h2>
                            <p><strong>Posted On:</strong> <?= date("F j, Y, g:i a", strtotime($task['DatePosted'])) ?></p>
                            <p><strong>Posted:</strong> <?= (new DateTime($task['DatePosted']))->diff(new DateTime())->days ?> day(s) ago</p>
                            <img src="../Community/<?= htmlspecialchars($task['ProfilePicture']) ?>" 
                                alt="Profile Picture" 
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">
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
            
        <!-- ------------------------------VIEW POSTS------------------------------ -->
        <h3>All Tasks</h3>
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
                <p class="task-title"><strong><?= htmlspecialchars($task['Title']) ?></strong></p>
                <p><?= htmlspecialchars($task['LocationType']) ?></p>
                <p><?= htmlspecialchars($task['CompletionDate']) ?></p>
                <p class="posted-time" data-dateposted="<?= $task['DatePosted'] ?>"></p>
                <p><strong>₱<?= number_format($task['Price'], 2) ?></strong> </p>
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

                    <!-- ------------------------------COMMENT SECTION------------------------------ -->
                    <!-- ------------------------------COMMENT SECTION------------------------------ -->
<div style="margin-top: 20px;">
    <h3>Comments</h3>
    <?php if ($commentResult->num_rows > 0): ?>
        <?php while ($comment = $commentResult->fetch_assoc()): ?>
            <div class="comment-box" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 10px;">
                <?php
                // Fetch task poster's profile picture
                $posterStmt = $connection->prepare("SELECT ProfilePicture FROM users WHERE UserID = ?");
                $posterStmt->bind_param("i", $comment['StudentID']);
                $posterStmt->execute();
                $posterResult = $posterStmt->get_result();
                $posterData = $posterResult->fetch_assoc();
                ?>
                
                <!-- Display profile picture of the student who commented -->
                <img src="../Student/<?= htmlspecialchars($posterData['ProfilePicture']) ?>" alt="Poster Profile Picture" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">

                <p><strong><?= htmlspecialchars($comment['FirstName'] . " " . $comment['LastName']) ?></strong></p>
                
                <?php
                    // Get rating for this commenter (if available)
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

<!-- ------------------------------LEAVE A COMMENT SECTION------------------------------ -->
<div style="margin-top: 30px;">
    <h3>Leave a Comment</h3>
    <?php if ($ongoingTaskCount >= 2): ?>
        <p style="color: red;"><strong>You cannot leave a comment because you already have 2 ongoing tasks. Complete a task to comment on new ones.</strong></p>
    <?php else: ?>
        <form action="submit_comment.php" method="POST">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <textarea name="comment_content" rows="4" style="width: 100%;" required placeholder="Write your comment..."></textarea>
            <br>
            <button type="submit">Post Comment</button>
        </form>
    <?php endif; ?>
</div>

                </div>
            </div>
        <?php endwhile; ?>
        <?php else: ?>
            <p>No active community posts available.</p>
        <?php endif; ?>
        <script src="stud_script.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
    const notifications = <?php echo json_encode($notifications); ?>;
    const notificationBadge = document.getElementById("notificationBadge");
    const notificationList = document.getElementById("notificationList");
    const notificationModal = document.getElementById("notificationModal");

    // Check if there are new notifications
    if (notifications.length > 0) {
        const notificationsViewed = localStorage.getItem("notificationsViewed");

        // Show the red badge only if notifications haven't been viewed
        if (!notificationsViewed || notificationsViewed !== "true") {
            notificationBadge.style.display = "flex";
        }

        // Populate the notification list
        notifications.forEach(notification => {
            const listItem = document.createElement("li");
            listItem.style.padding = "10px 0";
            listItem.style.borderBottom = "1px solid #ccc";
            listItem.innerHTML = `
                <p><strong>Task Title:</strong> ${notification.Title}</p>
                <button onclick="showNotificationDetails(${JSON.stringify(notification).replace(/"/g, '&quot;')})" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">View Details</button>
            `;
            notificationList.appendChild(listItem);
        });
    }
});

// Toggle the notification modal
function toggleNotificationModal() {
    const modal = document.getElementById("notificationModal");
    modal.style.display = modal.style.display === "block" ? "none" : "block";

    // Hide the red badge when the modal is opened
    if (modal.style.display === "block") {
        document.getElementById("notificationBadge").style.display = "none";

        // Save the state in localStorage
        localStorage.setItem("notificationsViewed", "true");
    }
}

// Show task details in a new modal
function showNotificationDetails(notification) {
    // Create a new modal for task details
    const modalId = "taskDetailsModal";
    let modal = document.getElementById(modalId);

    // If the modal doesn't exist, create it
    if (!modal) {
        modal = document.createElement("div");
        modal.id = modalId;
        modal.className = "modal";
        modal.style.display = "block";
        modal.style.position = "fixed";
        modal.style.top = "50%";
        modal.style.left = "50%";
        modal.style.transform = "translate(-50%, -50%)";
        modal.style.background = "white";
        modal.style.border = "1px solid #ccc";
        modal.style.borderRadius = "5px";
        modal.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
        modal.style.width = "400px";
        modal.style.zIndex = "1000";
        modal.style.padding = "20px";
        modal.style.maxHeight = "80vh";
        modal.style.overflowY = "auto";

        document.body.appendChild(modal);
    }

    // Populate the modal with task details
    modal.innerHTML = `
        <h4>${notification.Title}</h4>
        <p><strong>Posted By:</strong> ${notification.FirstName} ${notification.LastName}</p>
        <p><strong>Location:</strong> ${notification.Location}</p>
        <p><strong>Email:</strong> ${notification.Email}</p>
        <p><strong>Description:</strong> ${notification.Description}</p>
        <p><strong>Notes:</strong> ${notification.Notes}</p>
        <p>View your ongoing tasks to see full details.</p>
        <button onclick="closeTaskDetailsModal('${modalId}')" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">Close</button>
    `;

    // Show the modal
    modal.style.display = "block";
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