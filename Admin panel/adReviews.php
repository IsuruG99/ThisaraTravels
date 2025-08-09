<?php
require '../auth-config.php';

$vehicleLookup = [];
$vehiclesCursor = getVehiclesCollection()->find(filter: [], options: ['projection' => ['vehicle_name' => 1, 'type' => 1]]);
foreach ($vehiclesCursor as $vehicle) {
    $vehicleLookup[(string) $vehicle['_id']] = [
        'vehicle_name' => $vehicle['vehicle_name'] ?? 'N/A',
        'type' => $vehicle['type'] ?? 'N/A'
    ];
}

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    try {
        $deleteResult = getReviewsCollection()->deleteOne(filter: ['_id' => new MongoDB\BSON\ObjectId($deleteId)]);
        if ($deleteResult->getDeletedCount() > 0) {
            header(header: 'Location: adReviews.php?deleted=1');
            exit;
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error deleting review: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Show success messages
if (isset($_GET['deleted'])) {
    echo '<div class="alert alert-success">Review deleted successfully!</div>';
}

// Fetch all reviews
$reviews = iterator_to_array(iterator: getReviewsCollection()->find(filter: [], options: [
    'sort' => ['date' => -1]
]));

// Function to get customer name by user ID
function getCustomerName($userId): mixed {
    try {
        $user = getUsersCollection()->findOne(filter: ['_id' => new MongoDB\BSON\ObjectId($userId)]);
        return $user ? ($user['UserName'] ?? 'Unknown User') : 'Unknown User';
    } catch (Exception $e) {
        return 'Unknown User';
    }
}

// Function to format date
function formatDate($date): mixed {
    if (is_object(value: $date) && $date instanceof MongoDB\BSON\UTCDateTime) {
        return $date->toDateTime()->format('Y-m-d H:i:s');
    }
    return 'Invalid Date';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management - Admin Panel</title>
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
                <li><a href="adUsers.php"><i class="fas fa-users"></i><span>User Management</span></a></li>
                <li><a href="adReviews.php" class="active"><i class="fas fa-star"></i><span>Reviews</span></a></li>
                <li><a href="adSettings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="adLogout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2>Review Management</h2>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star" style="color: #238636;"></i>
                    </div>
                    <div class="stat-number"><?php echo count(value: $reviews); ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star-half-alt" style="color: #238636;"></i>
                    </div>
                    <div class="stat-number">
                        <?php 
                        $avgRating = 0;
                        if (!empty($reviews)) {
                            $totalStars = 0;
                            foreach ($reviews as $review) {
                                $totalStars += (int)($review['starCount'] ?? 0);
                            }
                            $avgRating = round(num: $totalStars / count(value: $reviews), precision: 1);
                        }
                        echo $avgRating;
                        ?>
                    </div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star" style="color: #238636;"></i>
                    </div>
                    <div class="stat-number">
                        <?php 
                        $fiveStarReviews = 0;
                        foreach ($reviews as $review) {
                            if (($review['starCount'] ?? 0) == 5) {
                                $fiveStarReviews++;
                            }
                        }
                        echo $fiveStarReviews;
                        ?>
                    </div>
                    <div class="stat-label">5-Star Reviews</div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search reviews by customer or vehicle name...">
            </div>

            <!-- Reviews Table -->
            <div class="table-container">
                <?php if (empty($reviews)): ?>
                    <div class="empty-state">
                        <i class="empty-icon fas fa-star"></i>
                        <h3>No reviews found</h3>
                        <p>No reviews have been submitted yet.</p>
                    </div>
                <?php else: ?>
                    <table class="table" id="reviewsTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <?php
                                // Fetch vehicle info using vehicleId
                                $vehicleId = (string)($review['vehicleId'] instanceof MongoDB\BSON\ObjectId 
                                    ? $review['vehicleId'] 
                                    : $review['vehicleId']);
                                $vehicleInfo = $vehicleLookup[$vehicleId] ?? ['vehicle_name' => 'Unknown Vehicle', 'type' => 'N/A'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <i class="fas fa-user"></i> 
                                            <?php echo htmlspecialchars(string: getCustomerName(userId: $review['userId'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vehicle-info">
                                            <i class="fas fa-car"></i> 
                                            <?php echo htmlspecialchars(string: $vehicleInfo['vehicle_name']); ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars(string: $vehicleInfo['type']); ?></small>
                                    </td>
                                    <td>
                                        <div class="star-rating">
                                            <?php 
                                            $starCount = (int)($review['starCount'] ?? 0);
                                            for ($i = 1; $i <= 5; $i++): 
                                            ?>
                                                <i class="fas fa-star star <?php echo $i <= $starCount ? '' : 'empty'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted">(<?php echo $starCount; ?>)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($review['comment'])): ?>
                                            <div class="review-comment">
                                                <i class="fas fa-comment"></i>
                                                <?php echo htmlspecialchars(string: $review['comment']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No comment</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="review-date">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo formatDate(date: $review['date']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-secondary" onclick="showReviewDetails('<?php echo addslashes((string)($review['_id'])); ?>', '<?php echo addslashes(getCustomerName($review['userId'])); ?>', '<?php echo addslashes($vehicleInfo['vehicle_name']); ?>', '<?php echo $starCount; ?>', '<?php echo addslashes($review['comment'] ?? ''); ?>', '<?php echo addslashes(formatDate($review['date'])); ?>', '<?php echo addslashes((string)($review['orderId'] ?? '')); ?>', '<?php echo addslashes($vehicleInfo['type']); ?>')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <a href="?delete=<?php echo (string)($review['_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?\n\nCustomer: <?php echo htmlspecialchars(getCustomerName($review['userId'])); ?>\nVehicle: <?php echo htmlspecialchars($vehicleInfo['vehicle_name']); ?>\n\nThis action cannot be undone and will remove the review from all pages.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php if (empty($reviews)): ?>
                    <!-- Empty state when no reviews are found -->
                    <div class="empty-state">
                        <i class="empty-icon fas fa-star"></i>
                        <h3>No reviews found</h3>
                        <p>No reviews have been submitted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Review Details Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Review Details</h2>
            <div class="booking-details">
                <div class="detail-row">
                    <div class="detail-label">Customer:</div>
                    <div class="detail-value" id="modalCustomer"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Vehicle:</div>
                    <div class="detail-value" id="modalVehicle"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Vehicle Type:</div>
                    <div class="detail-value" id="modalVehicleType"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Rating:</div>
                    <div class="detail-value" id="modalRating"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Comment:</div>
                    <div class="detail-value" id="modalComment"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value" id="modalDate"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Order ID:</div>
                    <div class="detail-value" id="modalOrderId"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Review ID:</div>
                    <div class="detail-value" id="modalReviewId"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#reviewsTable tbody tr');
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            } else {
                console.error('Search input not found');
            }
        });

        // Modal functionality
        function showReviewDetails(reviewId, customer, vehicle, rating, comment, date, orderId, vehicleType) {
            document.getElementById('modalReviewId').textContent = reviewId || 'N/A';
            document.getElementById('modalCustomer').textContent = customer || 'N/A';
            document.getElementById('modalVehicle').textContent = vehicle || 'N/A';
            document.getElementById('modalVehicleType').textContent = vehicleType || 'N/A';
            document.getElementById('modalRating').innerHTML = generateStars(rating || 0);
            document.getElementById('modalComment').textContent = comment || 'No comment';
            document.getElementById('modalDate').textContent = date || 'N/A';
            document.getElementById('modalOrderId').textContent = orderId || 'N/A';
            
            document.getElementById('reviewModal').style.display = 'block';
        }

        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star star"></i>';
                } else {
                    stars += '<i class="fas fa-star star empty"></i>';
                }
            }
            return stars + ' <span class="text-muted">(' + rating + ')</span>';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Close modal when clicking on X or outside the modal
        window.onclick = function(event) {
            var modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal when clicking on X
        document.querySelector('.close').onclick = function() {
            document.getElementById('reviewModal').style.display = 'none';
        }
    </script>
</body>
</html> 