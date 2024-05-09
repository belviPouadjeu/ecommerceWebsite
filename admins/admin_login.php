<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Include Composer's autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    // Connection to the database
    include '../components/connection.php';

    session_start();

    $errorMessage = [];

    // Import the necessary classes from the library
    use Defuse\Crypto\Crypto;
    use Defuse\Crypto\Key;

    // Function to encrypt data
    function encrypt($data, $key)
    {
        // Generate a new encryption key if not provided
        if (!$key) {
            $key = Key::createNewRandomKey();
        }

        // Encrypt the data using the key
        $encryptedData = Crypto::encrypt($data, $key);

        // Return the encrypted data along with the key
        return [
            'data' => $encryptedData,
            'key' => $key->saveToAsciiSafeString() // Convert key to string for storage
        ];
    }

    // Function to decrypt data
    function decrypt($data, $key)
    {
        // Load the key from the string representation
        $key = Key::loadFromAsciiSafeString($key);

        // Decrypt the data using the key
        $decryptedData = Crypto::decrypt($data, $key);

        // Return the decrypted data
        return $decryptedData;
    }

    // Function to revoke Remember Me functionality for a user
    function revokeRememberMe($conn, $adminId) {
        // Clear remember token and expiration time from the database
        $stmt = $conn->prepare("UPDATE admins SET remember_token = NULL, token_expires_at = NULL WHERE id = ?");
        $stmt->execute([$adminId]);

        // Remove remember token cookie from the user's browser
        setcookie('remember_token', null, -1, '/');
    }

    // Handle user request to revoke Remember Me functionality
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['revoke_remember_me'])) {
        // Revoke Remember Me functionality for the current user
        revokeRememberMe($conn, $_SESSION['admin_id']);
        // Optionally, you can redirect the user to their account settings page or a confirmation page
        // header("Location: account_settings.php");
        // exit();
    }
    
    // Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form inputs
    // $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $pass = trim(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Afficher la requête SQL générée
    echo "SELECT * FROM admins WHERE name = '$name' OR email = '$email'";

    // Query the database to fetch the admin with the provided email or username
    $stmt = $conn->prepare("SELECT * FROM admins WHERE name = ? OR email = ?");
    $stmt->execute([$name, $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && isset($admin['password_key'])) {
        // Decrypt the stored password
        $decryptedPassword = decrypt($admin['password'], $admin['password_key']);

        // Verify if the provided password matches the decrypted password
        if ($pass === $decryptedPassword) {
            // Passwords match, user is authenticated
            $_SESSION['admin_id'] = $admin['id'];

            // Redirect to dashboard or authenticated page
            header("Location: dashboard.php");
            exit();
        } else {
            // Passwords don't match, display error message
            $errorMessage[] = 'Invalid name, email, or password.';
        }
    } else {
        // Admin with the provided email or username doesn't exist, display error message
        $errorMessage[] = 'Admin doesn\'t exist. Please register first';
    }
}

    // Display message
    if (!empty($errorMessage)) {
        foreach ($errorMessage as $error) {
            echo '
            <div class="message">
                <span>' . $error . '</span>
                <i class="fas fa-times" onclick="removeErrorMessage(this);"></i>
            </div>';
        }
    }
    echo "<script>
    function removeErrorMessage(element){
        element.parentElement.remove();
    }
    </script>"

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login admin</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <!-- section admin login starts -->

    <section class="form_container">

        <form action="" method="post" autocomplete="off">
            <h3>Login now</h3>
            <!-- <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off"> -->
            <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">

            <div style="position: relative;">
                <input type="password" id="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box password-field" autocomplete="off">
                <span class="close-eye-icon image" id="close-eye"></span> <!-- Added id for targeting in JavaScript -->
                <span class="open-eye-icon image" id="open-eye"></span> <!-- Added id for targeting in JavaScript -->
            </div>

            <div class="revoke-container">

                <div class="revoke">
                    <input type="checkbox" name="revoke_remember_me" id="revoke_remember_me">
                    <label for="revoke_remember_me" class="js-revoke-remeber-me"><strong>Revoke Remember Me</strong></label>
                </div>

                <div class="text">
                <span>⚠️ Please read this scripture : </span>By checking this box <strong>( Revoke Remember Me )</strong>, you will revoke the 'Remember Me' functionality for your account. 
                    This means that your session will not persist across browser sessions, enhancing the security of your account, especially if you're using a shared or public computer. 
                    We recommend using this option if you're accessing the site from a device that others may also use.
                </div>

            </div>
            
            <input type="submit" value="login Now" class="btn" name="submit">
        </form>
    </section>
    <!-- section admin login ends -->

    <!-- custom js file link -->
    <script src="../js/admin.js"></script>
</body>
</html>