<?php
session_start();
include 'connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); 
    exit();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_role'] === 'employee') {
        header("Location: employee_dashboard.php"); 
    } else {

        header("Location: logout.php"); 
    }
    exit();
}

// --- Get User Info ---
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Customer';

$currentPage = 'customer_dashboard'; // Set this for potential active link styling in header
$logout_path = "logout.php"; // Path to logout script

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - <?php echo $user_name; ?></title>
    <link rel="stylesheet" href="css/styles.css">

</head>
<body>

<header>
        <div class="logo-container">
            <img src="images/food_logo.png" alt="FoodDo Logo" class="logo">
            <h2>FoodDo</h2>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="<?php echo $logout_path; ?>">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="customer-dashboard-container">
        <h1>Welcome, <?php echo $user_name; ?>!</h1>
        <p class="welcome-message">Manage your orders, browse products, and update your profile.</p>

        <div class="customer-actions">
            <div class="action-card">
                <h2>Browse Products</h2>
                <p>Explore our menu and find your favorite items.</p>
                <!-- Make sure 'products.php' exists or change the link -->
                <a href="products.php" class="btn">View Products</a>
            </div>

            <div class="action-card">
                <h2>My Orders</h2>
                <p>Check the status and details of your past orders.</p>
                <!-- Make sure 'my_orders.php' exists or change the link -->
                <a href="my_orders.php" class="btn">View My Orders</a>
            </div>

            <div class="action-card">
                <h2>My Profile</h2>
                <p>Update your contact information or change your password.</p>
                <!-- Make sure 'profile.php' exists or change the link -->
                <a href="profile.php" class="btn">Edit Profile</a>
            </div>

            <!-- Add more sections as needed -->
            <!--
            <div class="action-card">
                <h2>My Wishlist</h2>
                <p>View items you have saved for later.</p>
                <a href="wishlist.php" class="btn">View Wishlist</a>
            </div>
            -->

        </div> <!-- /.customer-actions -->

    </div> <!-- /.customer-dashboard-container -->

    <?php
    // --- Include Footer ---
    // Ensure the path to your footer file is correct
    include_once 'includes/footer.php';
    ?>

</body>
</html>
