<?php
session_start(); // Start the session at the very beginning
include 'connect.php'; // Include database connection (using $conn for 'foodo' db)

// --- Authentication Check ---
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // If not logged in or not an admin, redirect to login page
    header("Location: index.php");
    exit(); // Stop script execution

    echo "You are not authorized to access this page.";
    return;
}

// --- Fetch Summary Data ---
$user_count = 0;
$order_count = 0;
$foodchain_count = 0;

// Get total users
$result_users = mysqli_query($conn, "SELECT COUNT(*) as count FROM user");
if ($result_users && mysqli_num_rows($result_users) > 0) {
    $user_count = mysqli_fetch_assoc($result_users)['count'];
}

// Get total orders
$result_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
if ($result_orders && mysqli_num_rows($result_orders) > 0) {
    $order_count = mysqli_fetch_assoc($result_orders)['count'];
}

// Get total food chains
$result_foodchains = mysqli_query($conn, "SELECT COUNT(*) as count FROM foodchain");
if ($result_foodchains && mysqli_num_rows($result_foodchains) > 0) {
    $foodchain_count = mysqli_fetch_assoc($result_foodchains)['count'];
}

// --- Fetch Data for Graphs ---

// 1. Orders and Revenue Over Time (Last 7 Days)
$orderDates = [];
$orderCounts = [];
$revenueData = [];
$sevenDaysAgo = date('Y-m-d', strtotime('-6 days')); // Include today

$sql_orders_time = "SELECT
                        DATE(orderdate) as order_day,
                        COUNT(*) as order_count,
                        SUM(price * quantity) as total_revenue
                    FROM orders
                    WHERE orderdate >= ?
                    GROUP BY DATE(orderdate)
                    ORDER BY order_day ASC";

$stmt_orders_time = mysqli_prepare($conn, $sql_orders_time);
if ($stmt_orders_time) {
    mysqli_stmt_bind_param($stmt_orders_time, "s", $sevenDaysAgo);
    mysqli_stmt_execute($stmt_orders_time);
    $result_orders_time = mysqli_stmt_get_result($stmt_orders_time);

    while ($row = mysqli_fetch_assoc($result_orders_time)) {
        $orderDates[] = date('M d', strtotime($row['order_day'])); // Format date like 'Apr 12'
        $orderCounts[] = $row['order_count'];
        $revenueData[] = $row['total_revenue'] ?? 0; // Use null coalescing operator for safety
    }
    mysqli_stmt_close($stmt_orders_time);
} else {
    // Handle error - log it or display a message
    error_log("Error preparing orders time statement: " . mysqli_error($conn));
}


// 2. User Role Distribution
$roleLabels = [];
$roleCounts = [];
$sql_roles = "SELECT role, COUNT(*) as count FROM user GROUP BY role";
$result_roles = mysqli_query($conn, $sql_roles);

if ($result_roles && mysqli_num_rows($result_roles) > 0) {
    while ($row = mysqli_fetch_assoc($result_roles)) {
        $roleLabels[] = ucfirst($row['role']); // Capitalize role name
        $roleCounts[] = $row['count'];
    }
}

// 3. Popular Food Types (Top 5 by Quantity)
$foodTypeLabels = [];
$foodTypeQuantities = [];
$sql_foodtypes = "SELECT foodtype, SUM(quantity) as total_quantity
                  FROM orders
                  GROUP BY foodtype
                  ORDER BY total_quantity DESC
                  LIMIT 5";
$result_foodtypes = mysqli_query($conn, $sql_foodtypes);

if ($result_foodtypes && mysqli_num_rows($result_foodtypes) > 0) {
    while ($row = mysqli_fetch_assoc($result_foodtypes)) {
        $foodTypeLabels[] = $row['foodtype'];
        $foodTypeQuantities[] = $row['total_quantity'];
    }
}



