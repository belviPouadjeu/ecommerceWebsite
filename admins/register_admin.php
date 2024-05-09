<?php

// Redirect to HTTPS if not already using it
/*if ($_SERVER['HTTPS'] !== 'on') {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit();
}*/
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

    // Function to generate a new remember token and update the database
    function updateRememberToken($conn, $adminId)
    {
        // Generate a new remember token
        $rememberToken = bin2hex(random_bytes(32)); // Adjust the length of the token as needed

        // Set expiration time for the token (e.g., 30 days from now)
        $expirationTime = time() + (30 * 24 * 60 * 60); // 30 days * 24 hours * 60 minutes * 60 seconds

        // Store the token and its expiration time in the database
        $stmt = $conn->prepare("UPDATE admins SET remember_token = ?, token_expires_at = ? WHERE id = ?");
        $stmt->execute([$rememberToken, date('Y-m-d H:i:s', $expirationTime), $adminId]); 

        // Set the remember token in the user's browser as a cookie
        setcookie('remember_token', $rememberToken, $expirationTime, '/', '', true, true);
    }

    // Check for remembered sessions
    if (!isset($_SESSION['admin_id']) && isset($_COOKIE['remember_token'])) {
        // Check if the remember token exists in the database and is valid
        $stmt = $conn->prepare("SELECT * FROM admins WHERE remember_token = ? AND token_expires_at > ?");
        $stmt->execute([$_COOKIE['remember_token'], date('Y-m-d H:i:s')]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Automatically authenticate the user
            $_SESSION['admin_id'] = $admin['id'];
        
            // Redirect to dashboard or authenticated page
            header("Location: dashboard.php");
            exit();
        } else {
            // Remove invalid token from the user's browser
            setcookie('remember_token', null, -1, '/');
        }
    }


    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate form inputs
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $pass = trim(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $cpass = trim(filter_input(INPUT_POST, 'cpass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        // Add regex validation for password
        if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]{8}$/', $pass)) {
            $errorMessage[] = 'Password is 8 long and must contain letters, numbers and special characters.';
        }

        if (!preg_match('/^[A-Za-z]+$/', $name)) {
            $errorMessage[] = 'Name must contain only letters.';
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            $errorMessage[] = 'Invalid email format.';
        }

        if ($pass !== $cpass) {
            $errorMessage[] = 'Password and confirm password do not match.';
        } else {
            // Encrypt password and generate remember token
            $key = Key::createNewRandomKey();
            $encryptedPassword = encrypt($pass, $key);
            $rememberToken = bin2hex(random_bytes(32)); // Adjust the length of the token as needed

            $encryptedPassword = encrypt($pass, $key);
            $encryptedPasswordKey = $encryptedPassword['key']; // Récupérez la clé de cryptage

            // Insert admin data into the database
            // $stmt = $conn->prepare("INSERT INTO admins (name, email, password, remember_token) VALUES (:name, :email, :password, :remember_token)");
            // $stmt = $conn->prepare("INSERT INTO admins (name, email, password, password_key, remember_token) VALUES (:name, :email, :password, :password_key, :remember_token)");
            // $stmt->bindParam(':name', $name);
            // $stmt->bindParam(':email', $email);
            // $stmt->bindParam(':password', $encryptedPassword['data']);
            // $stmt->bindParam(':remember_token', $rememberToken);
            // $stmt->bindParam(':password_key', $encryptedPassword['key']);
            // $stmt->bindParam(':password_key', $encryptedPasswordKey);
            $stmt = $conn->prepare("INSERT INTO admins (name, email, password, password_key, remember_token) VALUES (:name, :email, :password, :password_key, :remember_token)");

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $encryptedPassword['data']);
            $stmt->bindParam(':remember_token', $rememberToken);
            $stmt->bindParam(':password_key', $encryptedPassword['key']);

            if ($stmt->execute()) {
                // Get the ID of the newly inserted admin
                $adminId = $conn->lastInsertId();

                // Update the token expiration time for the new admin
                updateRememberToken($conn, $adminId);

                // Redirect to dashboard or authenticated page
                header("Location: dashboard.php");
                exit();
            } else {
                $errorMessage[] = 'Failed to insert admin.';
            }
        }
    }

    // Define constants for roles
    define('ROLE_SUPER_ADMIN', 'ROLE_SUPER_ADMIN');
    define('ROLE_REGULAR_ADMIN', 'ROLE_REGULAR_ADMIN');

    try {
        // Fetch all admins from the database
        $stmt = $conn->query("SELECT id FROM admins");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Iterate over admins and assign roles based on position
        foreach ($admins as $key => $admin) {
            // Assign ROLE_SUPER_ADMIN to the first 3 admins, ROLE_REGULAR_ADMIN to the rest
            $role = ($key < 1) ? ROLE_SUPER_ADMIN : ROLE_REGULAR_ADMIN;
            $adminId = $admin['id'];

            // Update the admin's role in the database
            $updateStmt = $conn->prepare("UPDATE admins SET role = ? WHERE id = ?");
            $success = $updateStmt->execute([$role, $adminId]);

            /*if ($success) {
                echo "Admin with ID $adminId has been assigned the role: $role <br>";
            } else {
                echo "Failed to update role for admin with ID $adminId <br>";
                // Output any specific error information for debugging
                var_dump($updateStmt->errorInfo());
            }*/
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }


    // Display error messages
    /*if  (!empty($errorMessage)) {
        foreach ($errorMessage as $error) {
            echo "<div class='message'><span>{$error}</span><i class='fas fa-times'></i></div>";
        }
    }*/
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
    <title>Register Admin</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <!-- Display error message -->
    <?php include '../components/admin_header.php';?>

    <!-- Register admin section starts -->
    <section class="form_container">
        <form action="" method="post" autocomplete="off">
            <h3>Register New Admin</h3>
            <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
            <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">

            <div style="position: relative;">
                <input type="password" id="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box password-field" autocomplete="off" oninput="this.value = this.value.replace(/\s/g, '')">
                <span class="close-eye-icon image" id="close-eye"></span> <!-- Added id for targeting in JavaScript -->
                <span class="open-eye-icon image" id="open-eye"></span> <!-- Added id for targeting in JavaScript -->
            </div>

            <div class="progress hidden"> <!-- Initially hidden -->
                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <input type="password" name="cpass" required placeholder="Confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

            <!-- The Remember Me checkbox here -->
            <div class="remember-container">
                <input type="checkbox" name="remember_me" id="remember_me" required>
                <label for="remember_me" class="js-remeber-me"><strong>Remember Me </strong></label>
            </div>
            
            <input type="submit" value="Register Now" class="btn" name="submit">
        </form>
    </section>
    <!-- Register admin section ends -->

    <!-- custom js file link -->
    <script src="../js/admin.js"></script>

</body>
</html>
