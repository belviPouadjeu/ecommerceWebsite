<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the login page if not logged in
    // header("Location: login.php");
    // exit();
}

// Include the database connection
include 'components/connection.php';

// Initialize variables
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_remember_me'])) {
        // Update the user's preference for "Remember Me"

        // Check if the remember_me checkbox is checked
        $rememberMe = isset($_POST['remember_me']) ? 1 : 0;

        // Update the user's record in the database
        $adminId = $_SESSION['admin_id'];
        $stmt = $conn->prepare("UPDATE admins SET remember_me = ? WHERE id = ?");
        if ($stmt->execute([$rememberMe, $adminId])) {
            // Success message
            $errorMessage = 'Your preference for "Remember Me" has been updated.';
        } else {
            // Error message
            $errorMessage = 'Failed to update your preference for "Remember Me". Please try again.';
        }
    } elseif (isset($_POST['revoke_access'])) {
        // Revoke access by clearing remember token and expiration time from the database

        // Update the user's record in the database
        $adminId = $_SESSION['admin_id'];
        $stmt = $conn->prepare("UPDATE admins SET remember_token = NULL, token_expires_at = NULL WHERE id = ?");
        if ($stmt->execute([$adminId])) {
            // Success message
            $errorMessage = 'Access has been revoked. You will be logged out from remembered sessions.';
            // Redirect to logout
            header("Location: logout.php");
            exit();
        } else {
            // Error message
            $errorMessage = 'Failed to revoke access. Please try again.';
        }
    }
}

// Fetch the user's current "Remember Me" preference
$adminId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT remember_me FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Extract the current value of "Remember Me" from the user's record
$rememberMeValue = $user['remember_me'] ?? 0; // Default to 0 if not set
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file here -->
</head>

<body>
    <h1>Access Setting</h1>
    <div class="container">
        <h2>Account Settings</h2>
        <?php if (!empty($errorMessage)) : ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="remember_me">Remember Me</label>
                <input type="checkbox" id="remember_me" name="remember_me" <?php echo $rememberMeValue ? 'checked' : ''; ?>>
            </div>
            <button type="submit" name="update_remember_me">Update</button>
        </form>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" name="revoke_access">Revoke Access</button>
        </form>
    </div>
</body>

</html>
