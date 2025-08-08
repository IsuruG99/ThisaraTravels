<?php
require '../auth-config.php';

// Function to check for booking conflicts
function checkBookingConflicts($vehicle_id, $pickupDate, $dropoffDate, $excludeBookingId = null): array
{
    $conflicts = [];
    $query = [
        'vehicle_id' => new MongoDB\BSON\ObjectId($vehicle_id),
        'status' => ['$in' => ['pending', 'accepted']], // Only check pending and accepted bookings
        '$or' => [
            [
                'pickup_date' => ['$lte' => $dropoffDate],
                'dropoff_date' => ['$gte' => $pickupDate]
            ]
        ]
    ];

    // Exclude current booking if updating
    if ($excludeBookingId) {
        $query['_id'] = ['$ne' => new MongoDB\BSON\ObjectId($excludeBookingId)];
    }

    $conflictingBookings = getBookingsCollection()->find(filter: $query);
    foreach ($conflictingBookings as $booking) {
        $conflicts[] = [
            'id' => (string) $booking['_id'],
            'name' => $booking['name'] ?? 'Unknown',
            'pickup_date' => $booking['pickup_date'],
            'dropoff_date' => $booking['dropoff_date'],
            'status' => $booking['status'] ?? 'pending'
        ];
    }

    return $conflicts;
}

// Function to auto-deny conflicting bookings
function autoDenyConflictingBookings($vehicle_id, $pickupDate, $dropoffDate, $acceptedBookingId): int|null
{
    $query = [
        'vehicle_id' => new MongoDB\BSON\ObjectId($vehicle_id),
        'status' => 'pending', // Only auto-deny pending bookings
        '_id' => ['$ne' => new MongoDB\BSON\ObjectId($acceptedBookingId)],
        '$or' => [
            [
                'pickup_date' => ['$lte' => $dropoffDate],
                'dropoff_date' => ['$gte' => $pickupDate]
            ]
        ]
    ];

    $result = getBookingsCollection()->updateMany(
        filter: $query,
        update: ['$set' => ['status' => 'denied', 'denied_reason' => 'Auto-denied due to conflicting accepted booking']]
    );

    return $result->getModifiedCount();
}

// Handle Accept/Deny/Pending actions
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $bookingId = $_GET['id'];
    $newStatus = ($action === 'accept') ? 'accepted' : (($action === 'deny') ? 'denied' : (($action === 'pending') ? 'pending' : null));

    if ($newStatus) {
        // If accepting a booking, check for conflicts and auto-deny them
        if ($newStatus === 'accepted') {
            $booking = getBookingsCollection()->findOne(filter: ['_id' => new MongoDB\BSON\ObjectId($bookingId)]);
            if ($booking) {
                $conflicts = checkBookingConflicts(
                    vehicle_id: $booking['vehicle_id'] instanceof MongoDB\BSON\ObjectId 
                        ? (string) $booking['vehicle_id'] 
                        : $booking['vehicle_id'],
                    pickupDate: $booking['pickup_date'],
                    dropoffDate: $booking['dropoff_date'],
                    excludeBookingId: $bookingId
                );

                if (!empty($conflicts)) {
                    $autoDeniedCount = autoDenyConflictingBookings(
                        vehicle_id: $booking['vehicle_id'] instanceof MongoDB\BSON\ObjectId 
                            ? (string) $booking['vehicle_id'] 
                            : $booking['vehicle_id'],
                        pickupDate: $booking['pickup_date'],
                        dropoffDate: $booking['dropoff_date'],
                        acceptedBookingId: $bookingId
                    );

                    // Store conflict info in session for display
                    $_SESSION['conflict_info'] = [
                        'auto_denied_count' => $autoDeniedCount,
                        'conflicts' => $conflicts
                    ];
                }
            }
        }

        getBookingsCollection()->updateOne(
            filter: ['_id' => new MongoDB\BSON\ObjectID($bookingId)],
            update: ['$set' => ['status' => $newStatus]]
        );
        header(header: 'Location: adBooking.php');
        exit;
    }
}

// Fetch all bookings with conflict detection
$bookingsCursor = getBookingsCollection()->find(filter: [], options: [
    'sort' => ['created_at' => -1], // Sort by booking creation date (newest first)
    'limit' => 1000 // Add a reasonable limit to prevent memory issues
]);

$vehicleLookup = [];
$vehiclesCursor = getVehiclesCollection()->find(filter: [], options: ['projection' => ['vehicle_name' => 1, 'type' => 1]]);
foreach ($vehiclesCursor as $vehicle) {
    $vehicleLookup[(string) $vehicle['_id']] = [
        'vehicle_name' => $vehicle['vehicle_name'] ?? 'N/A',
        'type' => $vehicle['type'] ?? 'N/A'
    ];
}

$userLookup = [];
$usersCursor = getUsersCollection()->find(filter: [], options: ['projection' => ['UserName' => 1, 'Email' => 1]]);
foreach ($usersCursor as $user) {
    $userLookup[(string) $user['_id']] = [
        'username' => $user['UserName'] ?? 'N/A',
        'email' => $user['Email'] ?? 'N/A'
    ];
}

