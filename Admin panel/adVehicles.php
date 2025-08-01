<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - Admin Panel</title>
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
                    <a href="adIndex.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="adVehicles.php" class="active">
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
            <?php
            require '../auth-config.php';

            // Handle delete action
            if (isset($_GET['delete'])) {
                $deleteId = $_GET['delete'];
                try {
                    $deleteResult = getVehiclesCollection()->deleteOne(['_id' => new MongoDB\BSON\ObjectID($deleteId)]);
                    if ($deleteResult->getDeletedCount() > 0) {
                        header('Location: adVehicles.php?deleted=1');
                        exit;
                    }
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error deleting vehicle: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Show success messages
            if (isset($_GET['success'])) {
                echo '<div class="alert alert-success">Vehicle added successfully!</div>';
            }
            if (isset($_GET['updated'])) {
                echo '<div class="alert alert-success">Vehicle updated successfully!</div>';
            }
            if (isset($_GET['deleted'])) {
                echo '<div class="alert alert-success">Vehicle deleted successfully!</div>';
            }
            ?>

            <div class="page-header">
                <h2>Manage Vehicles</h2>
                <a href="adAddVehicle.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Vehicle
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search vehicles...">
            </div>

            <!-- Vehicles Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Seats</th>
                            <th>AC/Non-AC</th>
                            <th>Features</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vehiclesCursor = getVehiclesCollection()->find();
                        $vehicles = iterator_to_array($vehiclesCursor);
                        
                        if (empty($vehicles)) {
                            echo '<tr><td colspan="6" class="text-center">No vehicles found. <a href="adAddVehicle.php">Add your first vehicle</a></td></tr>';
                        } else {
                            foreach ($vehicles as $vehicle) {
                                echo '<tr>';
                                echo '<td>';
                                if (!empty($vehicle['vehiclePhoto'])) {
                                    $photoPath = $vehicle['vehiclePhoto'];
                                    // Try with and without '../' for compatibility
                                    if (file_exists($photoPath)) {
                                        $displayPath = $photoPath;
                                    } elseif (file_exists('../' . $photoPath)) {
                                        $displayPath = '../' . $photoPath;
                                    } else {
                                        $displayPath = $photoPath; // fallback
                                    }
                                    echo '<img src="' . htmlspecialchars($displayPath) . '" alt="Vehicle" class="img-thumbnail">';
                                } else {
                                    // fallback
                                    echo '<div style="width: 80px; height: 60px; background: #30363d; display: flex; align-items: center; justify-content: center; border-radius: 4px;">';
                                    echo '<i class="fas fa-car" style="color: #8b949e;"></i>';
                                    echo '</div>';
                                }
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($vehicle['vehicle_name'] ?? '') . '</td>';
                                echo '<td>' . htmlspecialchars($vehicle['seat_count'] ?? 'N/A') . '</td>';
                                echo '<td>' . (($vehicle['ac_nac'] ?? '') === 'AC' ? 'AC' : 'Non-AC') . '</td>';
                                $features = $vehicle['features'] ?? [];
                                if ($features instanceof \MongoDB\Model\BSONArray) {
                                    $features = iterator_to_array($features);
                                }
                                if (is_array($features)) {
                                    $featuresStr = htmlspecialchars(implode(', ', $features));
                                } else {
                                    $featuresStr = htmlspecialchars($features);
                                }
                                echo '<td>' . $featuresStr . '</td>';
                                echo '<td>';
                                echo '<div class="table-actions">';
                                echo '<a href="adEditVehicle.php?id=' . $vehicle['_id'] . '" class="btn btn-warning btn-sm">';
                                echo '<i class="fas fa-edit"></i> Edit';
                                echo '</a>';
                                echo '<a href="?delete=' . $vehicle['_id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this vehicle?\')">';
                                echo '<i class="fas fa-trash"></i> Delete';
                                echo '</a>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Mobile menu toggle functionality
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