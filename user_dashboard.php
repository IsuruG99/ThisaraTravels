<?php
session_start(); // Start the session
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') { 
    // If not logged in, redirect to login page with a message
    $_SESSION['error_message'] = "User not logged in.";
    header("Location: login.php");
    exit;
}

require 'vendor/autoload.php'; // Ensure Composer's autoload is included

$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels";
$databaseName = "ThisaraTravels";

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;

    // Fetch data for dashboard
    $bookingsCollection = $database->booking; // Adjust collection name as needed
    $reviewsCollection = $database->userdata; // Adjust collection name as needed
    $messagesCollection = $database->messages; // Adjust collection name for messages
    $todosCollection = $database->usertododata; // Change to userdata collection for to-do items

    // Get the logged-in user's username
    $username = $_SESSION['username'];

    // Fetch counts for the logged-in user
    $newBookings = $bookingsCollection->countDocuments(['UserName' => $username, 'Status' => 'In Complete']); // Count new bookings for the user
    $newReviews = $reviewsCollection->countDocuments(['UserName' => $username]); // Count new reviews for the user
    $newMessages = $messagesCollection->countDocuments(['recipient' => $username, 'read' => false]); // Count new unread messages for the user

    // Fetch recent bookings for the logged-in user
    $recentBookings = $bookingsCollection->find(
        ['UserName' => $username], // Filter by logged-in user's username
        ['sort' => ['BookingDate' => -1], 'limit' => 5] // Sort and limit results
    );

    // Fetch to-do items from the userdata collection for the logged-in user
    $todos = $todosCollection->find(['UserName' => $username]); // Fetch only the user's todos

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['action']) && $_POST['action'] === 'get_notifications') {
            // Return the new bookings notification count as a JSON response
            echo json_encode([
                'newNotifications' => $newBookings,
                'newReviews' => $newReviews,
                'newMessages' => $newMessages
            ]);
            exit;
        }

        if (isset($_POST['add_todo'])) {
            // Add new to-do
            $todoText = trim($_POST['todo_text']);
            $todoDate = $_POST['todo_date'];

            if (!empty($todoText) && !empty($todoDate)) {
                $document = [
                    'UserName' => $username, // Save the username for reference
                    'text' => $todoText,
                    'date' => $todoDate, // Store the date
                    'completed' => false // Default status
                ];
                $insertResult = $todosCollection->insertOne($document);

                if ($insertResult->getInsertedCount() === 1) {
                    header("Location: user_dashboard.php"); // Redirect to the same page to refresh
                    exit;
                } else {
                    echo "Error: Unable to add to-do.";
                }
            }
        } elseif (isset($_POST['remove_todo'])) {
            // Remove to-do
            $todoId = new MongoDB\BSON\ObjectId($_POST['todo_id']);

            $deleteResult = $todosCollection->deleteOne(['_id' => $todoId]);

            if ($deleteResult->getDeletedCount() === 1) {
                header("Location: user_dashboard.php"); // Redirect to the same page to refresh
                exit;
            } else {
                echo "Error: Unable to remove to-do.";
            }
        }
    }
	
	// Fetch booking updates for the user (accepted/completed bookings)
$bookingUpdates = $bookingsCollection->find(
    ['UserName' => $username, '$or' => [['Status' => 'Accepted'], ['Status' => 'Completed']]],
    ['sort' => ['BookingDate' => -1], 'limit' => 5] // Limit the number of notifications shown
);

// Store the booking updates in a session
$_SESSION['booking_updates'] = iterator_to_array($bookingUpdates);


} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" type="text/css" href="dashboardstyle.css"
		 
    <title>Profile</title>
</head>
<body>
    <h1 class="center-text">Welcome to the User Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bx-user'></i>
            <span class="text">UserDashboard</span>
        </a>
        <ul class="side-menu top">
            <li class="active" id="dashboard-link">
                <a href="#">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li id="booking-link">
                <a href="#">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Booking</span>
                </a>
            </li>
            <li id="reviews-link">
                <a href="#">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Reviews</span>
                </a>
            </li>
            <li id="messages-link">
                <a href="#">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Messages</span>
                </a>
            </li>
			 <!-- SIDEBAR 
            <li id="users-link">
                <a href="#">
                    <i class='bx bxs-group'></i>
                    <span class="text">Users</span>
                </a>
            </li>-->
        </ul>
        <ul class="side-menu">
            <li id="settings-link">
                <a href="#">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="nav-item nav-link logout-button">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- END SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
        
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
<a href="#" class="notification" id="notificationBell">
    <i class='bx bxs-bell'></i>
    <span class="num" id="notificationCount"><?php echo $newBookings; ?></span> <!-- Show new bookings count -->
</a>

<div id="notificationList" class="notification-list" style="display:none;">
    <ul>
        <?php
        if (isset($_SESSION['booking_updates']) && count($_SESSION['booking_updates']) > 0) {
            foreach ($_SESSION['booking_updates'] as $update) {
                echo "<li>{$update['BookingDate']} - {$update['Status']}</li>";
            }
        } else {
            echo "<li>No new booking updates.</li>";
        }
        ?>
    </ul>
</div>


            <a href="index.php" class="profile">
                <img id="navbar-profile-photo" src="default_photo_path.jpg" alt="Profile Photo" style="max-width: 60px; max-height: 40px;">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div id="dashboard-content" class="content-section active">
                <div class="head-title">
                    <div class="left">
                        <h1>Dashboard</h1>
                        <ul class="breadcrumb">
                            <li><a href="#">Dashboard</a></li>
                            <li><i class='bx bx-chevron-right'></i></li>
                            <li><a class="active" href="#">Home</a></li>
                        </ul>
                    </div>
                </div>

