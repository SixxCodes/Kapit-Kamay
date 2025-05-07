<!-- 
    unsa gni mabuhat sa community dashboard?
    1. make post
    2. view, edit, delete post
    3. view comments, profile
    4. hire
    5. rate 
-->

<!-- 
    TO-DO List:
    1. Buhat create task modal
    2. buhat view task modal (mark as done, edit, delete, comment, hire)
    3. butang previous posts
    4. butang sa profile (active posts, previous posts, total task posted)
 -->

 <?php
    session_start();
    include_once('../includes/mysqlconnection.php'); // connect ni sa database

    // check if user: logged in, role: Community
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    //ang gi-login nga email kay mao na sya ang ma-store sa $email variable
    $email = $_SESSION['login_email'];

    // Get Community User ID 
    $stmt = $connection->prepare("SELECT UserID, FirstName, LastName FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($communityID, $firstName, $lastName);
    $stmt->fetch();
    $stmt->close();

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
        <title>Homepage</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <!-- ------------------------------PROFILE SUMMARY------------------------------ -->
        <h1>Kapit-Kamay</h1>
        <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>


        <!-- ------------------------------CREATE POST------------------------------ -->
        <!-- Plus button to open modal -->
        <button onclick="document.getElementById('createTaskModal').style.display='block'">+ Create Task</button>

        <!-- Create Task Modal -->
        <div id="createTaskModal" style="display:none;">
            <form action="" method="POST">
                <h3>Create New Task</h3>

                <label>Title:</label>
                <input type="text" name="title" required><br>

                <label>Description:</label>
                <textarea name="description" required></textarea><br>

                <label>Location Type:</label>
                <select name="location_type" required>
                    <option value="Online">Online</option>
                    <option value="In-person">In-person</option>
                </select><br>

                <label>Location (if In-person):</label>
                <input type="text" name="location"><br>

                <label>Category:</label>
                <input type="text" name="category" required><br>

                <label>Completion Date:</label>
                <input type="date" name="completion_date"><br>

                <label>Price:</label>
                <input type="number" name="price" step="0.01" required><br>

                <label>Notes (optional):</label>
                <textarea name="notes"></textarea><br>

                <input type="submit" name="create_task" value="Post Task">
                <button type="button" onclick="document.getElementById('createTaskModal').style.display='none'">Cancel</button>
            </form>
        </div>
        
        <!-- ------------------------------ACTIVE POST------------------------------ -->
        <h2>Active Posts</h2>
        <?php
            $active_query = $connection->prepare("SELECT * FROM tasks WHERE CommunityID = ? AND Status IN ('Open', 'Ongoing') ORDER BY DatePosted DESC");
            $active_query->bind_param("i", $communityID);
            $active_query->execute();
            $active_result = $active_query->get_result();

            if ($active_result->num_rows > 0) {
                while ($task = $active_result->fetch_assoc()) {
                    echo "<div style='border:1px solid #000; padding:10px; margin-bottom:10px;'>";
                    echo "<h3>" . htmlspecialchars($task['Title']) . "</h3>";
                    echo "<p><strong>Category:</strong> " . htmlspecialchars($task['Category']) . "</p>";
                    echo "<p><strong>Location Type:</strong> " . htmlspecialchars($task['LocationType']) . "</p>";
                    echo "<p><strong>Price:</strong> ₱" . number_format($task['Price'], 2) . "</p>";
                    echo "<p><strong>Status:</strong> " . htmlspecialchars($task['Status']) . "</p>";

                    // Dropdown for task actions
                    echo "<form action='update_task.php' method='POST'>";
                    echo "<input type='hidden' name='task_id' value='" . $task['TaskID'] . "'>";
                    echo "<select name='task_status'>";
                    echo "<option value='Open'" . ($task['Status'] == 'Open' ? ' selected' : '') . ">Open</option>";
                    echo "<option value='Ongoing'" . ($task['Status'] == 'Ongoing' ? ' selected' : '') . ">Ongoing</option>";
                    echo "<option value='Closed'" . ($task['Status'] == 'Closed' ? ' selected' : '') . ">Closed</option>";
                    echo "</select>";

                    // Action buttons: Edit, Delete, Mark as Done
                    // echo "<button type='submit' name='update_task'>Update</button>";
                    echo "<a href='edit_task.php?task_id=" . $task['TaskID'] . "'>Edit</a> | ";
                    echo "<a href='delete_task.php?task_id=" . $task['TaskID'] . "' onclick='return confirm(\"Are you sure you want to delete this task?\")'>Delete</a>";
                    echo "</form>";

                    echo "</div>";
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

        
        <a href="logout.php">Logout</a>

        <?php
        if (isset($_POST['create_task'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $locationType = $_POST['location_type'];
            $location = $_POST['location'] ?? null;
            $category = $_POST['category'];
            $completionDate = $_POST['completion_date'] ?: null;
            $price = $_POST['price'];
            $notes = $_POST['notes'];
            $status = 'Open';

            $stmt = $connection->prepare("INSERT INTO tasks (CommunityID, Title, Description, LocationType, Location, Category, CompletionDate, Price, Notes, Status) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssdss", $communityID, $title, $description, $locationType, $location, $category, $completionDate, $price, $notes, $status);

            if ($stmt->execute()) {
                echo "<script>alert('Task posted successfully!'); window.location.href = 'comm_dashboard.php';</script>";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        ?>

    </body>
</html>
