<?php
include 'mysqlconnection.php';

if (isset($_POST['signup-btn'])) {
    $name = $_POST['signup_name'];
    $email = $_POST['signup_email'];
    $password = $_POST['signup_password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // gamit ug password hash kay packut mn ko

    // Check ug email exist bah
    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email already exists!";
    } else {
        // add nag bag-ong user na gi-input tralalero tralala
        $insert = $connection->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $name, $email, $hashed_password);
        if ($insert->execute()) {
            header("Location: login.html");
        } else {
            echo "Error: " . $connection->error;
        }
        $insert->close();
    }

    $stmt->close();
}
?>
