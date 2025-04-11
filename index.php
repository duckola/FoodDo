<?php
session_start(); // Start the session at the very beginning
include 'connect.php'; // Include database connection

$signup_error = '';
$login_error = '';
$signup_success = '';

// --- Sign Up Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_submit'])) {
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'])); // Keep as string
    $password = trim($_POST['password']); // Get password

    // Basic Validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $signup_error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_error = "Invalid email format.";
    } elseif (strlen($password) < 6) { // Example: Enforce minimum password length
         $signup_error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM user WHERE email = ?";
        $stmt_check = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $signup_error = "Email address already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $default_role = 'customer';

            $insert_sql = "INSERT INTO user (name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $insert_sql);

            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "sssss", $name, $email, $phone, $hashed_password, $default_role);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $signup_success = "Registration successful! You can now log in.";

                } else {
                    $signup_error = "Error during registration. Please try again. " . mysqli_error($conn); // Log detailed error in development
                    // $signup_error = "Error during registration. Please try again."; // Production error
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                 $signup_error = "Error preparing statement. Please try again.";
            }
        }
        mysqli_stmt_close($stmt_check);
    }
}

// --- Login Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Email and password are required.";
    } else {
        // Prepare SELECT statement
        $sql = "SELECT user_id, name, email, password, role FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    session_regenerate_id(true); // Prevent session fixation

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role']; // Store role

                    $redirect_url = ''; // Initialize redirect URL

                    switch ($user['role']) {
                        case 'admin':
                            $redirect_url = 'admin_dashboard.php';
                            break;
                        case 'customer':
                            $redirect_url = 'customer_dashboard.php'; // Example target page
                            break;
                        case 'employee':
                            $redirect_url = 'employee_dashboard.php'; // Example target page
                            break;
                        default:
                            // Optional: Handle unexpected roles - redirect to a default page or show error
                            // Redirecting to landing page as a safe default
                            $redirect_url = 'landingpage.php';
                            // You might want to log this occurrence
                            error_log("User logged in with unexpected role: " . $user['role'] . " (User ID: " . $user['user_id'] . ")");
                            break;
                    }

                    // Perform the redirection if a URL was determined
                    if (!empty($redirect_url)) {
                        header("Location: " . $redirect_url);
                        exit(); // Important: stop script execution after redirect
                    } else {
                        $login_error = "Login successful, but could not determine destination based on role.";
                    }

                } else {
                    // Invalid password
                    $login_error = "Invalid email or password.";
                }
            } else {
                // No user found with that email
                $login_error = "Invalid email or password.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $login_error = "Error preparing login statement. Please try again.";
        }
    }

}

mysqli_close($conn); // Close connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to FoodDo - Login/Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Optional: For icons -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Add styles for error/success messages if not in styles.css */
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            text-align: center;
        }
        .success-message {
            color: green;
            font-size: 0.9em;
            margin-top: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="images/food_logo.png" alt="FoodDo Logo" class="logo">
            <h2>FoodDo</h2>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Login/Sign Up</a></li>
                    <li><a href="about-us.php">About Us</a></li>
                    <li><a href="contact-us.php">Contact Us</a></li>
                    <li><a href="landingpage.php">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="line-separator"></div>

    <!-- Main Content Area for Login/Sign Up -->
    <div class="container" id="container">
        <!-- Sign Up Form -->
        <div class="form-container sign-up-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h1>Create Account</h1>
                <!-- Social Icons (Optional) -->
                <!-- <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span> -->
                <?php if (!empty($signup_error)): ?>
                    <p class="error-message"><?php echo $signup_error; ?></p>
                <?php endif; ?>
                 <?php if (!empty($signup_success)): ?>
                    <p class="success-message"><?php echo $signup_success; ?></p>
                <?php endif; ?>
                <input type="text" name="name" placeholder="Name" required value="<?php echo isset($_POST['name']) && empty($signup_success) ? htmlspecialchars($_POST['name']) : ''; ?>" />
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) && empty($signup_success) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                <input type="tel" name="phone" placeholder="Phone Number" required value="<?php echo isset($_POST['phone']) && empty($signup_success) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="signup_submit">Sign Up</button>
            </form>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h1>Sign in</h1>
                <!-- Social Icons (Optional) -->
                <!-- <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your account</span> -->
                 <?php if (!empty($login_error)): ?>
                    <p class="error-message"><?php echo $login_error; ?></p>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) && isset($_POST['login_submit']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                <input type="password" name="password" placeholder="Password" required />
                <a href="#">Forgot your password?</a> <!-- Add link to password reset page if available -->
                <button type="submit" name="login_submit">Sign In</button>
            </form>
        </div>

        <!-- Overlay Panels -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content Area -->

    <footer>
        <p>Lee Jasmin Adolfo | BSCS - 2</p>
    </footer>

    <script>
        // Script for toggling panels
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        // Script for active navigation link (optional)
        const navLinks = document.querySelectorAll("nav ul li a");
        navLinks.forEach(function(link) {
             if (link.href === window.location.href) {
                 link.classList.add("active");
             }
        });

         // Check session storage on index.php load (to handle redirect from landing page sign up)
         document.addEventListener('DOMContentLoaded', (event) => {
            if (sessionStorage.getItem('showSignUp') === 'true') {
                container.classList.add("right-panel-active");
                sessionStorage.removeItem('showSignUp'); // Clear the flag
            }

            // If there was a signup error/success, keep the signup panel active on reload
            <?php if (($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_submit'])) || !empty($signup_error) || !empty($signup_success)): ?>
                container.classList.add("right-panel-active");
            <?php endif; ?>
            // If there was a login error, keep the signin panel active (default)
            // No extra JS needed unless signup was active before reload
         });

    </script>
</body>
</html>