$logout_path = "logout.php"; // Path to logout script
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodDo</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Use the main styles -->
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="images/food_logo.png" alt="FoodDo Logo" class="logo">
            <h2>FoodDo</h2>
            <nav>
                <ul>
                    <!-- Admin Navigation -->
                    <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <!-- <li><a href="manage_foodchains.php">Manage Food Chains</a></li>
                    <li><a href="manage_orders.php">View Orders</a></li> -->
                    <li><a href="<?php echo $logout_path; ?>">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <!-- Main Admin Dashboard Content -->
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <p class="welcome-message">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?>!</strong></p>

        <!-- Summary Cards -->
        <!-- <div class="summary-cards"> -->
            <!-- <div class="card"> -->
                <!-- <h3>Total Users</h3> -->
                <!-- <p><?php echo $user_count; ?></p> -->
            <!-- </div> -->
            <!-- <div class="card"> -->
                <!-- <h3>Total Orders</h3> -->
                <!-- <p><?php echo $order_count; ?></p> -->
            <!-- </div> -->
            <!-- <div class="card"> -->
                <!-- <h3>Food Chains</h3> -->
                <!-- <p><?php echo $foodchain_count; ?></p> -->
            <!-- </div> -->
        <!-- </div> -->

        <!-- Graph Section -->
        <div class="graph-section">
            <h2>Performance Overview</h2>
            <div class="graph-grid">
                <div class="graph-container">
                    <canvas id="ordersChart"></canvas>
                </div>
                <div class="graph-container">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="graph-container">
                     <canvas id="rolesChart"></canvas>
                </div>
                 <div class="graph-container">
                     <canvas id="foodTypesChart"></canvas>
                </div>
            </div>
        </div>


        <!-- Management Sections -->
        <div class="management-sections">
             <!-- Existing management cards -->
            <div class="section-card">
                <h2>Manage Users</h2>
                <p>View, add, edit, or remove system users (customers, employees, managers).</p>
                <a href="manage_users.php" class="btn">Go to Users</a>
            </div>
            <div class="section-card">
                <h2>Manage Customers</h2>
                <p>View and manage customer details and payment methods.</p>
                <a href="manage_customers.php" class="btn">Go to Customers</a>
            </div>
             <div class="section-card">
                <h2>Manage Employees</h2>
                <p>View, assign roles, and manage employee information.</p>
                <a href="manage_employees.php" class="btn">Go to Employees</a>
            </div>
            <div class="section-card">
                <h2>Manage Food Chains</h2>
                <p>Add, edit, or remove food chain branches and their details.</p>
                <a href="manage_foodchains.php" class="btn">Go to Food Chains</a>
            </div>
            <div class="section-card">
                <h2>View Orders</h2>
                <p>Monitor and review all customer orders placed through the system.</p>
                <a href="manage_orders.php" class="btn">Go to Orders</a>
            </div>
            <div class="section-card">
                <h2>Manage Suppliers</h2>
                <p>Maintain the list of suppliers and their contact information.</p>
                <a href="manage_suppliers.php" class="btn">Go to Suppliers</a>
            </div>
        </div>

    </div>
    <!-- End Main Admin Dashboard Content -->

    <footer>
        <p>Lee Jasmin Adolfo | BSCS - 2</p>
    </footer>

    <script>
        // Pass PHP data to JavaScript
        const orderDates = <?php echo json_encode($orderDates); ?>;
        const orderCounts = <?php echo json_encode($orderCounts); ?>;
        const revenueData = <?php echo json_encode($revenueData); ?>;
        const roleLabels = <?php echo json_encode($roleLabels); ?>;
        const roleCounts = <?php echo json_encode($roleCounts); ?>;
        const foodTypeLabels = <?php echo json_encode($foodTypeLabels); ?>;
        const foodTypeQuantities = <?php echo json_encode($foodTypeQuantities); ?>;

        // Chart Configurations
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false, 
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    font: {
                        size: 16
                    }
                }
            },
            scales: { 
                y: {
                    beginAtZero: true
                }
            }
        };

        // 1. Orders Over Time Chart
        const ctxOrders = document.getElementById('ordersChart')?.getContext('2d');
        if (ctxOrders) {
            new Chart(ctxOrders, {
                type: 'line',
                data: {
                    labels: orderDates,
                    datasets: [{
                        label: 'Orders per Day (Last 7 Days)',
                        data: orderCounts,
                        borderColor: 'rgb(255, 75, 43)', // FoodDo Orange
                        backgroundColor: 'rgba(255, 75, 43, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    ...chartOptions, // Spread common options
                    plugins: { // Override title specifically
                       ...chartOptions.plugins,
                        title: {
                           ...chartOptions.plugins.title,
                            display: true,
                            text: 'Orders Trend (Last 7 Days)'
                        }
                    }
                }
            });
        } else {
            console.error("Canvas element with ID 'ordersChart' not found.");
        }


        // 2. Revenue Over Time Chart
        const ctxRevenue = document.getElementById('revenueChart')?.getContext('2d');
         if (ctxRevenue) {
            new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: orderDates, // Use the same dates as orders chart
                    datasets: [{
                        label: 'Revenue per Day (Last 7 Days)',
                        data: revenueData,
                        borderColor: 'rgb(54, 162, 235)', // Blue
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                 options: {
                    ...chartOptions,
                     plugins: {
                       ...chartOptions.plugins,
                        title: {
                           ...chartOptions.plugins.title,
                            display: true,
                            text: 'Revenue Trend (Last 7 Days)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { // Format Y-axis as currency (optional)
                                callback: function(value, index, values) {
                                    return '$' + value.toFixed(2); // Adjust currency symbol/format if needed
                                }
                            }
                        }
                    }
                }
            });
        } else {
             console.error("Canvas element with ID 'revenueChart' not found.");
        }

        // 3. User Role Distribution Chart
        const ctxRoles = document.getElementById('rolesChart')?.getContext('2d');
         if (ctxRoles) {
            new Chart(ctxRoles, {
                type: 'doughnut', // Or 'pie'
                data: {
                    labels: roleLabels,
                    datasets: [{
                        label: 'User Roles',
                        data: roleCounts,
                        backgroundColor: [ // Add more colors if you have more roles
                            'rgba(255, 99, 132, 0.7)', // Red
                            'rgba(54, 162, 235, 0.7)', // Blue
                            'rgba(255, 206, 86, 0.7)', // Yellow
                            'rgba(75, 192, 192, 0.7)', // Green
                            'rgba(153, 102, 255, 0.7)', // Purple
                            'rgba(255, 159, 64, 0.7)' // Orange
                        ],
                        hoverOffset: 4
                    }]
                },
                 options: {
                    ...chartOptions,
                     plugins: {
                       ...chartOptions.plugins,
                        title: {
                           ...chartOptions.plugins.title,
                            display: true,
                            text: 'User Role Distribution'
                        }
                    },
                    scales: {} // Remove scales for pie/doughnut
                }
            });
        } else {
             console.error("Canvas element with ID 'rolesChart' not found.");
        }

        // 4. Popular Food Types Chart
        const ctxFoodTypes = document.getElementById('foodTypesChart')?.getContext('2d');
         if (ctxFoodTypes) {
            new Chart(ctxFoodTypes, {
                type: 'bar',
                data: {
                    labels: foodTypeLabels,
                    datasets: [{
                        label: 'Total Quantity Ordered (Top 5)',
                        data: foodTypeQuantities,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', // Teal
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 1
                    }]
                },
                 options: {
                    ...chartOptions,
                     plugins: {
                       ...chartOptions.plugins,
                        title: {
                           ...chartOptions.plugins.title,
                            display: true,
                            text: 'Top 5 Popular Food Types (by Quantity)'
                        }
                    },
                    indexAxis: 'y', // Optional: Makes it a horizontal bar chart
                }
            });
        } else {
             console.error("Canvas element with ID 'foodTypesChart' not found.");
        }


        // Script for active navigation link (optional)
        const navLinks = document.querySelectorAll("nav ul li a");
        navLinks.forEach(function(link) {
             if (link.href === window.location.href) {
                 link.classList.add("active");
             }
        });
    </script>
</body>
</html>
