<?php
    session_start();
    include_once('../includes/mysqlconnection.php'); // connect ni sa database

    // check if user: logged in, role: Community
    if (!isset($_SESSION['login_email']) || $_SESSION['role'] !== 'Community') {
        echo "Unauthorized access.";
        exit();
    }

    //ang gi-login nga email kay mao na sya ang ma-store sa $email variable
    $email = $_SESSION['login_email'];

    // Get Community User ID 
    $stmt = $connection->prepare("SELECT UserID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($communityID);
    $stmt->fetch();
    $stmt->close();

    // pag wlay match
    if (!$communityID) {
        echo "Community user not found.";
        exit();
    }

    // ------------------------------CREATE POST------------------------------ 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // mag-collect na ni sya ug form data na gi-input sa user
        $location = $_POST['location'];
        $completion_date = $_POST['completion_date'];
        $category = $_POST['category'];
        $duration = $_POST['duration'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $notes = $_POST['notes'];
        $title = $_POST['title'];
        $locationType = $_POST['location_type'];
        $status = "Open"; // Default status (wla pa pud ni sya)

        // insert na ni sya sa database
        $insertQuery = "INSERT INTO tasks (
            CommunityID, Title, Description, LocationType, Location, Category, CompletionDate, Price, Notes, Status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($insertQuery);
        $stmt->bind_param(
            "issssssdss",
            $communityID,
            $title,
            $description,
            $locationType,
            $location,
            $category,
            $completion_date,
            $price,
            $notes,
            $status
        );

        // Execute and Handle Result
        if ($stmt->execute()) {
            echo "Task posted successfully!";
            // Optional: redirect to dashboard
            // header("Location: comm_dashboard.php");
            // exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // ------------------------------VIEW POST------------------------------ 
    // kwaon ang taSks nga gi-post sa current naka-log in community user
    $query = "
    SELECT 
        t.TaskID, t.Title, t.LocationType, t.DatePosted, t.Price,
        (SELECT COUNT(*) FROM comments WHERE comments.TaskID = t.TaskID) AS CommentCount
    FROM tasks t
    WHERE t.CommunityID = ?
    ORDER BY t.DatePosted DESC
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $communityID);
    $stmt->execute();
    $result = $stmt->get_result();

?>
