// ------------------------------HEADER------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("taskSearch");
    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();
        const taskBoxes = document.querySelectorAll(".task-box");

        taskBoxes.forEach(box => {
            const title = box.querySelector(".task-title").textContent.toLowerCase();
            if (title.includes(query)) {
                box.style.display = "block";
            } else {
                box.style.display = "none";
            }
        });
    });
});

function toggleNotificationModal() {
    const modal = document.getElementById("notificationModal");
    modal.style.display = modal.style.display === "block" ? "none" : "block";

    // i-hide ang red ! pag gi-open ang notif
    if (modal.style.display === "block") {
        document.getElementById("notificationBadge").style.display = "none";

        localStorage.setItem("notificationsViewed", "true");
    }
}

function showNotificationDetails(notification) {
    // modal for task details
    const modalContent = `
        <h4>${notification.Title}</h4>
        <p><strong>Posted By:</strong> ${notification.FirstName} ${notification.LastName}</p>
        <p><strong>Location:</strong> ${notification.Location}</p>
        <p><strong>Email:</strong> ${notification.Email}</p>
        <p><strong>Description:</strong> ${notification.Description}</p>
        <p><strong>Notes:</strong> ${notification.Notes}</p>
        <button onclick="goBackToNotificationList()" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">Back to List</button>
    `;

    const notificationModal = document.getElementById("notificationModal");
    notificationModal.innerHTML = modalContent;
}

// Restore the red badge on page refresh if notifications are not viewed
document.addEventListener("DOMContentLoaded", function () {
    const notificationsViewed = localStorage.getItem("notificationsViewed");
    if (!notificationsViewed) {
        document.getElementById("notificationBadge").style.display = "flex";
    }
});

// ------------------------------USER------------------------------
function openUserModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Close when clicking outside for user
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

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
    
    // task details modal
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
    
    document.getElementById('modalTaskID').value = taskId;
    
    document.getElementById('editTaskLink').href = 'edit_task.php?task_id=' + taskId;
    document.getElementById('deleteTaskLink').href = 'delete_task.php?task_id=' + taskId;

    // current task status in the dropdown
    var statusDropdown = document.getElementById('taskStatusDropdown');
    for (var i = 0; i < statusDropdown.options.length; i++) {
        if (statusDropdown.options[i].value === status) {
            statusDropdown.selectedIndex = i;
            break;
        }
    }

    // Show modal
    document.getElementById('taskModal').style.display = 'block';

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

// Close modals if clicked outside
window.onclick = function(event) {
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
}

// ------------------------------FOR COUNT TIME AGO------------------------------
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

function openCommunityProfileModal(profilePicture, fullName, email, role) {
    // Populate the modal with the community poster's details
    document.getElementById('communityProfilePicture').src = '../Community/' + profilePicture;
    document.getElementById('communityFullName').textContent = fullName;
    document.getElementById('communityEmail').textContent = email;
    document.getElementById('communityRole').textContent = role;

    // Show the modal
    document.getElementById('communityProfileModal').style.display = 'block';
}

function closeCommunityProfileModal() {
    // Hide the modal
    document.getElementById('communityProfileModal').style.display = 'none';
}

// Toggle the notification modal
function toggleNotificationModal() {
    const modal = document.getElementById("notificationModal");
    modal.style.display = modal.style.display === "block" ? "none" : "block";

    // Hide the red badge when the modal is opened
    if (modal.style.display === "block") {
        document.getElementById("notificationBadge").style.display = "none";

        // Save the state in localStorage
        localStorage.setItem("notificationsViewed", "true");
    }
}

// Show task details in a new modal
function showNotificationDetails(notification) {
    // Create a new modal for task details
    const modalId = "taskDetailsModal";
    let modal = document.getElementById(modalId);

    // If the modal doesn't exist, create it
    if (!modal) {
        modal = document.createElement("div");
        modal.id = modalId;
        modal.className = "modal";
        modal.style.display = "block";
        modal.style.position = "fixed";
        modal.style.top = "50%";
        modal.style.left = "50%";
        modal.style.transform = "translate(-50%, -50%)";
        modal.style.background = "white";
        modal.style.border = "1px solid #ccc";
        modal.style.borderRadius = "5px";
        modal.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
        modal.style.width = "400px";
        modal.style.zIndex = "1000";
        modal.style.padding = "20px";
        modal.style.maxHeight = "80vh";
        modal.style.overflowY = "auto";

        document.body.appendChild(modal);
    }

    // Populate the modal with task details
    modal.innerHTML = `
        <h4>${notification.Title}</h4>
        <p><strong>Posted By:</strong> ${notification.FirstName} ${notification.LastName}</p>
        <p><strong>Location:</strong> ${notification.Location}</p>
        <p><strong>Email:</strong> ${notification.Email}</p>
        <p><strong>Description:</strong> ${notification.Description}</p>
        <p><strong>Notes:</strong> ${notification.Notes}</p>
        <p>View your ongoing tasks to see full details.</p>
        <button onclick="closeTaskDetailsModal('${modalId}')" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">Close</button>
    `;

    // Show the modal
    modal.style.display = "block";
}

// Close the task details modal
function closeTaskDetailsModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "none";
    }
}