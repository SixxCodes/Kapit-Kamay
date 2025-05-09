// ------------------------------TASK MODAL------------------------------
function openTaskModal(taskElement) {
    // Get task details sa gi-click lang nga task box
    var taskId = taskElement.getAttribute('data-taskid');
    var title = taskElement.getAttribute('data-title');
    var description = taskElement.getAttribute('data-description');
    var locationType = taskElement.getAttribute('data-locationtype');
    var location = taskElement.getAttribute('data-location');
    var category = taskElement.getAttribute('data-category');
    var estimatedDuration = taskElement.getAttribute("data-estimatedduration");
    var completionDate = taskElement.getAttribute('data-completiondate');
    var price = taskElement.getAttribute('data-price');
    var notes = taskElement.getAttribute('data-notes');
    var status = taskElement.getAttribute('data-status');
    
    // Set task details into the modal
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalCategory').innerText = category;
    document.getElementById("modalEstimatedDuration").innerText = estimatedDuration;
    // document.getElementById('modalLocationType').innerText = locationType;
    document.getElementById('modalLocation').innerText = location;
    document.getElementById('modalCompletionDate').innerText = completionDate;
    document.getElementById('modalPrice').innerText = price;
    document.getElementById('modalNotes').innerText = notes;
    // document.getElementById('modalStatus').innerText = status;
    document.getElementById("modalDescription").textContent = description;
    
    // Set hidden input field with task ID
    document.getElementById('modalTaskID').value = taskId;
    fetchComments(taskId);
    
    // Set edit and delete links with  task ID
    document.getElementById('editTaskLink').href = 'edit_task.php?task_id=' + taskId;
    document.getElementById('deleteTaskLink').href = 'delete_task.php?task_id=' + taskId;

    // Set current task status in the dropdown
    var statusDropdown = document.getElementById('taskStatusDropdown');
    for (var i = 0; i < statusDropdown.options.length; i++) {
        if (statusDropdown.options[i].value === status) {
            statusDropdown.selectedIndex = i;
            break;
        }
    }

    // Show modal
    document.getElementById('taskModal').style.display = 'block';

    // ------------------------------FOR COUNT TIME AGO------------------------------
    const datePosted = new Date(taskElement.dataset.dateposted);
    const now = new Date();
    const diffMs = now - datePosted;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    const diffMonths = Math.floor(diffDays / 30);
    const diffYears = Math.floor(diffDays / 365);

    let timeAgo = "Just now";
    if (diffYears > 0) timeAgo = diffYears + " year(s) ago";
    else if (diffMonths > 0) timeAgo = diffMonths + " month(s) ago";
    else if (diffDays > 0) timeAgo = diffDays + " day(s) ago";
    else if (diffHours > 0) timeAgo = diffHours + " hour(s) ago";
    else if (diffMins > 0) timeAgo = diffMins + " minute(s) ago";

    document.getElementById('modalTimeAgo').textContent = timeAgo;

    document.getElementById('taskModal').style.display = "block";
}

function closeTaskModal() {
    // Hide modal
    document.getElementById('taskModal').style.display = 'none';
}

// ------------------------------FOR SWITCH STATUS------------------------------
function updateTaskStatus(selectElement) {
    const status = selectElement.value;
    const taskID = document.getElementById('modalTaskID').value;

    // Send AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_task.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        if (xhr.status === 200) {
            alert("Task status updated to " + status);
            document.getElementById('modalStatus').innerText = status;

            // closeTaskModal();
            // location.reload();
        } else {
            alert("Error updating task status.");
        }
    };

    xhr.send("task_id=" + encodeURIComponent(taskID) + "&task_status=" + encodeURIComponent(status));
}

// ------------------------------USER------------------------------
function openUserModal() {
    document.getElementById('userModal').style.display = 'block';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Optional: Close when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

// ------------------------------FILTER TASKS------------------------------
function filterMyTasks() {
    const input = document.getElementById('taskSearchBar').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-box');

    tasks.forEach(task => {
        const title = task.getAttribute('data-title').toLowerCase();
        // Only show tasks that contain the keyword
        if (title.includes(input)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}









function fetchComments(taskId) {
    // Use AJAX to fetch comments for the specific task
    fetch(`fetch_comments.php?task_id=${taskId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('commentsSection').innerHTML = data;
        })
        .catch(error => console.error('Error fetching comments:', error));
}

function acceptComment(commentID) {
    if (confirm("Are you sure you want to accept this comment?")) {
        // Send an AJAX request to accept the comment
        fetch('accept_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentID}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success or error message
            location.reload(); // Reload the page to reflect changes
        })
        .catch(error => console.error('Error:', error));
    }
}













function openProfileModal(comment) {
    // Populate the modal with the student's details
    document.getElementById('modalProfilePicture').src = comment.ProfilePicture 
        ? "../Student/" + comment.ProfilePicture 
        : "../assets/default-avatar.png";
    document.getElementById('modalFullName').innerText = comment.FirstName + " " + comment.LastName;
    document.getElementById('modalEmail').innerText = comment.Email;
    document.getElementById('modalRole').innerText = comment.Role;
    document.getElementById('modalTrustPoints').innerText = comment.TrustPoints;

    // Show the modal
    document.getElementById('profileModal').style.display = 'block';
}

function closeProfileModal() {
    // Hide the modal
    document.getElementById('profileModal').style.display = 'none';
}





function getTimeAgo(dateString) {
    const now = new Date();
    const posted = new Date(dateString);
    const diff = Math.floor((now - posted) / 1000); // difference in seconds

    if (diff < 60) return "Posted just now";
    if (diff < 3600) return `Posted ${Math.floor(diff / 60)} minute(s) ago`;
    if (diff < 86400) return `Posted ${Math.floor(diff / 3600)} hour(s) ago`;
    if (diff < 604800) return `Posted ${Math.floor(diff / 86400)} day(s) ago`;
    
    return `Posted on ${posted.toLocaleDateString()}`;
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.posted-time').forEach(function (el) {
        const datePosted = el.getAttribute('data-dateposted');
        if (datePosted) {
            el.textContent = getTimeAgo(datePosted);
        }
    });
});