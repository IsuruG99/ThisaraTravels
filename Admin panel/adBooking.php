<?php
require '../auth-config.php';

// Handle Accept/Deny/Pending actions
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $bookingId = $_GET['id'];
    $newStatus = ($action === 'accept') ? 'accepted' : (($action === 'deny') ? 'denied' : (($action === 'pending') ? 'pending' : null));
    if ($newStatus) {
        getBookingsCollection()->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($bookingId)],
            ['$set' => ['status' => $newStatus]]
        );
        header('Location: adBooking.php');
        exit;
    }
}

// Fetch all bookings
$bookings = iterator_to_array(getBookingsCollection()->find([], ['sort' => ['_id' => -1]]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
                <li><a href="adBooking.php" class="active"><i class="fas fa-calendar-check"></i><span>Bookings</span></a></li>
                <li><a href="adUsers.php"><i class="fas fa-users"></i><span>User Management</span></a></li>
                <li><a href="adReviews.php"><i class="fas fa-star"></i><span>Reviews</span></a></li>
                <li><a href="adSettings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2>Manage Bookings</h2>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search bookings... ">
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="7" class="text-center">No bookings found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)($booking['_id'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($booking['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($booking['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($booking['vehicle_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($booking['pickup_date'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($booking['status'] ?? 'pending')); ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="openModal('<?php echo $booking['_id']; ?>')">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Booking Details</h2>
            <div id="bookingDetails" class="booking-details">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-actions">
                <a href="#" id="pendingBtn" class="btn btn-warning">Pending</a>
                <a href="#" id="acceptBtn" class="btn btn-success">Accept</a>
                <a href="#" id="denyBtn" class="btn btn-danger">Deny</a>
            </div>
        </div>
    </div>

    <script>
        let currentBookingId = null;
        const bookings = <?php echo json_encode($bookings); ?>;

        function openModal(bookingId) {
            currentBookingId = bookingId;
            const booking = bookings.find(b => b._id.$oid === bookingId || b._id === bookingId);
            if (booking) {
                const details = `
                    <div class="detail-row">
                        <div class="detail-label">Booking ID:</div>
                        <div class="detail-value">${booking._id.$oid || booking._id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Customer Name:</div>
                        <div class="detail-value">${booking.name || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value">${booking.phone || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">${booking.email || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username:</div>
                        <div class="detail-value">${booking.username || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Vehicle Name:</div>
                        <div class="detail-value">${booking.vehicle_name || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Vehicle Type:</div>
                        <div class="detail-value">${booking.vehicle_type || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Pickup Date:</div>
                        <div class="detail-value">${booking.pickup_date || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Dropoff Date:</div>
                        <div class="detail-value">${booking.dropoff_date || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Pickup Time:</div>
                        <div class="detail-value">${booking.pickup_time || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Pickup Location:</div>
                        <div class="detail-value">${booking.pickup_location || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Dropoff Location:</div>
                        <div class="detail-value">${booking.dropoff_location || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Custom Pickup:</div>
                        <div class="detail-value">${booking.custom_pickup_location || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Custom Dropoff:</div>
                        <div class="detail-value">${booking.custom_dropoff_location || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Special Request:</div>
                        <div class="detail-value">${booking.Special_Request || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Current Status:</div>
                        <div class="detail-value">${booking.status || 'pending'}</div>
                    </div>
                `;
                document.getElementById('bookingDetails').innerHTML = details;
                
                // Update action buttons
                document.getElementById('pendingBtn').href = `?action=pending&id=${bookingId}`;
                document.getElementById('acceptBtn').href = `?action=accept&id=${bookingId}`;
                document.getElementById('denyBtn').href = `?action=deny&id=${bookingId}`;
                
                document.getElementById('bookingModal').style.display = 'block';
            }
        }

        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeModal();
            }
        }

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
    </script>
</body>
</html> 