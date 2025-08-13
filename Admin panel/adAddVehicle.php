<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle - Admin Panel</title>
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
                    <a href="adLogout.php">
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
            <div class="page-header">
                <h2>Add New Vehicle</h2>
                <a href="adVehicles.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                </a>
            </div>

            <?php
            require '../auth-config.php';
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $vehicleName = trim($_POST['vehicle_name'] ?? '');
                $vehicleType = $_POST['vehicle_type'] ?? '';
                $seatCount = trim($_POST['seat_count'] ?? '');
                $acNac = $_POST['ac_nac'] ?? '';
                $featuresArr = $_POST['features'] ?? [];
                $features = is_array($featuresArr) ? implode(', ', $featuresArr) : '';
                
                $errors = [];
                
                // Validation
                if (empty($vehicleName)) {
                    $errors[] = "Vehicle name is required";
                }

                    if (empty($vehicleType)) {
                $errors[] = "Vehicle type is required";
                 }
                
                if (empty($seatCount) || !is_numeric($seatCount) || $seatCount < 1) {
                    $errors[] = "Valid seat count is required";
                }
                
                if (empty($acNac)) {
                    $errors[] = "Please select AC/NAC";
                }
                
                // Handle file upload
                $photo = '';
                if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'C:/wamp64/www/ThisaraTravels/Admin panel/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileInfo = pathinfo($_FILES['vehicle_photo']['name']);
                    $extension = strtolower($fileInfo['extension']);
                    // Validate file type
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($extension, $allowedTypes)) {
                        $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed";
                    } else {
                        // Generate unique filename
                        $filename = uniqid('veh_', true) . '.' . $extension;
                        $uploadPath = $uploadDir . $filename;
                        if (move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $uploadPath)) {
                            $photo = 'uploads/' . $filename; // Store DB value as uploads/filename.jpg
                        } else {
                            $errors[] = "Failed to upload photo";
                        }
                    }
                }
                
                // If no errors, save vehicle
                if (empty($errors)) {
                    $vehicle = [
                        'vehicle_name' => $vehicleName,
                        'type' => $vehicleType,
                        'seat_count' => (int)$seatCount,
                        'ac_nac' => $acNac,
                        'features' => $features,
                        'vehiclePhoto' => $photo
                    ];
                    
                    // Insert into MongoDB
                    if (insertVehicle($vehicle)) {
                        // Redirect to vehicles list with success message
                        header('Location: adVehicles.php?success=1');
                        exit;
                    } else {
                        $errors[] = "Failed to add vehicle to database.";
                    }
                }
            }
            ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="vehicle_name" class="form-label">Vehicle Name *</label>
                        <input type="text" id="vehicle_name" name="vehicle_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['vehicle_name'] ?? ''); ?>" required>
                    </div>

                            <div class="form-group">
            <label for="vehicle_type" class="form-label">Vehicle Type *</label>
            <select id="vehicle_type" name="vehicle_type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="van" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'van') ? 'selected' : ''; ?>>Van</option>
                <option value="car" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'car') ? 'selected' : ''; ?>>Car</option>
                
            </select>
        </div>

                    <div class="form-group">
                        <label for="seat_count" class="form-label">Number of Seats *</label>
                        <input type="number" id="seat_count" name="seat_count" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['seat_count'] ?? ''); ?>" 
                               min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label for="ac_nac" class="form-label">AC/NAC *</label>
                        <select id="ac_nac" name="ac_nac" class="form-control" required>
                            <option value="">Select AC/NAC</option>
                            <option value="AC" <?php echo (isset($_POST['ac_nac']) && $_POST['ac_nac'] === 'AC') ? 'selected' : ''; ?>>AC</option>
                            <option value="NAC" <?php echo (isset($_POST['ac_nac']) && $_POST['ac_nac'] === 'NAC') ? 'selected' : ''; ?>>Non-AC</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Special Features</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="wifi" name="features[]" value="WiFi" 
                                       <?php echo (isset($_POST['features']) && in_array('WiFi', $_POST['features'])) ? 'checked' : ''; ?>>
                                <label for="wifi">WiFi</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="music" name="features[]" value="Music System" 
                                       <?php echo (isset($_POST['features']) && in_array('Music System', $_POST['features'])) ? 'checked' : ''; ?>>
                                <label for="music">Music System</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="coolbox" name="features[]" value="Cool Box" 
                                       <?php echo (isset($_POST['features']) && in_array('Cool Box', $_POST['features'])) ? 'checked' : ''; ?>>
                                <label for="coolbox">Cool Box</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vehicle_photo" class="form-label">Vehicle Photo</label>
                        <div class="file-upload">
                            <input type="file" id="vehicle_photo" name="vehicle_photo" accept="image/*">
                            <label for="vehicle_photo" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <br>Click to upload or drag and drop
                                <br><small>JPG, JPEG, PNG, GIF up to 5MB</small>
                            </label>
                        </div>
                        <div id="photoPreview"></div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Add Vehicle
                        </button>
                        <a href="adVehicles.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Photo preview functionality
        document.getElementById('vehicle_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="img-preview">
                        <p class="form-text">Selected: ${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
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