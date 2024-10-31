<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lagonoy_farmers";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input data to prevent SQL injection
function sanitize_input($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = sanitize_input($_POST["password"]);

    // Check if the admin account exists with a fixed username
    $sql = "SELECT user_id, password FROM admin WHERE username = 'Admin'";
    $result = $conn->query($sql);

    // Predefined default password for first-time admin creation
    $default_password = 'admin246';

    if ($result->num_rows === 0) {
        // Admin account doesn't exist, only create it if the correct password is entered
        if ($password === $default_password) {
            // Hash the password before storing it
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

            // Insert the admin account with the default password
            $create_sql = "INSERT INTO admin (username, password) VALUES ('Admin', '$hashed_password')";
            if ($conn->query($create_sql) === TRUE) {
                // Display success message and use JavaScript to redirect after 3 seconds
                echo "<p>Admin account created successfully. Redirecting to dashboard...</p>";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'admin_dashboard.php';
                        }, 3000); // Redirect after 3 seconds
                      </script>";
                exit();
            } else {
                echo "Error creating admin account: " . $conn->error;
                exit(); // Stop execution if admin account creation fails
            }
        } else {
            // The password entered doesn't match the default password for admin account creation
            echo "Invalid password for admin account creation.";
            exit(); // Stop execution if the wrong password is entered
        }
    } else {
        // Admin account exists, proceed with login
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];

        // Verify the password
        if (password_verify($password, $stored_hash)) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            // Redirect to the admin dashboard
            echo "<p>Login successful. Redirecting to dashboard...</p>";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'admin_dashboard.php';
                    }, 1000); // Redirect after 1 second
                  </script>";
            exit();
        } else {
            echo "Invalid password.";
        }
    }
}

$conn->close();