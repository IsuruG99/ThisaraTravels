<?php
require 'auth-config.php';
// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $db = getMongoDB();
    $reviewsCollection = getReviewsCollection();
    $usersCollection = getUsersCollection();
    $bookingsCollection = getBookingsCollection();
    $vehiclesCollection = getVehiclesCollection();

    if (!empty($_GET['vehicle'])) {
        $filter['vehicleType'] = $_GET['vehicle'];
    }
    if (!empty($_GET['stars'])) {
        $filter['starCount'] = (string) $_GET['stars'];
    }

    $documents = $reviewsCollection->find(filter: ['vehicleId' => ['$exists' => true]])->toArray();
    $totalRating = 0;
    $starCount = count(value: $documents);

    // Prepare user data
    $userData = [];
    foreach ($documents as $doc) {
        $totalRating += (int) $doc['starCount'];
        if (isset($doc['userId'])) {
            $userId = (string) $doc['userId'];
            if (!isset($userData[$userId])) {
                $user = $usersCollection->findOne(filter: ['_id' => $doc['userId']]);
                $userData[$userId] = [
                    'userName' => $user['UserName'] ?? 'Unknown User',
                    'profileImage' => $user['profile_image'] ?? 'img/profile-icon.png'
                ];
            }
        }
    }
    $avgRating = $starCount ? round(num: $totalRating / $starCount, precision: 1) : 0;
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle report submission using PHPMailer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_review'])) {

    $reviewId = htmlspecialchars(string: $_POST['report_review_id']);
    $reason = htmlspecialchars(string: $_POST['report_reason']);
    $reporter = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';

    $mail = new PHPMailer(exceptions: true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom(address: $_ENV['SMTP_USER'], name: 'Thisara Travels & Tours');
        $mail->addAddress(address: $_ENV['SMTP_ADMIN']);

        // Content
        $mail->isHTML(isHtml: true);
        $mail->Subject = "Review Report - " . $reviewId;
        $mail->Body = "
            <h3 style='color: #08625c; margin-bottom: 15px;'>New Review Report</h3>
            <p><strong>Review ID:</strong> {$reviewId}</p>
            <p><strong>Reason:</strong> {$reason}</p>
            <p><strong>Reported by:</strong> {$reporter}</p>
            <p style='margin-top: 20px; color: #666;'>
                Please investigate this reported review as soon as possible.
            </p>
        ";

        $mail->send();
        $_SESSION['report_success'] = true;
    } catch (Exception $e) {
        $_SESSION['report_error'] = "Report could not be sent. Error: {$mail->ErrorInfo}";
    }
    header(header: "Location: testimonial.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Testimonials - View All</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/testimonial.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Header Start -->
    <?php
    $currentPage = 'review';
    include 'components/header.php';
    ?>
    <!-- Header End -->

    <!-- Report Success Alert -->
    <?php if (isset($_SESSION['report_success'])): ?>
        <div class="container mt-3 floating-element">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thank you for reporting this review. We'll investigate it shortly.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['report_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['report_error'])): ?>
        <div class="container mt-3 floating-element">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['report_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['report_error']); ?>
    <?php endif; ?>

    <div class="hero-section text-center floating-element-wide">
        <div class="container">
            <h1>Testimonials</h1>
            <p>What our clients say about us</p>
        </div>
    </div>

    <!-- Vehicle Filter -->
    <div class="container my-4 floating-element-slow">
        <div class="filter-form">
            <form method="GET" class="text-center">
                <label for="vehicle">Filter by Vehicle Type:</label>
                <select name="vehicle" id="vehicle" class="form-select d-inline-block w-auto mx-2">
                    <option value="">All</option>
                    <option value="Car" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Car') ? 'selected' : '' ?>>Car
                    </option>
                    <option value="KDH Van" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'KDH Van') ? 'selected' : '' ?>>KDH Van</option>
                    <option value="Dolphin" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Dolphin') ? 'selected' : '' ?>>Dolphin</option>
                </select>

                <label for="stars" class="ms-3">Filter by Rating:</label>
                <select name="stars" id="stars" class="form-select d-inline-block w-auto mx-2">
                    <option value="">All</option>
                    <option value="5" <?= (isset($_GET['stars']) && $_GET['stars'] == '5') ? 'selected' : '' ?>>5 Stars
                    </option>
                    <option value="4" <?= (isset($_GET['stars']) && $_GET['stars'] == '4') ? 'selected' : '' ?>>4 Stars
                    </option>
                    <option value="3" <?= (isset($_GET['stars']) && $_GET['stars'] == '3') ? 'selected' : '' ?>>3 Stars
                    </option>
                    <option value="2" <?= (isset($_GET['stars']) && $_GET['stars'] == '2') ? 'selected' : '' ?>>2 Stars
                    </option>
                    <option value="1" <?= (isset($_GET['stars']) && $_GET['stars'] == '1') ? 'selected' : '' ?>>1 Star
                    </option>
                </select>

                <button type="submit" class="btn btn-dark floating-element-fast">Apply</button>
                <?php if (!empty($_GET['vehicle']) || !empty($_GET['stars'])): ?>
                    <a href="testimonial.php" class="btn btn-outline-secondary ms-2 floating-element-fast">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Average Rating Display -->
    <?php if ($starCount > 0): ?>
        <div class="text-center mb-4 floating-element">
            <h5 class="text-secondary">Average Rating:</h5>
            <div class="star-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star<?= $i <= round(num: $avgRating) ? ' filled' : '' ?>">&#9733;</span>
                <?php endfor; ?>
                <span class="ms-2">(<?= $avgRating ?> out of 5 from <?= $starCount ?> reviews)</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <div class="container">
        <div class="row justify-content-center">
            <?php foreach ($documents as $doc) {
                // User information
                $userId = isset($doc['userId']) ? (string) $doc['userId'] : null;
                $userName = $userId ? ($userData[$userId]['userName'] ?? 'Unknown User') : 'Unknown User';
                $userImage = $userId ? ($userData[$userId]['profileImage'] ?? 'img/profile-icon.png') : 'img/profile-icon.png';

                // Fetch vehicle details if vehicleId exists
                $vehicleName = 'N/A';
                $vehicleType = 'N/A';

                if (isset($doc['vehicleId'])) {
                    $vehicle = $vehiclesCollection->findOne(filter: ['_id' => new MongoDB\BSON\ObjectId($doc['vehicleId'])]);
                    if ($vehicle) {
                        $vehicleName = $vehicle['vehicle_name'] ?? $doc['vehicleName'] ?? 'N/A';
                        $vehicleType = $vehicle['type'] ?? $doc['vehicleType'] ?? 'N/A';
                    }
                } else {
                    $vehicleName = $doc['vehicleName'] ?? 'N/A';
                    $vehicleType = $doc['vehicleType'] ?? 'N/A';
                }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="review-card floating-element">
                        <img src="<?= htmlspecialchars(string: $userImage) ?>" alt="User"
                            class="review-avatar floating-element-slow">
                        <h5><?= htmlspecialchars(string: $userName) ?></h5>
                        <p class="review-date">
                            <?php
                            if ($doc['date'] instanceof MongoDB\BSON\UTCDateTime) {
                                echo htmlspecialchars(string: $doc['date']->toDateTime()->format('Y-m-d h:i A'));
                            } else {
                                echo htmlspecialchars(string: $doc['date']);
                            }
                            ?>
                        </p>
                        <p class="vehicle-info">
                            <strong><?= htmlspecialchars(string: $vehicleName) ?></strong>
                            (<?= htmlspecialchars(string: $vehicleType) ?>)
                        </p>
                        <div class="rating">
                            <?php for ($i = 0; $i < (int) $doc['starCount']; $i++) {
                                echo '<span class="star filled floating-element-fast">&#9733;</span>';
                            } ?>
                        </div>
                        <p class="review-comment">"<?= htmlspecialchars(string: $doc['comment']) ?>"</p>

                        <!-- Report Button -->
                        <button type="button" class="btn-report floating-element-fast" data-bs-toggle="modal"
                            data-bs-target="#reportModal" data-review-id="<?= (string) $doc['_id'] ?>">
                            <i class="fas fa-flag"></i> Report
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content floating-element-slow">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="report_review_id" id="report_review_id">
                        <div class="mb-3">
                            <label for="report_reason" class="form-label">Reason for reporting</label>
                            <select class="form-select floating-element-fast" name="report_reason" id="report_reason"
                                required>
                                <option value="" selected disabled>Select a reason</option>
                                <option value="Inappropriate content">Inappropriate content</option>
                                <option value="False information">False information</option>
                                <option value="Spam or advertising">Spam or advertising</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="report_details" class="form-label">Additional details (optional)</label>
                            <textarea class="form-control floating-element-fast" name="report_details"
                                id="report_details" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary floating-element-fast"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="report_review" class="btn btn-danger floating-element-fast">Submit
                            Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Post Review -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="container my-5 floating-element">
            <div class="review-form-container">
                <h4>Leave a Review</h4>
                <form action="submit_review.php" method="POST" class="p-4">
                    <input type="hidden" name="userId" value="<?= $_SESSION['user_id'] ?>">

                    <input type="hidden" name="vehicleType" id="vehicleType">
                    <input type="hidden" name="vehicleName" id="vehicleName">

                    <div class="mb-3">
                        <label for="orderId" class="form-label">Booking</label>
                        <select name="orderId" id="orderId" class="form-select floating-element-fast" required
                            onchange="fillVehicleDetails(this)">
                            <?php
                            try {
                                // Get current user
                                $currentUser = $usersCollection->findOne(filter: ['UserName' => $_SESSION['username']]);
                                if (!$currentUser) {
                                    throw new Exception(message: "User not found");
                                }

                                $userId = $currentUser['_id'];

                                // Get user's pending bookings that have a vehicle_id
                                $userBookings = $bookingsCollection->find(
                                    filter: [
                                        'user_id' => $userId,
                                        'status' => 'pending',
                                        'vehicle_id' => ['$exists' => true, '$ne' => null]
                                    ]
                                )->toArray();

                                if (empty($userBookings)) {
                                    echo '<option value="" disabled selected>No pending bookings available</option>';
                                } else {
                                    echo '<option value="" disabled selected>Select your booking</option>';

                                    foreach ($userBookings as $booking) {
                                        // Get vehicle details from vehicles collection
                                        $vehicle = $vehiclesCollection->findOne(filter: ['_id' => $booking['vehicle_id']]);
                                        $vehicleDetails = $vehicle ? "{$vehicle['type']} - {$vehicle['vehicle_name']}" : 'Unknown Vehicle';

                                        // Format date range
                                        $dateRange = htmlspecialchars(string: $booking['pickup_date'] ?? '') . ' - ' .
                                            htmlspecialchars(string: $booking['dropoff_date'] ?? '');

                                        echo sprintf(
                                            '<option value="%s" data-vehicle="%s" data-dates="%s">%s</option>',
                                            (string) $booking['_id'],
                                            htmlspecialchars(string: $vehicleDetails),
                                            htmlspecialchars(string: $dateRange),
                                            $dateRange
                                        );
                                    }
                                }
                            } catch (Exception $e) {
                                echo '<option value="" disabled selected>Error loading bookings</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="vehicleDisplay" class="form-label">Vehicle</label>
                        <input type="text" id="vehicleDisplay" class="form-control floating-element-fast" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Booking Dates</label>
                        <input type="text" id="dateRange" class="form-control floating-element-fast" readonly>
                    </div>

                    <div class="mb-3 text-center">
                        <label class="form-label d-block">Rating</label>
                        <div class="rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>" class="floating-element-fast">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <div class="position-relative">
                            <textarea name="comment" id="comment" rows="4" class="form-control floating-element-fast"
                                required placeholder="Speak or type your review..."></textarea>
                            <button type="button" onclick="startVoiceInput()"
                                class="btn btn-sm position-absolute floating-element-fast"
                                style="right: 5px; bottom: 5px; background: transparent; border: none; font-size: 1.2rem;">
                                🎤
                            </button>
                        </div>
                        <p id="voiceStatus" class="small text-muted mt-1"></p>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success px-4 floating-element-fast" <?= empty($userBookings) ? 'disabled' : '' ?>>Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="container d-flex justify-content-center my-5 floating-element">
            <div class="card shadow-lg border-0 floating-element-wide"
                style="background: linear-gradient(90deg, #0d4f4b 0%, #128377 100%); color: #fff; border-radius: 18px; max-width: 400px;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-lock fa-2x floating-element-slow" style="color:#e8f5f1;"></i>
                    </div>
                    <h5 class="card-title mb-2">Post a Review</h5>
                    <p class="card-text mb-3">Please <a href="auth.php"
                            style="color:#ffe082; text-decoration:underline;">login</a> to leave a review.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include 'components/footer.php'; ?>

    <!-- VOICE-TO-TEXT functionality-->
    <script>
        // Voice-to-text function for review input
        function startVoiceInput() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                document.getElementById("voiceStatus").innerText = "Voice input not supported in your browser";
                return;
            }

            const recognition = new SpeechRecognition();
            recognition.lang = 'en-US';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            document.getElementById("voiceStatus").innerText = "Listening... Speak now";

            recognition.start();

            recognition.onresult = function (event) {
                const transcript = event.results[0][0].transcript;
                document.getElementById("comment").value = transcript;
                document.getElementById("voiceStatus").innerText = "Voice captured";
            };

            recognition.onerror = function (event) {
                document.getElementById("voiceStatus").innerText = "Error: " + event.error;
            };

            recognition.onend = function () {
                if (!document.getElementById("comment").value) {
                    document.getElementById("voiceStatus").innerText = "Try speaking again";
                }
            };
        }
    </script>

    <script>
        function fillVehicleDetails(select) {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.dataset.vehicle) {
                const vehicleParts = selectedOption.dataset.vehicle.split(' - ');
                document.getElementById('vehicleType').value = vehicleParts[0] || '';
                document.getElementById('vehicleName').value = vehicleParts[1] || '';
                document.getElementById('vehicleDisplay').value = selectedOption.dataset.vehicle;
                document.getElementById('dateRange').value = selectedOption.dataset.dates;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var reportModal = document.getElementById('reportModal');
            if (reportModal) {
                reportModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var reviewId = button.getAttribute('data-review-id');
                    var modalInput = reportModal.querySelector('#report_review_id');
                    modalInput.value = reviewId;
                });
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>