<ul class="box-info">
    <li>
        <i class='bx bxs-calendar-check'></i>
        <span class="text">
            <h3><?php echo $newBookings; ?></h3>
            <p>New Bookings</p>
        </span>
    </li>
    <li>
        <i class='bx bxs-group'></i>
        <span class="text">
            <h3><?php echo $newReviews; ?></h3>
            <p>New Reviews</p>
        </span>
    </li>
<li>
        <i class='bx bxs-message'></i> <!-- Updated icon for messages -->
        <span class="text">
            <h3><?php echo $newMessages; ?></h3> <!-- New Messages count -->
            <p>New Messages</p>
        </span>
    </li>
</ul>

                <div class="table-data">
                    <div class="order">
                        <div class="head">
                            <h3>Recent Bookings</h3>
                            <i class='bx bx-search'></i>
                            <i class='bx bx-filter'></i>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date Order</th>
                                    <th>Status</th>
                                    
                                </tr>
                            </thead>
<tbody>
    <?php foreach ($recentBookings as $booking): ?>
    <tr>
        <td><?php echo htmlspecialchars($booking->UserName); ?></td>
        <td><?php echo htmlspecialchars($booking->BookingDate); ?></td>
        <td>
            <span class="status <?php echo htmlspecialchars($booking->Status === 'Complete' ? 'complete' : 'incomplete'); ?>">
                <?php echo htmlspecialchars($booking->Status); ?>
            </span>
        </td>

    </tr>
    <?php endforeach; ?>
</tbody>

                        </table>
                </div>
    <div class="todo">
    <h2>To do</h2>
    <div class="head">
        <form action="user_dashboard.php" method="POST" class="add-todo-form">
            <input type="text" name="todo_text" placeholder="Add new to-do" required>
            <input type="date" name="todo_date" required> <!-- Date input field -->
            <button type="submit" name="add_todo"><i class='bx bx-plus'></i> Add</button>
        </form>
    </div>
    <ul class="todo-list">
        <?php foreach ($todos as $todo): ?>
        <li class="<?php echo $todo['completed'] ? 'completed' : 'not-completed'; ?>">
            <p><?php echo htmlspecialchars($todo['text']); ?></p>
            <small><?php echo isset($todo['date']) ? htmlspecialchars($todo['date']) : "No Date"; ?></small> <!-- Display the date or a default message -->
            <form action="user_dashboard.php" method="POST" class="remove-todo-form" style="display:inline;">
                <input type="hidden" name="todo_id" value="<?php echo $todo['_id']; ?>">
                <button type="submit" name="remove_todo" class="delete-button">
    <i class="bx bx-trash"></i> <!-- Add this for the delete icon -->
</button>

            </form>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

				</div>
			</div>
			<script>
document.getElementById("notificationBell").addEventListener("click", function() {
    // Hide the notification count and show the list
    var notificationList = document.getElementById("notificationList");
    notificationList.style.display = (notificationList.style.display === "none") ? "block" : "none";

    // Decrease the notification count after clicking
    var notificationCount = document.getElementById("notificationCount");
    if (parseInt(notificationCount.innerText) > 0) {
        notificationCount.innerText = 0; // Reset notification count
        // Optionally, make an AJAX request to update the notification count in the session
        fetch('update_notifications.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'read_notifications' })
        }).then(response => response.json()).then(data => {
            console.log("Notifications marked as read.");
        });
    }
});
</script>

<!-- Booking Content -->
<div id="booking-content" class="content-section">
    <div class="head-title">
        <div class="left">
            <h1>Booking</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Booking</a></li>
            </ul>
        </div>
        <!-- Add the Generate PDF button here -->
        <div class="right">
            <button id="generate-bookingpdf" class="btn-generate-pdf">Generate PDF</button>
        </div>
    </div>


    <!-- Booking Cards Container -->
    <div class="booking-cards-container">
        <!-- Booking cards will be dynamically inserted here -->
    </div>
</div>



<!-- Reviews Content -->
<div id="reviews-content" class="content-section">
    <!-- Header Section -->
    <div class="head-title">
        <div class="left">
            <h1>Reviews</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Reviews</a></li>
            </ul>
        </div>
        <!-- Add the Generate PDF button here -->
        <div class="right">
            <button id="generate-reviewpdf" class="btn-generate-pdf">Generate PDF</button>
        </div>
    </div>
    
    <!-- Reviews Cards Container -->
    <div class="reviews-cards-container">
        <!-- Reviews data will be dynamically inserted here -->
    </div>
</div>



<!-- Messages Content -->
<div id="messages-content" class="content-section">
    <!-- Messages data will go here -->
    <div class="head-title">
        <div class="left">
            <h1>Messages</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Messages</a></li>
            </ul>
        </div>

    </div>
    <div class="messages-data">
        <!-- Messages will be loaded here -->
    </div>
</div>



    <!-- Users Content -->
<div id="users-content" class="content-section">
    <!-- Users data will go here -->
    <div class="head-title">
        <div class="left">
            <h1>Users</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Users</a></li>
            </ul>
        </div>
    </div>
    <div class="users-data"> <!-- User cards will be dynamically appended here --> </div>
</div>

<div id="settings-content" class="content-section">
    <div class="head-title">
        <div class="left">
            <h1>Settings</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Settings</a></li>
            </ul>
        </div>
    </div>

    <div class="settings-data">
        <!-- user Info Card -->
        <div class="admin-info-card">
            <h2>Admin Information</h2>
            <p>Update your username, email, password, or profile photo.</p>

            <!-- Current Information -->
