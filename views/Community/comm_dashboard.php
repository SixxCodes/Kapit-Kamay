<!-- 
    unsa gni mabuhat sa community dashboard?
    1. make post (done)
    2. view, edit, delete post (done)
    3. view comments, profile
    4. hire
    5. rate 
-->

<!-- 
    TO-DO List:
    1. Buhat create task modal (done)
    2. buhat view task modal (mark as done, edit, delete, comment, hire)
    3. butang previous posts (done)
    4. butang sa profile (active posts, previous posts, total task posted) (done)
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
        <title>Community Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="comm_style.css">
    </head>
    <body>
        <!-- ------------------------------SEARCH------------------------------ -->
        <input type="text" id="taskSearchBar" placeholder="Search your tasks..." onkeyup="filterMyTasks()" style="padding: 8px; width: 100%; max-width: 400px; margin-bottom: 20px;">
        
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
                <!-- <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($user['TrustPoints']); ?></p> -->
                
                <!-- ------------------------------LOG OUT------------------------------ -->
                <a href="../logout.php">Logout</a>
            </div>
        </div>

        <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
            alt="Profile Picture" 
            id="userIcon" 
            style="width:100px; height:100px; border-radius:50%; cursor: pointer;"
            onclick="openUserModal()">

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
        <div style="margin-top: 10px;">
            <p><strong>Total Active Task(s):</strong> <?php echo $activeTasks; ?></p>
            <p><strong>Total Previous Task(s):</strong> <?php echo $previousTasks; ?></p>
            <p><strong>Total Task(s) Posted:</strong> <?php echo $totalTasks; ?></p>
        </div>

        <!-- ------------------------------CREATE POST------------------------------ -->
        <!-- Plus button to open create task -->
        <button onclick="document.getElementById('createTaskModal').style.display='block'">+ Create Task</button>

        <!-- Create Task -->
        <div id="createTaskModal" style="display:none;">
            <form action="" method="POST">
                <h3>Create New Task</h3>

                <label>Title:</label>
                <input type="text" name="title" required><br>

                <label>Location Type:</label>
                <select name="location_type" required>
                    <option value="Online">Online</option>
                    <option value="In-person">In-person</option>
                </select><br>

                <label>Location (if In-person):</label>
                <input type="text" name="location"><br>

                <label>Completion Date:</label>
                <input type="date" name="completion_date"><br>
        
                <label>Category:</label>
                <input type="text" name="category" required><br>

                <label>Estimated Duration:</label>
                <select name="estimated_duration" required>
                    <option value="less than 10 mins">less than 10mins</option>
                    <option value="10 mins">10 mins</option>
                    <option value="30 mins">30 mins</option>
                    <option value="1 hour">1 hour</option>
                    <option value="2 hours">2 hours</option>
                    <option value="4 hours">4 hours</option>
                    <option value="8 hours">8 hours</option>
                    <option value="1 day">1 day</option>
                    <option value="2 days">2 days</option>
                    <option value="1 week">1 week</option>
                </select><br>

                <label>Price:</label>
                <input type="number" name="price" step="0.01" required><br>

                <label>Task Description:</label>
                <textarea name="description" required></textarea><br>

                <label>Notes / Requirements (optional):</label>
                <textarea name="notes"></textarea><br>

                <input type="submit" name="create_task" value="Post Task">
                <button type="button" onclick="document.getElementById('createTaskModal').style.display='none'">Cancel</button>
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
                    (CommunityID, Title, Description, LocationType, Location, Category, CompletionDate, EstimatedDuration, Price, Notes, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("isssssssdss", $communityID, $title, $description, $locationType, $location, $category, $completionDate, $estimatedDuration, $price, $notes, $status);

                if ($stmt->execute()) {
                    echo "<script>alert('Task posted successfully!'); window.location.href = 'comm_dashboard.php';</script>";
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            }
        ?>
        
        <!-- ------------------------------ACTIVE POST------------------------------ -->
        <h2>Active Posts</h2>
        <?php
            $active_query = $connection->prepare("SELECT * FROM tasks WHERE CommunityID = ? AND Status IN ('Open', 'Ongoing') ORDER BY DatePosted DESC");
            $active_query->bind_param("i", $communityID);
            $active_query->execute();
            $active_result = $active_query->get_result();

            if ($active_result->num_rows > 0) {
                while ($task = $active_result->fetch_assoc()) {

                    echo "<div class='task-box' 
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
                                <h3>" . htmlspecialchars($task['Title']) . "</h3>
                                <p><strong>Location Type:</strong> " . htmlspecialchars($task['LocationType']) . "</p>
                                <p><strong>Completion Date:</strong> " . htmlspecialchars($task['CompletionDate']) . "</p>
                                <p><strong>Price:</strong> ₱" . number_format($task['Price'], 2) . "</p>
                                
                        </div>";
                }
            } else {
                echo "<p>No active posts found.</p>";
            }
            $active_query->close();
        ?>

        <!-- ------------------------------PREVIOUS POST------------------------------ -->
        <h2>Previous Posts</h2>
        <?php
            $previous_query = $connection->prepare("SELECT * FROM tasks WHERE CommunityID = ? AND Status = 'Closed' ORDER BY DatePosted DESC");
            $previous_query->bind_param("i", $communityID);
            $previous_query->execute();
            $previous_result = $previous_query->get_result();

            if ($previous_result->num_rows > 0) {
                while ($task = $previous_result->fetch_assoc()) {
                    echo "<div style='border:1px solid #aaa; padding:10px; margin-bottom:10px; background:#f0f0f0;'>";
                    echo "<h3>" . htmlspecialchars($task['Title']) . "</h3>";
                    echo "<p><strong>Category:</strong> " . htmlspecialchars($task['Category']) . "</p>";
                    echo "<p><strong>Completion Date:</strong> " . htmlspecialchars($task['CompletionDate']) . "</p>";
                    echo "<p><strong>Price:</strong> ₱" . number_format($task['Price'], 2) . "</p>";
                    echo "<p><strong>Status:</strong> " . htmlspecialchars($task['Status']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No previous posts found.</p>";
            }
            $previous_query->close();
        ?>

        <!-- ------------------------------VIEW POST------------------------------ -->
        <!-- Task Details Modal -->
        <div id="taskModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeTaskModal()" style="color: black;">&times;</span>
                <h2>Posted by <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                <p><em>Posted <span id="modalTimeAgo"></span></em></p>
                <img src="<?php echo htmlspecialchars($profileSrc); ?>" 
                    alt="Profile Picture" 
                    style="width:100px; height:100px; border-radius:50%;">
                    
                <!-- Task Title -->
                <h2 id="modalTitle"></h2>
                
                <!-- Task Details -->
                <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                <p><strong>Completion Date:</strong> <span id="modalCompletionDate"></span></p>
                <p><strong>Category:</strong> <span id="modalCategory"></span></p>
                <p><strong>Estimated Duration:</strong> <span id="modalEstimatedDuration"></span></p>
                <!-- <p><strong>Location Type:</strong> <span id="modalLocationType"></span></p> -->
                <p><strong>Price:</strong> ₱<span id="modalPrice"></span></p>
                <p><strong>Description:</strong> <span id="modalDescription"></span></p>
                <p><strong>Contact via Email:</strong></strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                <p><strong>Notes:</strong> <span id="modalNotes"></span></p>
                <!-- <p><strong>Status:</strong> <span id="modalStatus"></span></p> -->
                
                <!-- Dropdown -->
                <div id="taskActionSection">
                    <form action="update_task.php" method="POST">
                        <input type="hidden" name="task_id" id="modalTaskID">
                        
                        <!-- Dropdown for task status -->
                        <select name="task_status" id="taskStatusDropdown" onchange="updateTaskStatus(this)">
                            <option value="Open">Open</option>
                            <option value="Closed">Mark as Done</option>
                        </select>
                        
                        <!-- <button type="submit" id="updateTaskStatus">Update Status</button> -->
                    </form>

                    <!-- Edit Task and Delete Task actions -->
                    <a href="edit_task.php?task_id=" id="editTaskLink">Edit Task</a> | 
                    <a href="delete_task.php?task_id=" id="deleteTaskLink" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</a>
                </div>

                <!-- ------------------------------COMMENTS SECTION------------------------------ -->
                <h3>Comments</h3>
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
                                <div class="comment-box" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                                    <p><strong><?php echo htmlspecialchars($comment['FirstName'] . ' ' . $comment['LastName']); ?></strong></p>
                                    <p><em>Posted on <?php echo htmlspecialchars($comment['DatePosted']); ?></em></p>
                                    <p><strong>Trust Points:</strong> <?php echo htmlspecialchars($comment['TrustPoints']); ?></p>
                                    <p><?php echo htmlspecialchars($comment['Content']); ?></p>
                                    <button onclick="acceptComment(<?php echo $comment['CommentID']; ?>)">Accept</button>
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
                    <!-- Profile Modal -->
<div id="profileModal" class="modal" style="display: none;">
    <div class="modal-content" style="padding: 20px; border-radius: 10px; max-width: 400px; margin: auto;">
        <span class="close" onclick="closeProfileModal()" style="cursor: pointer; float: right; font-size: 20px;">&times;</span>
        <div style="text-align: center;">
            <img id="modalProfilePicture" src="" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
            <h2 id="modalFullName"></h2>
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

        <!-- ------------------------------JAVASCRIPT------------------------------ -->
        <script src="comm_script.js"></script>

    </body>
</html>