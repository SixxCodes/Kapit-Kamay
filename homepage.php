<?php
session_start();
include 'mysqlconnection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Homepage</title>
</head>
<body>
    <h1>Hello, 
        <?php 
            if(isset($_SESSION['login_email'])) {
                $email = $_SESSION['login_email'];
                $query = mysqli_query($connection, "SELECT * FROM users WHERE email='$email'");
                $row = mysqli_fetch_array($query);
                echo htmlspecialchars($row['name']);
            } 
        ?>
    </h1>
</body>
</html>
