<?php
session_start();

// Ensure sessions are initialized
if (!isset($_SESSION['vehicles'])) {
    $_SESSION['vehicles'] = [];
}

if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [];
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// Calculate statistics
$totalVehicles = count($_SESSION['vehicles']);
$totalBookings = count($_SESSION['bookings']);
$totalUsers = count($_SESSION['users']);

// Get recent vehicles (last 5)
$recentVehicles = array_slice(array_reverse($_SESSION['vehicles']), 0, 5);

// Get recent bookings (last 5)
$recentBookings = array_slice(array_reverse($_SESSION['bookings']), 0, 5);
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
                    <a href="adBookings.php">
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
                    <div class="stat-number"><?php echo count($_SESSION['vehicles'] ?? []); ?></div>
                    <div class="stat-label">Total Vehicles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check" style="color: #58a6ff;"></i>
                    </div>
                    <div class="stat-number"><?php echo count($_SESSION['bookings'] ?? []); ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users" style="color: #d29922;"></i>
                    </div>
                    <div class="stat-number"><?php echo count($_SESSION['users'] ?? []); ?></div>
                    <div class="stat-label">Total Users</div>
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
                
                <a href="adBookings.php" class="action-card">
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
                        $vehicles = $_SESSION['vehicles'] ?? [];
                        $recentVehicles = array_slice($vehicles, -3);
                        
                        if (empty($recentVehicles)) {
                            echo '<div class="empty-state">';
                            echo '<div class="empty-icon"><i class="fas fa-car"></i></div>';
                            echo '<h3>No Vehicles Yet</h3>';
                            echo '<p>Add your first vehicle to get started</p>';
                            echo '</div>';
                        } else {
                            foreach (array_reverse($recentVehicles) as $vehicle) {
                                echo '<div class="recent-item">';
                                echo '<div class="recent-item-info">';
                                echo '<div class="recent-item-title">' . htmlspecialchars($vehicle['name']) . '</div>';
                                
                                // Handle different possible field names for seats and AC
                                $seats = $vehicle['seats'] ?? $vehicle['seat_count'] ?? 'N/A';
                                $ac = $vehicle['ac'] ?? $vehicle['ac_nac'] ?? 'N/A';
                                
                                // Format AC display
                                if (is_bool($ac)) {
                                    $acDisplay = $ac ? 'AC' : 'Non-AC';
                                } else {
                                    $acDisplay = $ac;
                                }
                                
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
                        <a href="adBookings.php" class="recent-link">View All</a>
                    </div>
                    <div class="recent-list">
                        <?php
                        $bookings = $_SESSION['bookings'] ?? [];
                        $recentBookings = array_slice($bookings, -3);
                        
                        if (empty($recentBookings)) {
                            echo '<div class="empty-state">';
                            echo '<div class="empty-icon"><i class="fas fa-calendar"></i></div>';
                            echo '<h3>No Bookings Yet</h3>';
                            echo '<p>Bookings will appear here when users make reservations</p>';
                            echo '</div>';
                        } else {
                            foreach (array_reverse($recentBookings) as $booking) {
                                echo '<div class="recent-item">';
                                echo '<div class="recent-item-info">';
                                echo '<div class="recent-item-title">' . htmlspecialchars($booking['vehicle_name']) . '</div>';
                                echo '<div class="recent-item-desc">' . htmlspecialchars($booking['customer_name']) . ' • ' . $booking['date'] . '</div>';
                                echo '</div>';
                                echo '<div class="recent-item-date">' . ucfirst($booking['status']) . '</div>';
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