$bookings = [];
foreach ($bookingsCursor as $booking) {
    // Add conflict information to each booking
    if ($booking['status'] === 'pending') {
        $conflicts = checkBookingConflicts(
            vehicle_id: $booking['vehicle_id'],
            pickupDate: $booking['pickup_date'],
            dropoffDate: $booking['dropoff_date'],
            excludeBookingId: (string) $booking['_id']
        );
        $booking['conflicts'] = $conflicts;
        $booking['has_conflicts'] = !empty($conflicts);
    } else {
        $booking['conflicts'] = [];
        $booking['has_conflicts'] = false;
    }

    // Ensure _id is properly converted to string for JSON serialization
    $booking['_id'] = (string) $booking['_id'];

    // Convert MongoDB UTCDateTime objects to strings for JSON serialization
    if (isset($booking['created_at']) && $booking['created_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $booking['created_at'] = $booking['created_at']->toDateTime()->format('Y-m-d H:i:s');
    }
    if (isset($booking['pickup_date']) && $booking['pickup_date'] instanceof MongoDB\BSON\UTCDateTime) {
        $booking['pickup_date'] = $booking['pickup_date']->toDateTime()->format('Y-m-d H:i:s');
    }
    if (isset($booking['dropoff_date']) && $booking['dropoff_date'] instanceof MongoDB\BSON\UTCDateTime) {
        $booking['dropoff_date'] = $booking['dropoff_date']->toDateTime()->format('Y-m-d H:i:s');
    }

    $bookings[] = $booking;
}



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
                <li><a href="adBooking.php" class="active"><i
                            class="fas fa-calendar-check"></i><span>Bookings</span></a></li>
                <li><a href="adUsers.php"><i class="fas fa-users"></i><span>User Management</span></a></li>
                <li><a href="adReviews.php"><i class="fas fa-star"></i><span>Reviews</span></a></li>
                <li><a href="adSettings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="adLogout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2>Manage Bookings</h2>
            </div>

            <!-- Show conflict info if available -->
            <?php if (isset($_SESSION['conflict_info'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Auto-Deny Action:</strong>
                    <?php echo $_SESSION['conflict_info']['auto_denied_count']; ?> conflicting booking(s) were automatically
                    denied.
                    <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['conflict_info']); ?>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search bookings... ">
            </div>

            <div class="table-container">
                <table class="table" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Pickup Date</th>
                            <th>Dropoff Date</th>
                            <th>Status</th>
                            <th>Conflicts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No bookings found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr class="<?php echo $booking['has_conflicts'] ? 'conflict-row' : ''; ?>">
                                    <td><?php echo htmlspecialchars(string: (string) ($booking['_id'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(string: $booking['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(string: $booking['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(string: $booking['vehicle_name'] ?? ''); ?></td>
                                    <td>
                                        <?php
                                        if (isset($booking['pickup_date']) && $booking['pickup_date'] instanceof MongoDB\BSON\UTCDateTime) {
                                            echo $booking['pickup_date']->toDateTime()->format('Y-m-d H:i');
                                        } else {
                                            echo htmlspecialchars(string: $booking['pickup_date'] ?? '');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (isset($booking['dropoff_date']) && $booking['dropoff_date'] instanceof MongoDB\BSON\UTCDateTime) {
                                            echo $booking['dropoff_date']->toDateTime()->format('Y-m-d H:i');
                                        } else {
                                            echo htmlspecialchars(string: $booking['dropoff_date'] ?? '');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status'] ?? 'pending'; ?>">
                                            <?php echo htmlspecialchars(string: ucfirst(string: $booking['status'] ?? 'pending')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['has_conflicts']): ?>
                                            <span class="conflict-badge" title="Has conflicting bookings">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo count(value: $booking['conflicts']); ?> conflict(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="no-conflict-badge">
                                                <i class="fas fa-check"></i> No conflicts
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-info btn-sm"
                                                onclick="openModal('<?php echo $booking['_id']; ?>')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <a href="?action=accept&id=<?php echo $booking['_id']; ?>"
                                                    class="btn btn-success btn-sm"
                                                    onclick="return confirm('Accept this booking? This will automatically deny any conflicting bookings.')">
                                                    <i class="fas fa-check"></i> Accept
                                                </a>
                                                <a href="?action=deny&id=<?php echo $booking['_id']; ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('Deny this booking?')">
                                                    <i class="fas fa-times"></i> Deny
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
        const bookings = <?php echo json_encode(value: $bookings); ?>;
        const userLookup = <?php echo json_encode(value: $userLookup); ?>;
        const vehicleLookup = <?php echo json_encode(value: $vehicleLookup); ?>;

        function openModal(bookingId) {
            currentBookingId = bookingId;
            const booking = bookings.find(b => b._id === bookingId);
            if (booking) {
                // Get user data if user_id exists
                let userData = null;
                if (booking.user_id && booking.user_id.$oid) {
                    userData = userLookup[booking.user_id.$oid];
                } else if (booking.user_id) {
                    userData = userLookup[booking.user_id];
                }

                // Get vehicle data if vehicle_id exists
                let vehicleData = null;
                if (booking.vehicle_id && booking.vehicle_id.$oid) {   
                    vehicleData = vehicleLookup[booking.vehicle_id.$oid];
                } else if (booking.vehicle_id) {
                    vehicleData = vehicleLookup[booking.vehicle_id];
                }

                const details = `
                    <div class="detail-row">
                        <div class="detail-label">Booking ID:</div>
                        <div class="detail-value">${booking._id}</div>
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
                        <div class="detail-value">${userData?.email || booking.email || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username:</div>
                        <div class="detail-value">${userData?.username || booking.username || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Vehicle Name:</div>
                        <div class="detail-value">${vehicleData?.vehicle_name || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Vehicle Type:</div>
                        <div class="detail-value">${vehicleData?.type || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created Date:</div>
                        <div class="detail-value">${booking.created_at || 'N/A'}</div>
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
        window.onclick = function (event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#bookingsTable tbody tr');

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