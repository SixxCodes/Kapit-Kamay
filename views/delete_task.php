<?php
    session_start();
    include_once('../includes/mysqlconnection.php');

    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    if (isset($_GET['task_id'])) {
        $taskID = $_GET['task_id'];

        // Delete the task
        $delete_query = $connection->prepare("DELETE FROM tasks WHERE TaskID = ?");
        $delete_query->bind_param("i", $taskID);
        $delete_query->execute();

        // Check if deletion was successful
        if ($delete_query->affected_rows > 0) {
            echo "Task deleted successfully!";
        } else {
            echo "Error deleting task.";
        }
        $delete_query->close();
    } else {
        echo "Task ID not found!";
    }

    // Redirect back to community dashboard
    header('Location: comm_dashboard.php');
    exit();
?>
