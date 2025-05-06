<!-- unsa gni mabuhat sa community dashboard?
make post
view, edit, delete post
view comments, profile
hire
rate -->

<?php
session_start();
include_once('../includes/mysqlconnection.php');
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Homepage</title>
    </head>
    <body>
        <h1>Hello community member, 
            <?php 
                if(isset($_SESSION['login_email'])) {
                    $email = $_SESSION['login_email'];
                    $query = mysqli_query($connection, "SELECT * FROM users WHERE Email='$email'");
                    $row = mysqli_fetch_array($query);
                    echo htmlspecialchars($row['FirstName']);
                } 
            ?>
        </h1>
    </body>
</html>
