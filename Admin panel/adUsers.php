<?php
require '../auth-config.php';

// Fetch all users
$users = iterator_to_array(getUsersCollection()->find([], ['sort' => ['_id' => -1]]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <a href="adIndex.php" class="sidebar-logo">
                <i class="fas fa-car"></i> Admin Panel
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="adIndex.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="adVehicles.php"><i class="fas fa-car"></i><span>Vehicles</span></a></li>
                <li><a href="adBooking.php"><i class="fas fa-calendar-check"></i><span>Bookings</span></a></li>
                <li><a href="adUsers.php" class="active"><i class="fas fa-users"></i><span>User Management</span></a></li>
                <li><a href="adReviews.php"><i class="fas fa-star"></i><span>Reviews</span></a></li>
                <li><a href="adSettings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="adLogout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2>User Management</h2>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search users...">
            </div>

            <div class="table-container">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Profile Picture</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)($user['_id'] ?? '')); ?></td>
                                    <td>
                                        <?php if (!empty($user['ProfilePhoto'])): ?>
                                            <?php
                                            $photoPath = $user['ProfilePhoto'];
                                            if (file_exists($photoPath)) {
                                                $displayPath = $photoPath;
                                            } elseif (file_exists('../' . $photoPath)) {
                                                $displayPath = '../' . $photoPath;
                                            } else {
                                                $displayPath = $photoPath; // fallback
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['UserName'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($user['role'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($user['Verified'] ? 'Yes' : 'No'); ?></td>
                                    <td><?php echo htmlspecialchars($user['date'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#usersTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 