<?php
require '../auth-config.php';

// Check if admin is logged in (you may need to implement proper admin authentication)
// For now, we'll assume admin is logged in via session

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                handleProfileUpdate();
                break;
            case 'change_password':
                handlePasswordChange();
                break;
        }
    }
}

function handleProfileUpdate() {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Basic validation
    if (empty($username) || empty($email)) {
        $_SESSION['error'] = 'Username and email are required.';
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        return;
    }
    
    // Check if email is already taken by another user (excluding current admin)
    $existingUser = getUsersCollection()->findOne([
        'Email' => $email,
        '_id' => ['$ne' => new MongoDB\BSON\ObjectId($_SESSION['user_id'] ?? '')]
    ]);
    
    if ($existingUser) {
        $_SESSION['error'] = 'Email address is already in use.';
        return;
    }
    
    // Check if username is already taken by another user (excluding current admin)
    $existingUsername = getUsersCollection()->findOne([
        'UserName' => $username,
        '_id' => ['$ne' => new MongoDB\BSON\ObjectId($_SESSION['user_id'] ?? '')]
    ]);
    
    if ($existingUsername) {
        $_SESSION['error'] = 'Username is already in use.';
        return;
    }
    
    // Update admin profile
    try {
        $result = getUsersCollection()->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'] ?? '')],
            [
                '$set' => [
                    'UserName' => $username,
                    'Email' => $email
                ]
            ]
        );
        
        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = 'Profile updated successfully!';
        } else {
            $_SESSION['error'] = 'No changes were made.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating profile. Please try again.';
    }
}

function handlePasswordChange() {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = 'All password fields are required.';
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'New passwords do not match.';
        return;
    }
    
    if (strlen($newPassword) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        return;
    }
    
    // Get current admin user
    $adminUser = getUsersCollection()->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'] ?? '')
    ]);
    
    if (!$adminUser) {
        $_SESSION['error'] = 'Admin user not found.';
        return;
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $adminUser['Password'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        return;
    }
    
    // Update password
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = getUsersCollection()->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'] ?? '')],
            [
                '$set' => [
                    'Password' => $hashedPassword
                ]
            ]
        );
        
        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = 'Password changed successfully!';
        } else {
            $_SESSION['error'] = 'Error changing password. Please try again.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error changing password. Please try again.';
    }
}

// Get current admin profile data
$adminUser = null;

// Check if admin is logged in via session
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    try {
        $adminUser = getUsersCollection()->findOne([
            '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
        ]);
        
        if ($adminUser) {
            // Debug: Log the admin user data
            error_log('Admin user found: ' . json_encode($adminUser));
        } else {
            $_SESSION['error'] = 'Admin user not found in database.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error fetching admin data: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Admin authentication required.';
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Thisara Travels</title>
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
                <li><a href="adReviews.php"><i class="fas fa-star"></i><span>Reviews</span></a></li>
                <li><a href="adSettings.php" class="active"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="adLogout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2>Admin Settings</h2>
                    <p>Manage your profile and account settings</p>
                </div>
                <div class="header-actions">
                    <a href="adLogout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; ?>
                    <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; ?>
                    <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Profile Settings -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3><i class="fas fa-user"></i> Profile Settings</h3>
                        <p>Update your personal information</p>
                    </div>
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($adminUser['UserName'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminUser['Email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                        <p>Update your account password</p>
                    </div>
                    <form method="POST" class="settings-form" id="passwordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-input-group">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-input-group">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small>Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="password-input-group">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                        <p>Your account details</p>
                    </div>
                    <div class="account-info">
                        <div class="info-row">
                            <span class="info-label">Username:</span>
                            <span class="info-value"><?php echo htmlspecialchars($adminUser['UserName'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($adminUser['Email'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Account Type:</span>
                            <span class="info-value"><?php echo htmlspecialchars(ucfirst($adminUser['role'] ?? 'Admin')); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Verified:</span>
                            <span class="info-value"><?php echo ($adminUser['Verified'] ?? false) ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Registration Date:</span>
                            <span class="info-value">
                                <?php 
                                if (isset($adminUser['date'])) {
                                    echo htmlspecialchars($adminUser['date']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle function - improved version
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            const button = input.parentElement.querySelector('.password-toggle');
            if (!button) return;
            
            const icon = button.querySelector('i');
            if (!icon) return;
            
            // Toggle password visibility
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Ensure password toggles work after page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking for duplicate toggles...');
            // Remove any duplicate password toggles
            const passwordGroups = document.querySelectorAll('.password-input-group');
            console.log('Found', passwordGroups.length, 'password groups');
            
            passwordGroups.forEach((group, index) => {
                const toggles = group.querySelectorAll('.password-toggle');
                console.log('Group', index, 'has', toggles.length, 'toggles');
                
                if (toggles.length > 1) {
                    console.log('Removing first toggle from group', index);
                    toggles[0].remove();
                } else if (toggles.length === 1) {
                    console.log('Group', index, 'has exactly 1 toggle - keeping it');
                } else {
                    console.log('Group', index, 'has no toggles');
                }
            });
            
            // Additional check: ensure only one toggle per password field
            const allToggles = document.querySelectorAll('.password-toggle');
            console.log('Total toggles found:', allToggles.length);
            
            // If we have more toggles than password groups, remove extras
            if (allToggles.length > passwordGroups.length) {
                console.log('Found more toggles than password groups, removing extras');
                for (let i = passwordGroups.length; i < allToggles.length; i++) {
                    if (allToggles[i]) {
                        console.log('Removing extra toggle at index', i);
                        allToggles[i].remove();
                    }
                }
            }
        });
        
        // Password confirmation validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html> 