<div class="current-info">
    <p><strong>Current Username:</strong> <span id="current-username"></span></p>
    <p><strong>Current Email:</strong> <span id="current-email"></span></p>
    <p><strong>Current Profile Photo:</strong></p>
    <img id="profile-photo" src="default_photo_path.jpg" alt="Profile Photo" style="max-width: 100px; max-height: 100px;">
</div>

            <!-- Update Form -->
            <form id="update-settings-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">New Username:</label>
                    <input type="text" name="username" placeholder="Don't use space">
                    <span class="error-message" style="color:red;"></span> <!-- Error message for username -->
                </div>

                <!-- Profile Photo Upload -->
                <div class="form-group">
                    <label for="profile-photo">Upload New Profile Photo:</label>
                    <input type="file" name="profilePhoto" accept="image/*">
                    <span class="error-message" style="color:red;"></span> <!-- Error message for profile photo -->
                </div>

                <!-- Password Fields -->
                <div class="form-group">
                    <div>
                        <label for="current-password">Current Password:</label>
                        <input type="password" name="current-password" required placeholder="Enter Current Password">
                        <span class="error-message" style="color:red;"></span> <!-- Error message for current password -->
                    </div>
                    <div>
                        <label for="password">New Password:</label>
                        <input type="password" name="password" placeholder="Enter New Password">
                        <span class="error-message" style="color:red;"></span> <!-- Error message for new password -->
                    </div>
                    <div>
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" name="confirm-password" placeholder="Enter Again Password">
                        <span class="error-message" style="color:red;"></span> <!-- Error message for confirm password -->
                    </div>
                </div>
					<p>Enter Current Password Before Update Setting</p>
                <button type="submit">Update Settings</button>
            </form>
        </div>
    </div>
</div>











	
<style>

/* SIDEBAR */
#sidebar {
	position: fixed;
	top: 0;
	left: 0;
	width: 280px;
	height: 100%;
	background: var(--light);
	z-index: 2000;
	font-family: var(--lato);
	transition: .3s ease;
	overflow-x: hidden;
	scrollbar-width: none;
}
#sidebar::--webkit-scrollbar {
	display: none;
}
#sidebar.hide {
	width: 60px;
}
#sidebar .brand {
	font-size: 24px;
	font-weight: 700;
	height: 56px;
	display: flex;
	align-items: center;
	color: var(--blue);
	position: sticky;
	top: 0;
	left: 0;
	background: var(--light);
	z-index: 500;
	padding-bottom: 20px;
	box-sizing: content-box;
}
#sidebar .brand .bx {
	min-width: 60px;
	display: flex;
	justify-content: center;
}
#sidebar .side-menu {
	width: 100%;
	margin-top: 48px;
}
#sidebar .side-menu li {
	height: 48px;
	background: transparent;
	margin-left: 6px;
	border-radius: 48px 0 0 48px;
	padding: 4px;
}
#sidebar .side-menu li.active {
	background: var(--grey);
	position: relative;
}
#sidebar .side-menu li.active::before {
	content: '';
	position: absolute;
	width: 40px;
	height: 40px;
	border-radius: 50%;
	top: -40px;
	right: 0;
	box-shadow: 20px 20px 0 var(--grey);
	z-index: -1;
}
#sidebar .side-menu li.active::after {
	content: '';
	position: absolute;
	width: 40px;
	height: 40px;
	border-radius: 50%;
	bottom: -40px;
	right: 0;
	box-shadow: 20px -20px 0 var(--grey);
	z-index: -1;
}
#sidebar .side-menu li a {
	width: 100%;
	height: 100%;
	background: var(--light);
	display: flex;
	align-items: center;
	border-radius: 48px;
	font-size: 16px;
	color: var(--dark);
	white-space: nowrap;
	overflow-x: hidden;
}
#sidebar .side-menu.top li.active a {
	color: var(--blue);
}
#sidebar.hide .side-menu li a {
	width: calc(48px - (4px * 2));
	transition: width .3s ease;
}
#sidebar .side-menu li a.logout {
	color: var(--red);
}
#sidebar .side-menu.top li a:hover {
	color: var(--blue);
}
#sidebar .side-menu li a .bx {
	min-width: calc(60px  - ((4px + 6px) * 2));
	display: flex;
	justify-content: center;
}
/* SIDEBAR */

			</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>


<script>
// Highlight Active Sidebar Menu Item
const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item => {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i => i.parentElement.classList.remove('active'));
        li.classList.add('active');
    });
});

// Sidebar Toggle (Only for Normal View)
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleButton = document.querySelector('.sidebar-toggle');
    const menuBar = document.querySelector('#content nav .bx.bx-menu');
    const content = document.getElementById('content');

    // Function to handle sidebar toggle
    const handleSidebarToggle = () => {
        const isMobileView = window.innerWidth <= 768;

        if (!isMobileView) {
            // Enable toggle for normal view
            menuBar.addEventListener('click', () => {
                sidebar.classList.toggle('hide');
            });

            toggleButton.addEventListener('click', () => {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('sidebar-active');
            });

            // Reset margin for normal view
            content.style.marginLeft = '-10px';
            sidebar.classList.remove('hide'); // Ensure sidebar is visible
        } else {
            // Disable toggle in mobile view
            sidebar.classList.add('hide'); // Sidebar always hidden
            content.style.marginLeft = '0'; // Reset margin for mobile view

            // Remove toggle event listeners to prevent toggle
            menuBar.replaceWith(menuBar.cloneNode(true)); // Remove all listeners
            toggleButton.replaceWith(toggleButton.cloneNode(true)); // Remove all listeners
        }
    };

    // Initialize sidebar toggle behavior
    handleSidebarToggle();

    // Re-check toggle behavior on window resize
    window.addEventListener('resize', handleSidebarToggle);
});

