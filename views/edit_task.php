<?php
    session_start();
    include_once('../includes/mysqlconnection.php');

    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    if (isset($_GET['task_id'])) {
        $taskID = $_GET['task_id'];

        // Fetch task details
        $task_query = $connection->prepare("SELECT * FROM tasks WHERE TaskID = ?");
        $task_query->bind_param("i", $taskID);
        $task_query->execute();
        $task_result = $task_query->get_result();
        $task = $task_result->fetch_assoc();
        $task_query->close();
    } else {
        echo "Task ID not found!";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
</head>
<body>
    <h2>Edit Task: <?php echo htmlspecialchars($task['Title']); ?></h2>
    <form action="update_task.php" method="POST">
        <input type="hidden" name="task_id" value="<?php echo $task['TaskID']; ?>">

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['Title']); ?>" required><br><br>

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($task['Description']); ?></textarea><br><br>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($task['Location']); ?>"><br><br>

        <label for="price">Price</label>
        <input type="number" id="price" name="price" value="<?php echo $task['Price']; ?>" required><br><br>

        <label for="category">Category</label>
        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($task['Category']); ?>"><br><br>

        <label for="location_type">Location Type</label>
        <select name="location_type" id="location_type" required>
            <option value="Online" <?php echo $task['LocationType'] == 'Online' ? 'selected' : ''; ?>>Online</option>
            <option value="In-person" <?php echo $task['LocationType'] == 'In-person' ? 'selected' : ''; ?>>In-person</option>
        </select><br><br>

        <button type="submit">Update Task</button>
    </form>
</body>
</html>
