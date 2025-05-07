<?php
session_start();
include_once('../includes/mysqlconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskID = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    // and so on...

    $stmt = $connection->prepare("UPDATE tasks SET Title=?, Description=?, Category=?, Price=? WHERE TaskID=?");
    $stmt->bind_param("sssdi", $title, $description, $category, $price, $taskID);

    if ($stmt->execute()) {
        header("Location: comm_dashboard.php?success=Task updated");
        exit();
    } else {
        echo "Failed to update task.";
    }
    $stmt->close();
}
?>