// Search Bar Toggle for Mobile View
const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
    if (window.innerWidth < 576) {
        e.preventDefault();
        searchForm.classList.toggle('show');
        if (searchForm.classList.contains('show')) {
            searchButtonIcon.classList.replace('bx-search', 'bx-x');
        } else {
            searchButtonIcon.classList.replace('bx-x', 'bx-search');
        }
    }
});

if (window.innerWidth < 768) {
    sidebar.classList.add('hide');
} else if (window.innerWidth > 576) {
    searchButtonIcon.classList.replace('bx-x', 'bx-search');
    searchForm.classList.remove('show');
}

window.addEventListener('resize', function () {
    if (window.innerWidth >= 576) {
        searchForm.classList.remove('show');
        searchButtonIcon.classList.replace('bx-x', 'bx-search');
    }
});

// Dark Mode Toggle
// Select the dark mode toggle checkbox
const switchMode = document.getElementById('switch-mode');

// Check and apply the user's previous dark mode preference on page load
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark');
    switchMode.checked = true; // Ensure the toggle is in the correct state
} else {
    document.body.classList.remove('dark');
    switchMode.checked = false;
}

// Listen for changes to the checkbox
switchMode.addEventListener('change', function () {
    if (this.checked) {
        // Enable dark mode
        document.body.classList.add('dark');
        localStorage.setItem('darkMode', 'enabled'); // Save preference
    } else {
        // Disable dark mode
        document.body.classList.remove('dark');
        localStorage.setItem('darkMode', 'disabled'); // Save preference
    }
});

	
	
