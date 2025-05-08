<?php
    session_start();
    include_once('../../includes/mysqlconnection.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
        $email = $_SESSION['login_email'];

        // Make sure folder exists
        $uploadDir = "uploads/profile_pictures/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetPath)) {
            // Save the path to DB
            $stmt = $connection->prepare("UPDATE users SET ProfilePicture = ? WHERE Email = ?");
            $stmt->bind_param("ss", $targetPath, $email);
            $stmt->execute();
            $stmt->close();

            header("Location: stud_dashboard.php?upload=success");
            exit();
        } else {
            echo "Failed to upload image.";
        }
    }
?>
