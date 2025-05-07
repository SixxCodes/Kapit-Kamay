<?php
    session_start();
    include_once('../includes/mysqlconnection.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // ✅ 1. Handle status-only update (from AJAX or modal)
        if (isset($_POST['task_id']) && isset($_POST['task_status']) && !isset($_POST['title'])) {
            $taskID = $_POST['task_id'];
            $taskStatus = $_POST['task_status'];

            $stmt = $connection->prepare("UPDATE tasks SET Status = ? WHERE TaskID = ?");
            $stmt->bind_param("si", $taskStatus, $taskID);

            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "error";
            }

            $stmt->close();
            exit(); // Stop script here for status-only update
        }

        // ✅ 2. Handle full task update (from edit_task.php)
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $location = $_POST['location'];
        $location_type = $_POST['location_type'];
        $completion_date = $_POST['completion_date'];
        $notes = $_POST['notes'];
        $estimated_duration = $_POST['estimated_duration']; // <-- It's a string (e.g., '1 hour')

        // Update all fields including EstimatedDuration
        $stmt = $connection->prepare("UPDATE tasks 
            SET Title = ?, LocationType = ?, Location = ?, Category = ?, Price = ?, Description = ?, CompletionDate = ?, Notes = ?, EstimatedDuration = ? 
            WHERE TaskID = ?");
        $stmt->bind_param("ssssdssssi", $title, $location_type, $location, $category, $price, $description, $completion_date, $notes, $estimated_duration, $task_id);

        if ($stmt->execute()) {
            header("Location: comm_dashboard.php?success=Task updated");
            exit();
        } else {
            echo "Failed to update task.";
        }

        $stmt->close();
    }
?>
