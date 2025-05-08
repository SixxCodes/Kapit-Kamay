<?php
include_once('../includes/mysqlconnection.php');

if(isset($_POST['login-btn'])){
    $email = $_POST['login_email']; // kwaon ang login_email sa form
    $password = $_POST['login_password']; // kwaon ang login_password sa form
    $role = $_POST['role']; // kwaon ang role na gipili sa radio button sa form

    // Check if email is already naa
    $sql = "SELECT * FROM users WHERE Email = '$email'";
    $result = $connection->query($sql);
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        // Verifying password with hashed password na naa sa database
        if(password_verify($password, $row['Password'])){
            // Check if ang selected role kay match sa role na naa sa database
            if (strcasecmp($row['Role'], $role) === 0) { // case-insensitive comparison
                session_start();
                $_SESSION['login_email'] = $row['Email'];
                $_SESSION['role'] = $row['Role'];
            
                if ($row['Role'] == 'Student') {
                    header("Location: Student/stud_dashboard.php");
                } elseif ($row['Role'] == 'Community') {
                    header("Location: Community/comm_dashboard.php");
                }
                exit();
            } else {
                echo "Role mismatch! Please select the correct role.";
            }
            
        } else {
            echo "Invalid email or password!";
        }
    } else {
        echo "Invalid email or password!";
    }
}
?>
