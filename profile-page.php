<?php
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];

// Initialize MongoDB client
try {
    $client = new MongoDB\Client($uri);
    $collection = $client->ThisaraTravels->users;

    // Fetch user data if not already in session
    if (isset($_SESSION['user_id'])) {
        $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);
        if ($user) {
            // Update session username in case it changed
            $_SESSION['username'] = $user['UserName'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? 'img/default_profile.png';
            $_SESSION['has_password'] = !empty($user['Password']);
        } else {
            // User not found in DB - log them out
            session_destroy();
            $_SESSION['error_message'] = "User not logged in.";
            header("Location: auth.php");
            exit;
        }
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $client = new MongoDB\Client($uri);
    $database = $client->ThisaraTravels;
    $bookingsCollection = $database->booking;
    $reviewsCollection = $database->reviews;
    $username = $_SESSION['username'];

    // Get user bookings
    $userBookings = $bookingsCollection->find(
        ['UserName' => $username],
        ['sort' => ['BookingDate' => -1]]
    )->toArray();

    // Get user reviews
    $userReviews = $reviewsCollection->find(
        ['UserName' => $username],
        ['sort' => ['date' => -1]]
    )->toArray();

} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/profile-page.css">
</head>

<body>
    <div class="container py-5">
        <!-- Profile Header Section -->
        <div class="profile-header text-center">
            <div class="position-relative d-inline-block">
                <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile Photo"
                    class="profile-picture">
                <div class="update-photo-btn" data-bs-toggle="modal" data-bs-target="#updatePhotoModal">
                    <i class='bx bx-camera'></i>
                </div>
            </div>

            <!-- Username Display and Edit -->
            <div class="profile-info mt-4">
                <div class="username-section mb-3">
                    <h3 class="d-inline-block">
                        <span class="profile-username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class='bx bx-edit edit-icon' data-field="username" title="Edit username"></i>
                    </h3>
                    <form id="usernameForm" class="d-none"
                        onsubmit="event.preventDefault(); return submitFieldUpdate('username')">
                        <div class="d-flex justify-content-center align-items-center">
                            <input type="text" name="username"
                                value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                                class="form-control w-auto me-2">
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            <button type="button" class="btn btn-sm btn-secondary ms-2 cancel-edit">Cancel</button>
                        </div>
                        <?php if ($_SESSION['has_password']): ?>
                            <div class="mt-2" id="usernamePasswordField" style="display:none;">
                                <input type="password" name="username_current_password" placeholder="Current Password"
                                    class="form-control w-auto mx-auto">
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Email Display and Edit -->
                <div class="email-section mb-3">
                    <p class="d-inline-block mb-0">
                        <span class="profile-email">
                            <?php
                            // Fetch email from DB when needed
                            $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])], ['projection' => ['Email' => 1]]);
                            echo htmlspecialchars($user['Email'] ?? 'No email provided');
                            ?>
                        </span>
                        <?php if (isset($user['Email'])): ?>
                            <i class='bx bx-edit edit-icon' data-field="email" title="Edit email"></i>
                        <?php endif; ?>
                    </p>
                    <form id="emailForm" class="d-none"
                        onsubmit="event.preventDefault(); return submitFieldUpdate('email')">
                        <div class="d-flex justify-content-center align-items-center">
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>"
                                class="form-control w-auto me-2">
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            <button type="button" class="btn btn-sm btn-secondary ms-2 cancel-edit">Cancel</button>
                        </div>
                        <?php if ($_SESSION['has_password']): ?>
                            <div class="mt-2" id="emailPasswordField" style="display:none;">
                                <input type="password" name="email_current_password" placeholder="Current Password"
                                    class="form-control w-auto mx-auto">
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Password Change Section (only for non-OAuth users) -->
            <?php if (isset($_SESSION['has_password']) && $_SESSION['has_password']): ?>
                <div class="mt-3">
                    <button class="btn btn-outline-light" id="showPasswordChange">Change Password</button>
                    <div id="passwordChangeForm" class="mt-3 d-none">
                        <form id="passwordForm" onsubmit="return submitPasswordChange()">
                            <div class="mb-2">
                                <input type="password" name="current_password" placeholder="Current Password"
                                    class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <input type="password" name="new_password" placeholder="New Password" class="form-control"
                                    required>
                            </div>
                            <div class="mb-2">
                                <input type="password" name="confirm_password" placeholder="Confirm New Password"
                                    class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <button type="button" class="btn btn-secondary" id="cancelPasswordChange">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-light back-btn me-2">
                <i class='bx bx-arrow-back'></i> Back to Home
            </a>
            <a href="auth-logout.php" class="btn btn-light logout-btn">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings"
                    type="button" role="tab">My Bookings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button"
                    role="tab">My Reviews</button>
            </li>
        </ul>
        <script>
            // Edit field handling
            document.querySelectorAll('.edit-icon').forEach(icon => {
                icon.addEventListener('click', function () {
                    const field = this.getAttribute('data-field');
                    document.querySelector(`.profile-${field}`).classList.add('d-none');
                    this.classList.add('d-none');
                    document.getElementById(`${field}Form`).classList.remove('d-none');
                });
            });

            // Cancel edit
            document.querySelectorAll('.cancel-edit').forEach(btn => {
                btn.addEventListener('click', function () {
                    const form = this.closest('form');
                    form.classList.add('d-none');
                    form.parentElement.querySelector('.edit-icon').classList.remove('d-none');
                    form.parentElement.querySelector(`.profile-${form.id.replace('Form', '')}`).classList.remove('d-none');
                });
            });

            // Password change toggle
            document.getElementById('showPasswordChange')?.addEventListener('click', function () {
                this.classList.add('d-none');
                document.getElementById('passwordChangeForm').classList.remove('d-none');
            });
            document.getElementById('cancelPasswordChange')?.addEventListener('click', function () {
                document.getElementById('passwordChangeForm').classList.add('d-none');
                document.getElementById('showPasswordChange').classList.remove('d-none');
            });

            // Form submission handlers
            function submitFieldUpdate(field) {
                event.preventDefault();

                const form = document.getElementById(`${field}Form`);
                const formData = new FormData(form);
                formData.append('field', field);
                formData.append('user_id', '<?php echo $_SESSION["user_id"]; ?>');

                // Check if password is needed and not provided
                if (<?php echo $_SESSION['has_password'] ? 'true' : 'false'; ?> &&
                    document.getElementById(`${field}PasswordField`) &&
                    document.getElementById(`${field}PasswordField`).style.display === 'none') {

                    // Show password field and focus it
                    document.getElementById(`${field}PasswordField`).style.display = 'block';
                    form.querySelector('[name^="' + field + '_current_password"]').required = true;
                    form.querySelector('[name^="' + field + '_current_password"]').focus();
                    return false;
                }

                fetch('profile-backend.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error || 'Error updating field');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the field.');
                    });
                return false;
            }

            function submitPasswordChange() {
                event.preventDefault(); // Prevent default form submission

                const form = document.getElementById('passwordForm');
                const formData = new FormData(form);
                formData.append('change_password', '1');

                if (formData.get('new_password') !== formData.get('confirm_password')) {
                    alert("New passwords don't match!");
                    return false;
                }

                fetch('profile-backend.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Password updated successfully!");
                            location.reload();
                        } else {
                            alert(data.error || 'Error changing password');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while changing password.');
                    });
                return false;
            }
        </script>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabsContent">
            <!-- Bookings Tab -->
            <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                <div class="profile-card">
                    <h2 class="profile-card-title">My Bookings</h2>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Pickup Date</th>
                                    <th>Pickup Time</th>
                                    <th>Vehicle Plate</th>
                                    <th>Pickup Location</th>
                                    <th>Dropoff Location</th>
                                    <th>Booking Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <span class="badge 
                                                <?php echo match ($booking['BookingStatus']) {
                                                    'Completed' => 'bg-success',
                                                    'Pending' => 'bg-warning',
                                                    'Denied' => 'bg-danger',
                                                    'Accepted' => 'bg-primary',
                                                    default => 'bg-secondary'
                                                }; ?>">
                                                <?php echo htmlspecialchars($booking['BookingStatus']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            try {
                                                $pickupDate = $booking['PickupDateTime'] instanceof MongoDB\BSON\UTCDateTime
                                                    ? $booking['PickupDateTime']->toDateTime()
                                                    : new DateTime($booking['PickupDateTime'] ?? 'now');
                                                echo htmlspecialchars($pickupDate->format('M j, Y'));
                                            } catch (Exception $e) {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            try {
                                                echo htmlspecialchars($pickupDate->format('h:i A'));
                                            } catch (Exception $e) {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['VehiclePlate'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($booking['PickupLocation'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($booking['DropoffLocation'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $bookingDate = $booking['BookingDate'] instanceof MongoDB\BSON\UTCDateTime
                                                ? $booking['BookingDate']->toDateTime()
                                                : new DateTime($booking['BookingDate']);
                                            echo htmlspecialchars($bookingDate->format('M j, Y'));
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (($booking['BookingStatus'] ?? '') === 'Pending' || ($booking['BookingStatus'] ?? '') === 'Accepted'): ?>
                                                <button class="btn btn-sm btn-outline-danger cancel-btn"
                                                    data-booking-id="<?php echo htmlspecialchars($booking['_id'] instanceof MongoDB\BSON\ObjectId ? $booking['_id']->__toString() : ($booking['_id']['$oid'] ?? '')); ?>">
                                                    Cancel
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($userBookings)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No bookings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="profile-card">
                    <h2 class="profile-card-title">My Reviews</h2>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rating</th>
                                    <th>Vehicle</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userReviews as $review): ?>
                                    <tr>
                                        <td>
                                            <div class="star-rating">
                                                <?php echo str_repeat('★', $review['ReviewCount']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($review['vehicle'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($review['Comment']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($review['date']))); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger delete-review-btn"
                                                data-review-id="<?php echo htmlspecialchars($review['_id'] instanceof MongoDB\BSON\ObjectId ? $review['_id']->__toString() : ($review['_id']['$oid'] ?? '')); ?>">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($userReviews)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No reviews found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Photo Modal -->
    <div class="modal fade" id="updatePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="photoForm" method="POST" enctype="multipart/form-data">
                    <!-- Remove the action attribute -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profilePhoto" class="form-label">Select new photo</label>
                            <input class="form-control" type="file" id="profilePhoto" name="profilePhoto"
                                accept="image/*" required>
                            <input type="hidden" name="update_photo" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile photo update handling
        document.getElementById('photoForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('profile-backend.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Profile photo updated successfully!');
                        document.querySelector('.profile-picture').src = data.newPhotoPath + '?' + new Date().getTime();
                        var modal = bootstrap.Modal.getInstance(document.getElementById('updatePhotoModal'));
                        modal.hide();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the photo.');
                });
        });

        // Tab switching
        const profileTabs = document.querySelector('#profileTabs');
        profileTabs.addEventListener('click', function (e) {
            if (e.target.tagName === 'BUTTON') {
                const tabId = e.target.getAttribute('data-bs-target');
                localStorage.setItem('lastActiveTab', tabId);
            }
        });

        // Restore last active tab on page load
        document.addEventListener('DOMContentLoaded', function () {
            const lastActiveTab = localStorage.getItem('lastActiveTab');
            if (lastActiveTab) {
                const tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${lastActiveTab}"]`));
                tab.show();
            }
        });
    </script>
</body>

</html>