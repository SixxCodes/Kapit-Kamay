<?php
    session_start();
    include_once('../../includes/mysqlconnection.php'); // connect sa database

    // ang gi-log in na email ug if community ba ang user
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    if (isset($_GET['task_id'])) {
        $taskID = $_GET['task_id'];

        // query para kuha task details
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

            <label for="location_type">Location Type</label>
            <select name="location_type" id="location_type" required>
                <option value="Online" <?php echo $task['LocationType'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                <option value="In-person" <?php echo $task['LocationType'] == 'In-person' ? 'selected' : ''; ?>>In-person</option>
            </select><br><br>

            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($task['Location']); ?>"><br><br>

            <label for="completion_date">Completion Date</label>
            <input type="date" id="completion_date" name="completion_date" value="<?php echo htmlspecialchars($task['CompletionDate']); ?>"><br><br>

            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($task['Category']); ?>"><br><br>

            <label for="estimated_duration">Estimated Duration</label>
            <select id="estimated_duration" name="estimated_duration" required>
                <?php
                    $durations = [
                        'less than 10 mins', 
                        '10 mins', 
                        '30 mins', 
                        '1 hour',
                        '2 hours', 
                        '4 hours', 
                        '8 hours', 
                        '1 day',
                        '2 days', 
                        '1 week'
                    ];
                    foreach ($durations as $duration) {
                        $selected = ($task['EstimatedDuration'] === $duration) ? 'selected' : '';
                        echo "<option value=\"$duration\" $selected>$duration</option>";
                    }
                ?>
            </select><br><br>


            <label for="price">Price</label>
            <input type="number" id="price" name="price" value="<?php echo $task['Price']; ?>" required><br><br>

            <label for="description">Description</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($task['Description']); ?></textarea><br><br>

            <label for="notes">Notes</label>
            <textarea id="notes" name="notes"><?php echo htmlspecialchars($task['Notes']); ?></textarea><br><br>

            <button type="submit">Update Task</button>
        </form>
    </body>
</html>
