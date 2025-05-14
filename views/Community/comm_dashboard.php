<!-- 
    unsa gni mabuhat sa community dashboard?
    1. make post (done)
    2. view, edit, delete post (done)
    3. view comments, profile (done)
    4. hire (done)
    5. rate (done)
-->

<!-- 
    TO-DO List:
    1. Buhat create task modal (done)
    2. buhat view task modal (mark as done, edit, delete, hire)
    3. butang previous posts (done)
    4. butang sa profile (active posts, previous posts, total task posted) (done)

    Community:
    3. iuban sa dropdown ang edit ug delete task 1
    4. estimated duration: options lahion 1
    8. total comments sa active post
    11. notifications sa mga nag-apply
    15. filter category
    18. sorting date ascending and descending, highest to lowest, lowest to highest price
 -->

 <?php
    session_start();
    include_once('../../includes/mysqlconnection.php'); // connect ni sa database

    // check if user: logged in, role: Community
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    //ang gi-login nga email kay mao na sya ang ma-store sa $email variable
    $email = $_SESSION['login_email'];

    // Get Community User ID 
    $stmt = $connection->prepare("SELECT UserID, FirstName, LastName, Email, Role, TrustPoints, ProfilePicture FROM users WHERE Email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $communityID = $user['UserID'];
    $firstName = $user['FirstName'];
    $lastName = $user['LastName'];

    // pag wlay match
    if (!$communityID) {
        echo "Community user not found.";
        exit();
    }

?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>'s Dashboard </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/comm_style.css">
        <link rel="stylesheet" href="css/mobile_style.css">
        <link rel="shortcut icon" href="../../assets/images/logo1.png" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- <link rel="stylesheet" href="../css/main.css"> -->
    </head>
    <body> 

        <header class="main-header">
            <div class="header-main-logo">
                <img src="../../assets/images/logo1.png" alt="Logo Sample">
                <h1>Kapit-Kamay</h1>
            </div>
            <div class="header-main-role-dashboard">
                <h2>Community Dashboard</h2>
            </div>
            <div class="header-main-tools">
                <div class="header-main-notifications">
                    <img src="../../assets/images/notif-btn-green.png" alt="Notifications" onclick="toggleNotifications()" data-community-id="<?php echo $communityID; ?>">
                    <?php
                        // Fetch unread notifications count
                        $stmtUnread = $connection->prepare("SELECT COUNT(*) AS UnreadCount FROM notifications WHERE UserID = ? AND IsRead = 0");
                        $stmtUnread->bind_param("i", $communityID);
                        $stmtUnread->execute();
                        $stmtUnread->bind_result($unreadCount);
                        $stmtUnread->fetch();
                        $stmtUnread->close();
                        
                        if ($unreadCount > 0) {
                            echo "<span class='notification-badge'>$unreadCount</span>";
                        }
                    ?>
                </div>
                <div class="profile-icon-mobile">
                    <?php
                        $profileSrc = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : "../../assets/images/default-profile-pic.jpg";
                    ?>
                    <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                        alt="Profile Picture" 
                        class="user-icon"
                        id="userIcon" 
                        onclick="openUserModal()">
                </div>
            </div>
        </header>

        <div id="notificationsDropdown" class="notifications-dropdown" style="display: none;">
            <h3>Notifications</h3>
            <?php
                // Fetch notifications for the community user
                $stmtNotifications = $connection->prepare("SELECT Message, IsRead, DateCreated FROM notifications WHERE UserID = ? ORDER BY DateCreated DESC");
                $stmtNotifications->bind_param("i", $communityID);
                $stmtNotifications->execute();
                $resultNotifications = $stmtNotifications->get_result();

                if ($resultNotifications->num_rows > 0) {
                    while ($notification = $resultNotifications->fetch_assoc()) {
                        $isReadClass = $notification['IsRead'] ? 'read' : 'unread';
                        echo "<div class='notification-item $isReadClass'>";
                        echo "<p>" . htmlspecialchars($notification['Message']) . "</p>";
                        echo "<small>" . htmlspecialchars($notification['DateCreated']) . "</small>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No notifications yet.</p>";
                }
                $stmtNotifications->close();
            ?>
        </div>
        
        
        <!-- ------------------------------PROFILE------------------------------ -->
        <!-- PROFILE ICON -->
        <div class="body-container">
            <div class="body-content">
                <div class="body-content-user">
                    <div class="profile-summary-pc">
                        <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                            alt="Profile Picture" 
                            class="user-icon"
                            id="userIcon" 
                            onclick="openUserModal()">
                        
                        <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>

                        <!-- -------------------COUNT TASKS------------------- -->
                        <?php
                            // Step 1: Count Active Tasks 
                            $stmtActive = $connection->prepare("SELECT COUNT(*) FROM tasks WHERE CommunityID = ? AND Status = 'Open'");
                            $stmtActive->bind_param("i", $communityID);
                            $stmtActive->execute();
                            $stmtActive->bind_result($activeTasks);
                            $stmtActive->fetch();
                            $stmtActive->close();

                            // Step 2: Count Previous Tasks
                            $stmtPrevious = $connection->prepare("SELECT COUNT(*) FROM tasks WHERE CommunityID = ? AND Status = 'Closed'");
                            $stmtPrevious->bind_param("i", $communityID);
                            $stmtPrevious->execute();
                            $stmtPrevious->bind_result($previousTasks);
                            $stmtPrevious->fetch();
                            $stmtPrevious->close();

                            // Step 3: total
                            $totalTasks = $activeTasks + $previousTasks;
                        ?>

                        <!-- Display counts -->
                        <div class="display-counts-container">
                            <p><strong>Active Tasks</strong></p>
                            <p><?php echo $activeTasks; ?> Tasks</p>
                            <p><strong>Previous Tasks</strong></p>
                            <p><?php echo $previousTasks; ?> Tasks</p>
                            <p><strong>Total Tasks Posted</strong></p>
                            <p><?php echo $totalTasks; ?> Tasks</p>
                        </div>
                    </div>
                </div>

                <!-- ------------------------------PROFILE MODAL------------------------------ -->
                <div id="userModal" class="userModal">
                    <div class="userModal-content">
                        <div class="user-modal-content-profile-picture">
                            <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                            <?php
                                $profileSrc = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : "../../assets/images/default-profile-pic.jpg";
                            ?>

                            <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                                alt="Profile Picture" 
                            >

                            <!-- <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                                <input type="file" name="profile_picture" accept="image/*" required>
                                <button type="submit">✔️</button>
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

                        <span class="userClose" onclick="closeUserModal()">&times;</span>
                        <!-- <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['UserID']); ?></p> -->
                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['FirstName']); ?></p>
                        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['LastName']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['Role']); ?></p>
                        <!-- <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($user['TrustPoints']); ?></p> -->
                        
                        <!-- ------------------------------LOG OUT------------------------------ -->
                        <br/>
                        <a class="logout-btn" href="../logout.php">Logout</a>
                    </div>
                </div>

                <div class="body-content-task">
                    <!-- ------------------------------SEARCH------------------------------ -->
                    <div class="search-bar-container">
                        <input type="text" class="search-bar" id="taskSearchBar" placeholder="🔍 Search your tasks..." onkeyup="filterMyTasks()">
                    </div>
                    <!-- ------------------------------CREATE POST------------------------------ -->
                    <h2>Active Posts</h2>

                    <!-- Create Task -->
                    <div class="create-task-modal" id="createTaskModal">
                        <form action="" method="POST">
                            <div class="create-task-modal-content">
                                <div class="create-task-header-container">
                                    <div class="create-task-header-item-1">
                                        <input type="text" placeholder="Enter task title here" name="title" maxlength="30" required><br>
                                    </div>
                                    <div class="create-task-header-item-2">
                                        <input type="submit" name="create_task" value="Done">
                                        <button type="button" onclick="document.getElementById('createTaskModal').style.display='none'">Cancel</button>
                                    </div>
                                </div>

                                <div class="create-task-content">
                                    <div class="create-task-content-1">
                                        <div class="create-task-content-1-items">
                                            <label>📍 Location Type</label><br/>
                                            <select name="location_type" required>
                                                <option value="Online">Online</option>
                                                <option value="In-person">In-person</option>
                                            </select><br>
                                        </div>
                                        
                                        <div class="create-task-content-1-items">
                                            <label>📍 Location</label><br/>
                                            <input placeholder="Enter Location" type="text" name="location" required><br>
                                        </div>
                                    </div>

                                    <div class="create-task-content-2">
                                        <div class="create-task-content-2-items">
                                            <label>📅 Completion Date</label><br/>
                                            <input type="date" name="completion_date" required><br>
                                        </div>
                                        <div class="create-task-content-2-items">
                                            <label>⏳ Estimated Duration</label><br/>
                                            <select name="estimated_duration" required>
                                                <option value="10-30">10-30</option>
                                                <option value="30-60">30-60</option>
                                                <option value="30 mins">30 mins</option>
                                                <option value="1hr-3hr">1hr-3hr</option>
                                                <option value="1-2hr">1-2hr</option>
                                                <option value="2-4hr">2-4hr</option>
                                                <option value="6-8hr">6-8hr</option>
                                            </select><br>
                                        </div>
                                    </div>

                                    <div class="create-task-content-3">
                                        <div class="create-task-content-3-items">
                                            <label>🧩 Category</label><br/>
                                            <input placeholder="Enter Category" type="text" name="category" required><br>
                                        </div>

                                        <div class="create-task-content-3-items">
                                            <label>💵 Price</label><br/>
                                            <input placeholder="Enter Price" type="number" name="price" step="0.01" max="99999" required><br>
                                        </div>
                                    </div>

                                    <div class="create-task-textareas">
                                        <label>Task Description</label><br/>
                                        <textarea placeholder="Enter task description here..." name="description" required></textarea><br>

                                        <label>Notes / Requirements (Optional)</label><br/>
                                        <textarea placeholder="Enter notes / requirements here..." name="notes"></textarea><br>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php
                        if (isset($_POST['create_task'])) {
                            $title = $_POST['title'];
                            $description = $_POST['description'];
                            $locationType = $_POST['location_type'];
                            $location = $_POST['location'] ?? null;
                            $category = $_POST['category'];
                            $completionDate = $_POST['completion_date'] ?: null;
                            $estimatedDuration = $_POST['estimated_duration'];
                            $price = $_POST['price'];
                            $notes = $_POST['notes'];
                            $status = 'Open';

                            $stmt = $connection->prepare("INSERT INTO tasks 
                                (CommunityID, 
                                Title, 
                                Description, 
                                LocationType, 
                                Location, 
                                Category, 
                                CompletionDate, 
                                EstimatedDuration, 
                                Price, 
                                Notes, 
                                Status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                            $stmt->bind_param("isssssssdss", 
                            $communityID, 
                            $title, 
                            $description, 
                            $locationType, 
                            $location, 
                            $category, 
                            $completionDate, 
                            $estimatedDuration, 
                            $price, 
                            $notes, 
                            $status);

                            if ($stmt->execute()) {
                                echo "<script>alert('Task posted successfully!'); window.location.href = 'comm_dashboard.php';</script>";
                            } else {
                                echo "Error: " . $stmt->error;
                            }

                            $stmt->close();
                        }
                    ?>
                    
                    <!-- ------------------------------ACTIVE POSTS------------------------------ -->
                    <div class="create-task-grid-container">
                        <!-- Plus button to open create task -->
                        <button class="create-button square" onclick="document.getElementById('createTaskModal').style.display='block'">+</button>

                        <?php
                            $active_query = $connection->prepare("
                                SELECT t.*, 
                                    (SELECT COUNT(*) FROM comments c WHERE c.TaskID = t.TaskID) AS CommentCount
                                FROM tasks t
                                WHERE t.CommunityID = ? AND t.Status IN ('Open', 'Ongoing')
                                ORDER BY t.DatePosted DESC
                            ");
                            $active_query->bind_param("i", $communityID);
                            $active_query->execute();
                            $active_result = $active_query->get_result();

                            if ($active_result->num_rows > 0) {
                                while ($task = $active_result->fetch_assoc()) {

                                    echo "<div class='task-box square' 
                                            data-taskid='" . $task['TaskID'] . "' 
                                            data-title='" . htmlspecialchars($task['Title']) . "' 
                                            data-description='" . htmlspecialchars($task['Description']) . "' 
                                            data-locationtype='" . $task['LocationType'] . "' 
                                            data-location='" . $task['Location'] . "' 
                                            data-category='" . $task['Category'] . "' 
                                            data-completiondate='" . $task['CompletionDate'] . "' 
                                            data-price='" . $task['Price'] . "' 
                                            data-notes='" . htmlspecialchars($task['Notes']) . "' 
                                            data-dateposted='" . $task['DatePosted'] . "' 
                                            data-status='" . $task['Status'] . "' 
                                            data-estimatedduration='" . htmlspecialchars($task['EstimatedDuration']) . "' 
                                            onclick='openTaskModal(this)'>

                                        <div class='active-task-header'>
                                            <h3>" . htmlspecialchars($task['Title']) . "</h3>
                                            <p>💬" . $task['CommentCount'] . "</p>
                                        </div>

                                        <div class='active-task-content'>
                                            <div class='active-task-profile-pic'>
                                                <img src='" . $profileSrc . "' alt='Profile Picture' class='poster-profile-picture'>
                                            </div>

                                            <div class='active-task-information-container'>
                                                <div class='active-task-owner'>
                                                    <p>Posted by: " . htmlspecialchars($firstName . ' ' . $lastName) . "</p>
                                                </div>
                                                <div class='active-task-detail-container'>
                                                    <div class='active-task-detail'>
                                                        <p>📍" . htmlspecialchars($task['LocationType']) . "</p>
                                                    </div>
                                                    <div class='active-task-detail'>
                                                        <p>📅" . htmlspecialchars($task['CompletionDate']) . "</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class='active-task-price'>
                                                <p><strong>₱" . number_format($task['Price']) . "</strong></p>
                                            </div>
                                        </div>

                                    </div>";
                                }
                            } else {
                                echo "<p>No active posts found.</p>";
                            }
                            $active_query->close();
                        ?>
                    </div>

                    <!-- ------------------------------VIEW POST MODAL------------------------------ -->
                    <!-- Task Details Modal -->
                    <div id="taskModal" class="modal">
                        <div class="modal-content">
                            
                            <div class="task-modal-header">
                                <!-- Task Title -->
                                <h2 id="modalTitle"></h2>
                                <span class="close-view-post-modal" onclick="closeTaskModal()">&times;</span>
                            </div>

                            <div class="posted-by-container">
                                <div class="posted-by-title">
                                    <p>Posted by</p>
                                </div>
                                <div class="community-poster-profile-details">
                                    <div class="community-poster-profile-details-profile">
                                        <div class="community-poster-profile-details-profile-pic">
                                            <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                                            alt="Profile Picture">
                                        </div>
                                        <div class="community-poster-profile-details-details">
                                            <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                                            <p><em>Posted <span id="modalTimeAgo"></span></em></p>
                                        </div>
                                    </div>
                                    <!-- Dropdown -->
                                    <div id="taskActionSection">
                                        <form action="update_task.php" method="POST">
                                            <input type="hidden" name="task_id" id="modalTaskID">
                                            
                                            <!-- Dropdown for task status -->
                                            <select class="status-dropdown" name="task_status" id="taskStatusDropdown" onchange="updateTaskStatus(this)">
                                                <option value="Open" selected hidden>Open</option>
                                                <option value="Closed">✔️ Mark as Done</option>
                                                <option value="Edit" data-taskid="TASK_ID">🖊️ Edit Task</option>
                                                <option value="Delete" data-taskid="TASK_ID">🗑️ Delete Task</option>
                                            </select>
                                            
                                            <!-- <button type="submit" id="updateTaskStatus">Update Status</button> -->
                                        </form>

                                        <!-- Edit Task and Delete Task actions -->
                                        <div class="display-none">
                                            <a href="edit_task.php?task_id=" id="editTaskLink" title="Edit Task">🖊️</a> | 
                                            <a href="delete_task.php?task_id=" id="deleteTaskLink" title="Delete Task" onclick="return confirm('Are you sure you want to delete this task?')">🗑️</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="task-details-container">
                                <!-- Task Details -->
                                <div class="task-details-1">
                                    <p class="task-details-1-location"><strong>📍 Location</strong><br/>          
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span id="modalLocation"></span></p>

                                    <p class="task-details-1-date"><strong>📅 Completion Date</strong><br/>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="modalCompletionDate"></span></p>

                                    <p><strong>🧩 Category</strong><br/>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="modalCategory"></span></p>
                                </div>

                                <div class="task-details-2">
                                    <div class="task-details-inner-wrapper">
                                        <p><strong>⏳ Estimated Duration</strong><br/>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="modalEstimatedDuration"></span></p>
                                        <!-- <p><strong>Location Type:</strong> 
                                        <span id="modalLocationType"></span></p> -->
                                        <p><strong>💵 Price</strong><br/>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;₱<span id="modalPrice"></span></p>
                                    </div>
                                </div>

                                <p><strong>Task Description</strong><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="modalDescription"></span></p>

                                <p><strong>✉️ Contact via Email</strong><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($user['Email']); ?></p>

                                <p><strong>📌 Notes /  Requirements</strong><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;<span id="modalNotes"></span></p>
                                <!-- <p><strong>Status:</strong> <span id="modalStatus"></span></p> -->
                            </div>
                            
                            <!-- ------------------------------COMMENTS SECTION------------------------------ -->
                            <hr class="hr-comments"/>
                            <h3 class="h3-comments">Comments</h3>
                            <div id="commentsSection">
                                <?php
                                // Fetch comments for the specific task
                                if (isset($_GET['task_id'])) {
                                    $taskId = (int)$_GET['task_id'];

                                    $comments_query = $connection->prepare("
                                        SELECT c.*, u.FirstName, u.LastName, u.TrustPoints 
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
                                            ?>
                                            <div class="comment-box"> <!-- fetch_comments.php para sa comments -->
                                                <!-- <p><strong><?php echo htmlspecialchars($comment['FirstName'] . ' ' . $comment['LastName']); ?></strong></p>
                                                <p><em>Posted on <?php echo htmlspecialchars($comment['DatePosted']); ?></em></p>
                                                <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($comment['TrustPoints']); ?></p>
                                                <p><?php echo htmlspecialchars($comment['Content']); ?></p>
                                                <button onclick="acceptComment(<?php echo $comment['CommentID']; ?>)">Accept</button><button onclick="acceptComment(<?php echo $comment['CommentID']; ?>)">Accept</button> -->
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo "<p>No comments yet.</p>";
                                    }
                                    $comments_query->close();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Modal sa student na nag-comment-->
                    <div id="profileModal" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeProfileModal()">&times;</span>
                            <div>
                                <h2 id="modalFullName"></h2>
                                <img id="modalProfilePicture" src="" alt="Profile Picture">
                                <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                                <p><strong>Role:</strong> <span id="modalRole"></span></p>
                                <p><strong>Trust Points:</strong> <span id="modalTrustPoints"></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                        // Get tasks gikan ani nga community user (dapat dli niya makita ang post sa uban nga community user)
                        $task_query = $connection->prepare("SELECT * FROM tasks WHERE CommunityID = ? ORDER BY DatePosted DESC");
                        $task_query->bind_param("i", $communityID);
                        $task_query->execute();
                        $tasks_result = $task_query->get_result();
                    ?>

                    <!-- ------------------------------PREVIOUS POST------------------------------ -->
                    <h2>Previous Posts</h2>
                        <div class="previous-task-grid-container">
                            <?php
                                $previous_query = $connection->prepare("SELECT * FROM tasks WHERE CommunityID = ? AND Status = 'Closed' ORDER BY DatePosted DESC");
                                $previous_query->bind_param("i", $communityID);
                                $previous_query->execute();
                                $previous_result = $previous_query->get_result();

                                if ($previous_result->num_rows > 0) {
                                    while ($task = $previous_result->fetch_assoc()) {
                                        echo "<div class='previous-task-container previous-task-square'>";
                                            echo "<div class='previous-task-header'";
                                                echo "<h3 class='previous-task-header-h3'> <strong>" . htmlspecialchars($task['Title']) . "</strong> </h3>";
                                                echo "<p>" . htmlspecialchars($task['Status']) . "</p>";
                                            echo "</div>";

                                            echo "<div class='previous-task-content'>";

                                                echo "<div class='previous-task-profile-pic'>";
                                                    echo "<img src='" . $profileSrc . "' alt='Profile Picture' class='poster-profile-picture'>";
                                                echo "</div>";
                                                echo "<div class='previous-task-details-container'>";
                                                    echo "<div class='previous-task-details-owner'>";
                                                        echo "<div class='previous-task-owner'>";
                                                            echo "<p>Posted by: " . htmlspecialchars($firstName . ' ' . $lastName) . "</p>";
                                                        echo "</div>";
                                                    echo "</div>";

                                                    echo "<div class='previous-task-details-content'>";
                                                        echo "<div class='previous-task-detail'>";
                                                            echo "<p>📍" . htmlspecialchars($task['LocationType']) . "</p>";
                                                        echo "</div>";

                                                        echo "<div class='previous-task-detail'>";
                                                            echo "<p>📅" . htmlspecialchars($task['CompletionDate']) . "</p>";
                                                        echo "</div>";
                                                    echo "</div>";

                                                echo "</div>";

                                                echo "<div class='previous-task-price'>";
                                                    echo "<p> <strong>₱" . number_format($task['Price'], 2) . "</strong></p>";
                                                echo "</div>";
                                                
                                            echo "</div>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p>No previous posts found.</p>";
                                }
                                $previous_query->close();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ------------------------------JAVASCRIPT------------------------------ -->
        <script src="comm_script.js"></script>                    
    </body>
</html>