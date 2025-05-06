<?php
session_start();
include 'mysqlconnection.php';

if (isset($_POST['login-btn'])) {
    $email = $_POST['login_email'];
    $password = $_POST['login_password'];

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['login_email'] = $row['email'];
            header("Location: homepage.php");
            exit();
        } else {
            echo "Invalid email or password!";
        }
    } else {
        echo "Invalid email or password!";
    }

    $stmt->close();
}
?>