document.addEventListener('DOMContentLoaded', () => {
    const fetchAndDisplayBookings = async () => {
        try {
            const response = await fetch('fetch_userbookings.php');
            const data = await response.json();
            console.log('Fetched bookings data:', data); // Log fetched data for debugging

            const bookingCardsContainer = document.querySelector('#booking-content .booking-cards-container');
            bookingCardsContainer.innerHTML = ''; // Clear existing content
            
            if (data.bookings && data.bookings.length > 0) {
                data.bookings.forEach(booking => {
                    const card = document.createElement('div');
                    card.classList.add('booking-card-container');

                    card.innerHTML = `
                        <div class="booking-card" data-id="${booking.id}">
                            <div class="booking-header">
                                <h3>Booking ID: ${booking.id}</h3>
                                <span class="status ${booking.status === 'Complete' ? 'status-complete' : 'status-incomplete'}">${booking.status}</span>
                                <span class="acceptance ${booking.acceptance === 'Accepted' ? 'acceptance-accepted' : 'acceptance-not-accepted'}">${booking.acceptance}</span>
                            </div>
                            <div class="booking-details">
                                <p><strong>Name:</strong> ${booking.name}</p>
                                <p><strong>Email:</strong> ${booking.email}</p>
                                <p><strong>Phone:</strong> ${booking.phone}</p>
                                <p><strong>Pickup Location:</strong> ${booking.pickupLocation}</p>
                                <p><strong>Drop-off Location:</strong> ${booking.dropoffLocation}</p>
                                <p><strong>Pickup Date:</strong> ${booking.pickupDate}</p>
                                <p><strong>Drop-off Date:</strong> ${booking.dropoffDate}</p>
                                <p><strong>Pickup Time:</strong> ${booking.pickupTime}</p>
                                <p><strong>Special Requests:</strong> ${booking.specialRequest}</p>
                            </div>
                            <div class="card-actions">
                                <button class="btn-delete" data-id="${booking.id}">Delete</button>
                            </div>
                            <div class="confirm-message" style="display: none;">
                                <p></p>
                                <button class="btn-confirm-yes">Yes</button>
                                <button class="btn-confirm-no">No</button>
                            </div>
                        </div>
                    `;

                    bookingCardsContainer.appendChild(card);
                });
            } else {
                bookingCardsContainer.innerHTML = '<p class="no-booking-message">No bookings available.</p>';
            }

        } catch (error) {
            console.error('Error fetching bookings:', error);
        }
    };

const handleCardActions = (e) => {
    const card = e.target.closest('.booking-card-container'); // Find the closest booking card

    // Check if the card exists
    if (!card) return; 

    const confirmMessage = card.querySelector('.confirm-message'); // Get the confirmation message element

    // Check if confirmMessage exists
    if (!confirmMessage) return;

    const bookingId = e.target.dataset.id; // Ensure correct booking ID is used

    // Check if the delete button was clicked
    if (e.target.classList.contains('btn-delete')) {
        const id = e.target.dataset.id;

        confirmMessage.style.display = 'block';
        confirmMessage.querySelector('p').textContent = 'Are you sure you want to delete this booking?';

        confirmMessage.querySelector('.btn-confirm-yes').onclick = async () => {
            try {
                const response = await fetch('fetch_bookings.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await response.json();
                if (result.success) {
                    alert('Booking deleted successfully!');
                    confirmMessage.style.display = 'none';
                    fetchAndDisplayBookings(); // Refresh booking list
                } else {
                    alert('Error deleting booking: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting booking:', error);
            }
        };

        confirmMessage.querySelector('.btn-confirm-no').onclick = () => {
            confirmMessage.style.display = 'none';
        };
    }
};

// Ensure to bind the event listener after the DOM is fully loaded
document.querySelector('#booking-content').addEventListener('click', handleCardActions);


    const sidebarMenuItems = document.querySelectorAll('#sidebar .side-menu li');
    sidebarMenuItems.forEach(item => {
        item.addEventListener('click', () => {
            sidebarMenuItems.forEach(el => el.classList.remove('active'));
            item.classList.add('active');

            const sections = {
                dashboardContent: document.getElementById('dashboard-content'),
                bookingContent: document.getElementById('booking-content'),
                reviewsContent: document.getElementById('reviews-content'),
                messagesContent: document.getElementById('messages-content'),
                usersContent: document.getElementById('users-content'),
                settingsContent: document.getElementById('settings-content'),
            };

            Object.values(sections).forEach(section => section.classList.remove('active'));

            switch (item.textContent.trim()) {
                case 'Dashboard':
                    sections.dashboardContent.classList.add('active');
                    break;
                case 'Booking':
                    sections.bookingContent.classList.add('active');
                    fetchAndDisplayBookings();
                    break;
                case 'Reviews':
                    sections.reviewsContent.classList.add('active');
                    break;
                case 'Messages':
                    sections.messagesContent.classList.add('active');
                    break;
                case 'Users':
                    sections.usersContent.classList.add('active');
                    break;
                case 'Settings':
                    sections.settingsContent.classList.add('active');
                    break;
            }
        });
    });
// PDF generation function with enhanced layout and styling for User Dashboard
const generatePDF = () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Set Title
    doc.setFontSize(18);
    doc.setTextColor(40, 116, 166); // Attractive blue color for title
    doc.text("Booking Report", 105, 20, null, null, "center");

    // Define card styling variables
    let y = 40; // Initial Y position for the first card
    const cardHeight = 70; // Adjusted height to accommodate additional fields
    const pageWidth = doc.internal.pageSize.getWidth();
    const cardMargin = 10; // Left-right margin for the cards

    // Get Booking Data
    const bookingCardsContainer = document.querySelector('#booking-content .booking-cards-container');
    const bookings = Array.from(bookingCardsContainer.children).map(card => {
        const id = card.querySelector('.booking-card').dataset.id;
        const name = card.querySelector('.booking-details p:nth-of-type(1)').textContent.split(': ')[1].trim();
        const email = card.querySelector('.booking-details p:nth-of-type(2)').textContent.split(': ')[1].trim();
        const phone = card.querySelector('.booking-details p:nth-of-type(3)').textContent.split(': ')[1].trim();
        const pickupLocation = card.querySelector('.booking-details p:nth-of-type(4)').textContent.split(': ')[1].trim();
        const dropoffLocation = card.querySelector('.booking-details p:nth-of-type(5)').textContent.split(': ')[1].trim();
        const pickupDate = new Date(card.querySelector('.booking-details p:nth-of-type(6)').textContent.split(': ')[1].trim());
        const pickupTime = card.querySelector('.booking-details p:nth-of-type(8)').textContent.split(': ')[1].trim();
        const specialRequest = card.querySelector('.booking-details p:nth-of-type(9)').textContent.split(': ')[1].trim();
        const status = card.querySelector('.status').textContent.trim();
        const acceptance = card.querySelector('.acceptance').textContent.trim();

        return {
            id,
            name,
            email,
            phone,
            pickupLocation,
            dropoffLocation,
            pickupDate: pickupDate.toLocaleDateString(),
            pickupTime,
            specialRequest,
            status,
            acceptance
        };
    }).filter(Boolean); // Filter out undefined results

    if (bookings.length === 0) {
        alert("No bookings available.");
        return;
    }

    bookings.forEach(booking => {
        // Check if we need to create a new page
        if (y + cardHeight > doc.internal.pageSize.getHeight()) {
            doc.addPage();
            y = 20; // Reset Y position for new page
        }

        // Draw a rectangle for the card background
        doc.setFillColor(240, 240, 240); // Light grey background for the card
        doc.rect(cardMargin, y, pageWidth - 2 * cardMargin, cardHeight, 'F'); // Card dimensions

        // Set card text (Booking Details)
        doc.setTextColor(0); // Black text for card content
        doc.setFontSize(12);
        doc.text(`Booking ID: ${booking.id}`, cardMargin + 5, y + 10);
        doc.text(`Name: ${booking.name}`, cardMargin + 5, y + 20);
        doc.text(`Email: ${booking.email}`, cardMargin + 5, y + 30);
        doc.text(`Phone: ${booking.phone}`, cardMargin + 5, y + 40);
        doc.text(`Pickup: ${booking.pickupLocation}`, cardMargin + 85, y + 20);
        doc.text(`Dropoff: ${booking.dropoffLocation}`, cardMargin + 85, y + 30);
        doc.text(`Pickup Date: ${booking.pickupDate}`, cardMargin + 85, y + 40);
        doc.text(`Pickup Time: ${booking.pickupTime}`, cardMargin + 5, y + 50);
        doc.text(`Status: ${booking.status}`, cardMargin + 85, y + 50); // Added status
        doc.text(`Special Request: ${booking.specialRequest}`, cardMargin + 85, y + 60);
        doc.text(`Acceptance: ${booking.acceptance}`, cardMargin + 5, y + 60); // Added acceptance

        // Move Y for the next card
        y += cardHeight + 10; // Add extra padding between cards
    });

    // Footer Text (e.g., Page Number)
    doc.setFontSize(10);
    doc.setTextColor(150);
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 290); // Footer text at the bottom

    // Save the PDF
    doc.save("booking_report.pdf");
};

// Event listener for the PDF generation button
const pdfButton = document.getElementById('generate-bookingpdf');
if (pdfButton) { // Check if the button exists
    pdfButton.addEventListener('click', generatePDF);
}



});


