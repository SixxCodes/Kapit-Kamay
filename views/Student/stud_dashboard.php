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
    include_once('../../includes/mysqlconnection.php');
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Student Dashboard</title>
    </head>
    <body>
        <h1>Hello student, 
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
