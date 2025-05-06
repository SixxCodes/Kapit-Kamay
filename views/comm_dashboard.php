<!-- 
    unsa gni mabuhat sa community dashboard?
    1. make post
    2. view, edit, delete post
    3. view comments, profile
    4. hire
    5. rate 
-->

<!-- 
    TO-DO List:
    1. Buhat create task modal (done)
    2. buhat view task modal (mark as done, edit, delete, comment, hire)
    3. butang previous posts
    4. butang sa profile (active posts, previous posts, total task posted)
 -->

<?php
    // session_start();
    include_once('../includes/mysqlconnection.php');
    include_once('create_post.php');
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Homepage</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- temporary, must be external sya later -->
        <style>
            /* Modal Background */
            #taskModal1 {
                display: none;
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.6);
                justify-content: center;
                align-items: center;
                z-index: 999;
                overflow: auto;
            }

            /* Scrollable */
            .modal-content1 {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                overflow-y: auto;
                position: relative;
                margin: 40px auto;
            }

            #openModalBtn {
                font-size: 24px;
                padding: 10px 20px;
                cursor: pointer;
            }

            #closeModalBtn {
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 18px;
                cursor: pointer;
                background: transparent;
                border: none;
            }

            .form-group {
                margin-bottom: 15px;
            }

            label {
                display: block;
                margin-bottom: 5px;
            }

            input[type="text"],
            input[type="date"],
            select,
            textarea {
                width: 100%;
                padding: 8px;
                box-sizing: border-box;
            }

            button[type="submit"] {
                padding: 10px 15px;
                background-color: #28a745;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }

            button[type="submit"]:hover {
                background-color: #218838;
            }


            .task-card2 {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            }
            .modal2 {
                display: none;
                position: fixed;
                background: rgba(0,0,0,0.5);
                top: 0; left: 0; right: 0; bottom: 0;
                justify-content: center;
                align-items: center;
            }
            .modal-content2 {
                background: white;
                padding: 20px;
                min-width: 300px;
            }
        </style>

    </head>
    <body>
        <h1>
            <?php 
                if(isset($_SESSION['login_email'])) {
                    $email = $_SESSION['login_email'];
                    $query = mysqli_query($connection, "SELECT * FROM users WHERE Email='$email'");
                    $row = mysqli_fetch_array($query);
                    echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']);
                } 
            ?>
        </h1>

        <!-- ------------------------------CREATE POST------------------------------ -->
        
        <!-- Plus button to open modal -->
        <h2>Create a Task</h2>
        <button id="openModalBtn" style="font-size: 24px; padding: 10px;">＋</button>          

        <div id="taskModal1">
            <div class="modal-content1">
                <button id="closeModalBtn">✖</button>
                <h2>Create a Task</h2>
                <form action="create_post.php" method="post">

                    <div class="form-group">
                        <label for="title">Task Title:</label>
                        <input type="text" name="title" id="title" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" name="location" id="location" required>
                    </div>

                    <div class="form-group">
                        <label>Location Type:</label><br>
                        <input type="radio" name="location_type" value="Online" id="online" required>
                        <label for="online">Online</label>
                        <input type="radio" name="location_type" value="In-person" id="in_person" required>
                        <label for="in_person">In-person</label>
                    </div>

                    <div class="form-group">
                        <label for="completion_date">Completion Date:</label>
                        <input type="date" name="completion_date" id="completion_date" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" required>
                            <option value="" disabled selected>Select a category</option>
                            <option value="Cleaning">Cleaning</option>
                            <option value="Tutoring">Tutoring</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Errands">Errands</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="duration">Estimated Duration:</label>
                        <select name="duration" id="duration" required>
                            <option value="" disabled selected>Select duration</option>
                            <option value="30 minutes">30 minutes</option>
                            <option value="1 hour">1 hour</option>
                            <option value="2-3 hours">2–3 hours</option>
                            <option value="Half day">Half day</option>
                            <option value="Full day">Whole day</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (₱):</label>
                        <input type="text" name="price" id="price" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Task Description:</label>
                        <textarea name="description" id="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes / Requirements (optional):</label>
                        <textarea name="notes" id="notes"></textarea>
                    </div>

                    <button type="submit">Submit Task</button>
                </form>
            </div>
        </div>
        
        <!-- ------------------------------VIEW POSTS--------------------------------> 
        <h2>Your Posted Tasks</h2>
        
        // calculate time ago ni
        <?php while ($row = $result->fetch_assoc()): 
            $datePosted = new DateTime($row['DatePosted']);
            $now = new DateTime();
            $interval = $now->diff($datePosted);
            $minutesAgo = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
            if ($minutesAgo == 0) {
                $timeAgo = "just now";
            } elseif ($minutesAgo == 1) {
                $timeAgo = "1 minute ago";
            } else {
                $timeAgo = "$minutesAgo minutes ago";
            }
        ?>

        <div class="task-card2" onclick="openModal(<?= $row['TaskID'] ?>)">
            <h3><?= htmlspecialchars($row['Title']) ?></h3>
            <p>Location Type: <?= htmlspecialchars($row['LocationType']) ?></p>
            <p>Date Posted: <?= htmlspecialchars($row['DatePosted']) ?> (<?= $timeAgo ?>)</p>
            <p>Price: ₱<?= number_format($row['Price'], 2) ?></p>
            <p>Comments: <?= $row['CommentCount'] ?></p>
        </div>

        <?php endwhile; ?>

        <!-- Modal after click sa post-->
        <div id="taskModal" class="modal2" onclick="closeModal()">
            <div class="modal-content2" onclick="event.stopPropagation()">
                <h3>Task Details</h3>
                <p>This modal is currently empty. You can load task details here later.</p>
                <button onclick="closeModal()">Close</button>
            </div>
        </div>
        
        <!-- ------------------------------JAVASCRIPT--------------------------------> 
        <script>
            // sa create post na script
            const openModalBtn = document.getElementById('openModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const taskModal = document.getElementById('taskModal1');

            openModalBtn.addEventListener('click', () => {
                taskModal.style.display = 'flex';
            });

            closeModalBtn.addEventListener('click', () => {
                taskModal.style.display = 'none';
            });

            window.addEventListener('click', (e) => {
                if (e.target === taskModal) {
                    taskModal.style.display = 'none';
                }
            });

            // sa view post na script
            function openModal(taskID) {
                document.getElementById('taskModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('taskModal').style.display = 'none';
            }
        </script>
    </body>
</html>