//review section
 document.addEventListener('DOMContentLoaded', function () {
            const reviewsContainer = document.querySelector('.reviews-cards-container');
            const pdfButton = document.getElementById('generate-reviewpdf');

            // Fetch reviews data and display them
            async function fetchReviews() {
                const response = await fetch('fetch_userreviews.php');
                const reviews = await response.json();
                
                reviewsContainer.innerHTML = ''; // Clear existing reviews

                if (reviews.length === 0) {
                    // No reviews available message
                    const noReviewsMessage = document.createElement('p');
                    noReviewsMessage.textContent = 'No Reviews available.';
                    noReviewsMessage.classList.add('no-reviews-message'); // Optional: add a class for styling
                    reviewsContainer.appendChild(noReviewsMessage);
                    return; // Exit the function early since there are no reviews
                }

                reviews.forEach(review => {
                    const reviewCard = document.createElement('div');
                    reviewCard.classList.add('review-card');
                    reviewCard.innerHTML = `
                        <h3>${review.UserName}</h3>
                        <p class="review-date">${new Date(review.date).toLocaleString()}</p>
                        <p class="review-comment">${review.Comment}</p>
                        <div class="rating-stars">${'&#9733;'.repeat(review.ReviewCount)}</div>
                        <div class="like-section">
                            <i class="fas fa-heart"></i> ${review.likeCount}
                        </div>
                        <button class="edit-btn" data-id="${review.id}">Edit</button>
                        <button class="delete-btn" data-id="${review.id}">Delete</button>
                        <div class="edit-section" style="display: none;">
                            <textarea class="edit-comment">${review.Comment}</textarea>
                            <button class="save-edit" data-id="${review.id}">Save</button>
                            <button class="cancel-edit">Cancel</button>
                        </div>
                        <div class="confirmation" style="display: none;">
                            <p>Are you sure you want to delete this review?</p>
                            <div class="confirmation-buttons">
                                <button class="confirm-delete" data-id="${review.id}">Yes</button>
                                <button class="cancel-delete">No</button>
                            </div>
                        </div>
                    `;
                    reviewsContainer.appendChild(reviewCard);
                });
            }

 // PDF Generation Logic
pdfButton.addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Title
    doc.setFontSize(18);
    doc.setTextColor(40, 116, 166); // Attractive blue color for title
    doc.text('Your User Reviews', 105, 20, null, null, 'center');

    // Define card styling variables
    let yPosition = 30; // Initial Y position for the first review
    const cardHeight = 60; // Adjusted height to accommodate additional fields
    const pageWidth = doc.internal.pageSize.getWidth();
    const cardMargin = 10; // Left-right margin for the cards

    // Loop through each review and add to the PDF
    const reviewCards = document.querySelectorAll('.review-card');
    if (reviewCards.length === 0) {
        doc.text("No reviews available.", 14, yPosition);
        doc.save('user_reviews.pdf'); // Save the PDF
        return; // Exit if there are no reviews
    }

    reviewCards.forEach((reviewCard, index) => {
        // Check if we need to create a new page
        if (yPosition + cardHeight > doc.internal.pageSize.getHeight()) {
            doc.addPage();
            yPosition = 20; // Reset Y position for new page
        }

        // Draw a rectangle for the card background
        doc.setFillColor(240, 240, 240); // Light grey background for the card
        doc.rect(cardMargin, yPosition, pageWidth - 2 * cardMargin, cardHeight, 'F'); // Card dimensions

        // Get review details
        const userName = reviewCard.querySelector('h3').textContent;
        const reviewDate = reviewCard.querySelector('.review-date').textContent;
        const comment = reviewCard.querySelector('.review-comment').textContent;
        const likeCount = reviewCard.querySelector('.like-section').textContent.trim();

        // Set card text (Review Details)
        doc.setTextColor(0); // Black text for card content
        doc.setFontSize(12);
        doc.text(`${index + 1}. ${userName}`, cardMargin + 5, yPosition + 10);
        doc.text(`Date: ${reviewDate}`, cardMargin + 5, yPosition + 20);
        doc.text(`Comment: ${comment}`, cardMargin + 5, yPosition + 30);
        doc.text(`Likes: ${likeCount}`, cardMargin + 5, yPosition + 40);

        // Move Y for the next review
        yPosition += cardHeight + 10; // Add extra padding between cards
    });

    // Footer Text (e.g., Generation Date)
    doc.setFontSize(10);
    doc.setTextColor(150);
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 290); // Footer text at the bottom

    // Save the PDF
    doc.save('user_reviews.pdf');
});


            // Event delegation for delete, edit, and confirmation buttons
            reviewsContainer.addEventListener('click', (event) => {
                // Show confirmation box when delete button is clicked
                if (event.target.classList.contains('delete-btn')) {
                    const confirmation = event.target.nextElementSibling.nextElementSibling;
                    confirmation.style.display = confirmation.style.display === 'none' ? 'block' : 'none';
                }

                // Handle confirmation for delete (Yes)
                if (event.target.classList.contains('confirm-delete')) {
                    const reviewId = event.target.dataset.id;

                    // Perform delete action
                    fetch('fetch_userreviews.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${encodeURIComponent(reviewId)}&delete=true`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Review deleted successfully.');
                            fetchReviews(); // Refresh reviews
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }

                // Cancel confirmation (No)
                if (event.target.classList.contains('cancel-delete')) {
                    const confirmation = event.target.closest('.confirmation');
                    confirmation.style.display = 'none'; // Hide the entire confirmation box
                }

                // Show edit section when edit button is clicked
                if (event.target.classList.contains('edit-btn')) {
                    const reviewCard = event.target.closest('.review-card');
                    const editSection = reviewCard.querySelector('.edit-section');
                    const commentElement = reviewCard.querySelector('.review-comment');
                    
                    // Display edit section and hide the original comment
                    commentElement.style.display = 'none';
                    editSection.style.display = 'block';
                }

                // Handle saving edited review
                if (event.target.classList.contains('save-edit')) {
                    const reviewId = event.target.dataset.id;
                    const editSection = event.target.closest('.edit-section');
                    const newComment = editSection.querySelector('.edit-comment').value;

                    // Perform edit action
                    fetch('fetch_userreviews.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${encodeURIComponent(reviewId)}&comment=${encodeURIComponent(newComment)}&edit=true`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Review edited successfully.');
                            fetchReviews(); // Refresh reviews
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }

                // Cancel edit
                if (event.target.classList.contains('cancel-edit')) {
                    const editSection = event.target.closest('.edit-section');
                    const reviewCard = event.target.closest('.review-card');
                    const commentElement = reviewCard.querySelector('.review-comment');

                    // Hide edit section and show the original comment again
                    editSection.style.display = 'none';
                    commentElement.style.display = 'block';
                }
            });

            // Fetch reviews on page load
            fetchReviews();
        });





