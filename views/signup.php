<?php
include_once('../includes/mysqlconnection.php');

if(isset($_POST['signup-btn'])){
    $fname = $_POST['signup_fname'];
    $lname = $_POST['signup_lname']; // kwaon ang signup_name sa form
    $email = $_POST['signup_email']; // kwaon ang signup_email sa form
    $password = $_POST['signup_password']; // kwaon ang signup_password sa form
    $role = $_POST['role'];  // kwaon ang role na gipili sa radio button sa form

    // Naka-hash nga password kay packut
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email is already naa
    $checkEmail = "SELECT * FROM users WHERE Email = '$email'";
    $result = $connection->query($checkEmail);
    
    if($result->num_rows > 0){
        echo "Email already exists!";
    } else {
        // Insert user sa database
        $insertQuery = "INSERT INTO users (FirstName, LastName, Email, Password, Role) 
                        VALUES ('$fname', '$lname', '$email', '$password', '$role')";
        if($connection->query($insertQuery) === TRUE){
            header("Location: login.html"); // Redirect to login page
        } else {
            echo "Error: " . $connection->error;
        }
    }
}
?>
