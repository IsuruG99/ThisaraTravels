<?php
require 'adminBackend.php';

// Fetch statistics from MongoDB
$totalVehicles = getVehiclesCollection()->countDocuments();
$totalBookings = getBookingsCollection()->countDocuments();
$totalUsers = getUsersCollection()->countDocuments();
$totalReviews = getReviewsCollection()->countDocuments();

// Fetch recent vehicles (last 3 added)
$recentVehiclesCursor = getVehiclesCollection()->find([], [
    'sort' => ['_id' => -1],
    'limit' => 3
]);
$recentVehicles = iterator_to_array($recentVehiclesCursor);

// Get recent bookings (last 5)
$recentBookings = getRecentBookings(); // Use the function from adminBackend.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vehicle Hiring System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <a href="adIndex.php" class="sidebar-logo">
                <i class="fas fa-car"></i> Admin Panel
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="adIndex.php" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="adVehicles.php">
                        <i class="fas fa-car"></i>
                        <span>Vehicles</span>
                    </a>
                </li>
                <li>
                    <a href="adBooking.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="adUsers.php">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="adReviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="adSettings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1 class="welcome-title">Welcome to Admin Dashboard</h1>
                <p class="welcome-subtitle">Manage your vehicle hiring system efficiently</p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-car" style="color: #238636;"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalVehicles; ?></div>
                    <div class="stat-label">Total Vehicles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check" style="color: #58a6ff;"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalBookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users" style="color: #d29922;"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star" style="color: #ffc107;"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalReviews; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="adAddVehicle.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-title">Add Vehicle</div>
                    <div class="action-desc">Add a new vehicle to the fleet</div>
                </a>
                
                <a href="adVehicles.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-title">View Vehicles</div>
                    <div class="action-desc">Manage all vehicles in the system</div>
                </a>
                
                <a href="adBooking.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="action-title">Manage Bookings</div>
                    <div class="action-desc">Manage booking requests</div>
                </a>
                
                <a href="adUsers.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="action-title">User Management</div>
                    <div class="action-desc">View and manage user accounts</div>
                </a>
                
                <a href="adReviews.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="action-title">View Reviews</div>
                    <div class="action-desc">Manage customer reviews and ratings</div>
                </a>
            </div>

            <!-- Recent Activities -->
            <div class="recent-section">
                <div class="recent-card">
                    <div class="recent-header">
                        <h3 class="recent-title">Recent Vehicles</h3>
                        <a href="adVehicles.php" class="recent-link">View All</a>
                    </div>
                    <div class="recent-list">
                        <?php
                        if (empty($recentVehicles)) {
                            echo '<div class="empty-state">';
                            echo '<div class="empty-icon"><i class="fas fa-car"></i></div>';
                            echo '<h3>No Vehicles Yet</h3>';
                            echo '<p>Add your first vehicle to get started</p>';
                            echo '</div>';
                        } else {
                            foreach ($recentVehicles as $vehicle) {
                                echo '<div class="recent-item">';
                                echo '<div class="recent-item-info">';
                                echo '<div class="recent-item-title">' . htmlspecialchars($vehicle['vehicle_name'] ?? '') . '</div>';
                                $seats = $vehicle['seat_count'] ?? $vehicle['seats'] ?? 'N/A';
                                $ac = $vehicle['ac_nac'] ?? $vehicle['ac'] ?? 'N/A';
                                $acDisplay = ($ac === 'AC' || $ac === 'NAC') ? ($ac === 'AC' ? 'AC' : 'Non-AC') : $ac;
                                echo '<div class="recent-item-desc">' . $seats . ' seats • ' . $acDisplay . '</div>';
                                echo '</div>';
                                echo '<div class="recent-item-date">Added recently</div>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="recent-card">
                    <div class="recent-header">
                        <h3 class="recent-title">Recent Bookings</h3>
                        <a href="adBooking.php" class="recent-link">View All</a>
                    </div>
                    <div class="recent-list">
                        <?php
                        if (empty($recentBookings)) {
                            echo '<div class="empty-state">';
                            echo '<div class="empty-icon"><i class="fas fa-calendar"></i></div>';
                            echo '<h3>No Bookings Yet</h3>';
                            echo '<p>Bookings will appear here when users make reservations</p>';
                            echo '</div>';
                        } else {
                            foreach ($recentBookings as $booking) {
                                echo '<div class="recent-item">';
                                // Display booking details here, e.g.:
                                echo '<div class="recent-item-info">';
                                echo '<div class="recent-item-title">Booking ID: ' . htmlspecialchars((string)($booking["_id"] ?? '')) . '</div>';
                                // Add more booking fields as needed
                                echo '</div>';
                                echo '<div class="recent-item-date">Added recently</div>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="recent-card">
                <div class="recent-header">
                    <h3 class="recent-title">System Status</h3>
                </div>
                <div class="recent-list">
                    <div class="recent-item">
                        <div class="recent-item-info">
                            <div class="recent-item-title">Database Connection</div>
                            <div class="recent-item-desc">Session-based storage active</div>
                        </div>
                        <div class="recent-item-date" style="color: #238636;">
                            <i class="fas fa-check-circle"></i> Online
                        </div>
                    </div>
                    <div class="recent-item">
                        <div class="recent-item-info">
                            <div class="recent-item-title">File Upload System</div>
                            <div class="recent-item-desc">Vehicle photo uploads enabled</div>
                        </div>
                        <div class="recent-item-date" style="color: #238636;">
                            <i class="fas fa-check-circle"></i> Active
                        </div>
                    </div>
                    <div class="recent-item">
                        <div class="recent-item-info">
                            <div class="recent-item-title">Admin Panel</div>
                            <div class="recent-item-desc">All features operational</div>
                        </div>
                        <div class="recent-item-date" style="color: #238636;">
                            <i class="fas fa-check-circle"></i> Running
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add mobile menu toggle functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.admin-sidebar');
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile && !sidebar.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html> 