//message
document.addEventListener('DOMContentLoaded', () => {
    const messagesContainer = document.querySelector('.messages-data');

    // Function to show loading indicator
    function showLoading() {
        messagesContainer.innerHTML = '<p>Loading messages...</p>';
    }

    // Function to fetch messages
    async function fetchMessages() {
        showLoading();
        try {
            const response = await fetch('fetch_usermessages.php');

            // Check if the response is ok and can be parsed
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const messages = await response.json();

            // Clear existing messages
            messagesContainer.innerHTML = '';

            // Check if any messages exist
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<p>No messages found.</p>';
                return;
            }

            // Iterate through messages and add them to the DOM
            messages.forEach(message => {
                const messageCard = document.createElement('div');
                messageCard.className = 'message-card';
                messageCard.innerHTML = `
                    <h3>${message.name} <span>${new Date(message.date).toLocaleString()}</span></h3>
                    <p>${message.message}</p>
                    <button class="reply-button" data-id="${message.id}">Reply</button>
                    <div class="reply-box" style="display: none;">
                        <textarea placeholder="Type your reply here..."></textarea>
                        <button class="send-reply-button" data-id="${message.id}">Send Reply</button>
                    </div>
                    ${message.reply ? `<div class="reply"><strong>Reply:</strong> ${message.reply}</div>` : ''}
                    <button class="delete-button" data-id="${message.id}">Delete</button>
                    <div class="confirmation" style="display: none;">
                        <p>Are you sure you want to delete this message?</p>
                        <div class="confirmation-buttons">
                            <button class="confirm-delete" data-id="${message.id}">Yes</button>
                            <button class="cancel-delete">No</button>
                        </div>
                    </div>
                `;
                messagesContainer.appendChild(messageCard);
            });
        } catch (error) {
            console.error('Error fetching messages:', error);
            messagesContainer.innerHTML = '<p>Error loading messages. Please try again later.</p>';
        }
    }

    // Reusable function to send POST requests
    async function sendPostRequest(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { status: 'error', message: 'An error occurred.' };
        }
    }

    // Event delegation for reply and delete buttons
    messagesContainer.addEventListener('click', async (event) => {
        // Reply button toggle
        if (event.target.classList.contains('reply-button')) {
            const replyBox = event.target.nextElementSibling;
            replyBox.style.display = replyBox.style.display === 'none' ? 'block' : 'none';
        }

        // Send reply
        if (event.target.classList.contains('send-reply-button')) {
            const messageId = event.target.dataset.id;
            const replyText = event.target.previousElementSibling.value.trim();

            if (!replyText) {
                alert('Please enter a reply.');
                return;
            }

            // Disable the button to prevent multiple submissions
            event.target.disabled = true;

            const data = await sendPostRequest('reply_message.php', { id: messageId, reply: replyText });

            if (data.status === 'success') {
                alert('Reply sent successfully.');
                fetchMessages(); // Refresh messages
            } else {
                alert('Error: ' + data.message);
            }

            event.target.disabled = false;
        }

        // Delete confirmation
        if (event.target.classList.contains('delete-button')) {
            const confirmation = event.target.nextElementSibling;
            confirmation.style.display = confirmation.style.display === 'none' ? 'block' : 'none';
        }

        // Confirm delete
        if (event.target.classList.contains('confirm-delete')) {
            const messageId = event.target.dataset.id;

            const data = await sendPostRequest('fetch_usermessages.php', { delete_id: messageId });

            if (data.status === 'success') {
                alert('Message deleted successfully.');
                fetchMessages(); // Refresh messages
            } else {
                alert('Error: ' + data.message);
            }
        }

        // Cancel delete
        if (event.target.classList.contains('cancel-delete')) {
            const confirmation = event.target.closest('.confirmation');
            confirmation.style.display = 'none';
        }
    });

    // Fetch messages on page load
    fetchMessages();
});







//users_section	
document.addEventListener('DOMContentLoaded', () => {
    const usersContainer = document.querySelector('.users-data');

    // Function to fetch users and display cards
    async function fetchUsers() {
        const response = await fetch('fetch_users.php');
        const users = await response.json();
        
        usersContainer.innerHTML = ''; // Clear existing user cards

        users.forEach(user => {
            const userCard = document.createElement('div');
            userCard.className = 'user-card';
            userCard.innerHTML = `
                <h3>${user.name}</h3>
                <p><strong>Email:</strong> ${user.email}</p>
                <p><strong>Username:</strong> ${user.username}</p>
                <p><strong>Role:</strong> ${user.role}</p>
                <p><strong>Register Date:</strong> ${user.date}</p>

                <!-- Edit role functionality -->
                <button class="edit-role-button" data-id="${user.id}">Edit Role</button>
                <div class="edit-role">
                    <select class="role-select" data-id="${user.id}">
                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>admin</option>
                        <option value="user" ${user.role === 'user' ? 'selected' : ''}>user</option>
                    </select>
                    <button class="save-role-button" data-id="${user.id}">Save</button>
                </div>

                <!-- Delete functionality -->
                <button class="delete-user-button" data-id="${user.id}">Delete</button>
                <div class="confirmation">
                    <p>Are you sure you want to delete this user?</p>
                    <div class="confirmation-buttons">
                        <button class="confirm-delete" data-id="${user.id}">Yes</button>
                        <button class="cancel-delete">No</button>
                    </div>
                </div>
            `;
            usersContainer.appendChild(userCard);
        });
    }

    // Event delegation for edit, save, and delete buttons
usersContainer.addEventListener('click', (event) => {
    const target = event.target;

    // Toggle role edit section visibility
    if (target.classList.contains('edit-role-button')) {
        const editRoleSection = target.nextElementSibling;
        editRoleSection.style.display = editRoleSection.style.display === 'block' ? 'none' : 'block';
    }

    // Show confirmation box when delete button is clicked
    if (target.classList.contains('delete-user-button')) {
        const confirmationBox = target.nextElementSibling; // Get the confirmation box
        confirmationBox.style.display = 'block'; // Show confirmation box
    }

    // Handle confirm delete action
    if (target.classList.contains('confirm-delete')) {
        const userId = target.dataset.id;

        // Perform delete action
        fetch('fetch_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully.');
                fetchUsers(); // Refresh user list
            } else {
                alert('Error: ' + data.error);
            }
        });
    }

    // Handle cancel delete action
    if (target.classList.contains('cancel-delete')) {
        const confirmationBox = target.closest('.confirmation'); // Get the closest confirmation box
        confirmationBox.style.display = 'none'; // Hide the confirmation box
    }

    // Handle save role action
    if (target.classList.contains('save-role-button')) {
        const userId = target.dataset.id;
        const roleSelect = target.previousElementSibling; // Get the select element for the role
        const newRole = roleSelect.value; // Get the selected value

        // Perform update action
        fetch('fetch_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userId, role: newRole }) // Send user ID and new role
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User role updated successfully.');
                fetchUsers(); // Refresh user list
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
});

    // Fetch users on page load
    fetchUsers();
});

	
//user_setting_section	
document.addEventListener('DOMContentLoaded', () => {
    const usernameSpan = document.getElementById('current-username');
    const emailSpan = document.getElementById('current-email');
    const profilePhotoImg = document.getElementById('profile-photo'); // ID of the image tag showing the profile photo
	const navbarProfilePhoto = document.getElementById('navbar-profile-photo'); // For navbar
    const updateForm = document.getElementById('update-settings-form');

    // Function to fetch current settings
    async function fetchCurrentSettings() {
        try {
            const response = await fetch('profile_settings.php');
            const data = await response.json();

            if (data.success) {
                usernameSpan.textContent = data.username; 
                emailSpan.textContent = data.email; 
                if (data.profilePhoto) {
                    profilePhotoImg.src = data.profilePhoto; // Update the image source if available
					navbarProfilePhoto.src = data.profilePhoto; // Update the navbar image source
                } else {
                    profilePhotoImg.src = 'default_photo_path.jpg'; // Default photo if none
					navbarProfilePhoto.src = 'default_photo_path.jpg'
                }
            } else {
                displayError(data.error);
            }
        } catch (error) {
            console.error('Error fetching settings:', error);
            displayError('Failed to fetch current settings. Please try again later.');
        }
    }

    // Handle form submission
    updateForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(updateForm); // Use FormData to automatically include file data

        // Clear previous error messages
        clearErrorMessages();

        // Validate that new password and confirm password match
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm-password');
        if (password && password !== confirmPassword) {
            document.querySelector('input[name="password"] + .error-message').textContent = 'New password and confirmation password do not match.';
            return;
        }

        try {
            const response = await fetch('profile_settings.php', {
                method: 'POST',
                body: formData // Send the FormData directly to include file uploads
            });

            const data = await response.json();
            if (data.success) {
                alert('Settings updated successfully!');
                fetchCurrentSettings(); // Refresh settings

                // Clear form fields after successful update
                updateForm.reset(); // Resets the form fields
            } else {
                displayError(data.error);
            }
        } catch (error) {
            console.error('Error updating settings:', error);
            displayError('Failed to update settings. Please try again later.');
        }
    });

    // Function to display error messages
    function displayError(message) {
        if (message.includes('username already in use')) {
            document.querySelector('input[name="username"] + .error-message').textContent = message;
        } else if (message.includes('Email already in use')) {
            document.querySelector('input[name="email"] + .error-message').textContent = message;
        } else {
            // Display the error in the current password field for general errors
            document.querySelector('input[name="current-password"] + .error-message').textContent = message;
        }
    }

    // Function to clear previous error messages
    function clearErrorMessages() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach((error) => {
            error.textContent = '';
        });
    }

    // Fetch current settings on page load
    fetchCurrentSettings();
});
	



</script>
</body>
</html>