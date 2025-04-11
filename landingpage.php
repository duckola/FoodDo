<?php    
    include 'connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to FoodDo</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Use the same CSS as index.php -->
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="images/food_logo.png" alt="FoodDo Logo" class="logo">
            <h2>FoodDo</h2>
            <nav>
                <ul>
                    <!-- Update links to point to the correct PHP files -->
                    <li><a href="index.php">Login</a></li>
                    <li><a href="about-us.php">About Us</a></li>
                    <li><a href="contact-us.php">Contact Us</a></li>
                    <li><a href="landingpage.php" class="active">Home</a></li> <!-- Added Home link -->
                </ul>
            </nav>
        </div>
    </header>
    <div class="line-separator"></div> <!-- Optional separator like in index.php -->

    <div class="landing-image-container" style="text-align: center; margin: 20px 0; padding-top: 20px;">
        <img src="images/landing_image.png" alt="Welcome to FoodDo - Food ordering and management" style="max-width: 100%; height: auto; border-radius: 8px;">
    </div>


    <!-- Main Content Area for Landing Page -->
    <div class="landing-container" style="padding: 40px 20px; text-align: center; min-height: 60vh;">
        <h1>Welcome to FoodDo!</h1>
        <p style="font-size: 1.2em; margin-top: 20px;">Streamline Your Food Retail Business - From Stock to Checkout.</p>
        <p style="margin-top: 15px;">Manage inventory, process orders, and engage customers efficiently.</p>
        <div style="margin-top: 30px;">
            <a href="index.php" class="ghost" style="padding: 12px 25px; margin: 0 10px; border: 1px solid #FF4B2B; background-color: transparent; color: #FF4B2B; text-decoration: none; border-radius: 20px; font-weight: bold; text-transform: uppercase;">Login</a>
            <!-- If you have a separate registration page -->
            <!-- <a href="register.php" class="ghost" style="padding: 12px 25px; margin: 0 10px;">Sign Up</a> -->
             <!-- Or link back to index.php's sign up overlay -->
             <a href="index.php#signUp" class="ghost" id="signUpLanding" style="padding: 12px 25px; margin: 0 10px; border: 1px solid #FF4B2B; background-color: transparent; color: #FF4B2B; text-decoration: none; border-radius: 20px; font-weight: bold; text-transform: uppercase;">Sign Up</a>
        </div>
    </div>
    <!-- End Main Content Area -->

    <footer>
        <p>Lee Jasmin Adolfo | BSCS - 2</p>
    </footer>

    <script>
        // Script for active navigation link (optional, but good for consistency)
        const navLinks = document.querySelectorAll("nav ul li a");

        navLinks.forEach(function(link) {
            // If you want to automatically set active based on current page:
             if (link.href === window.location.href) {
                 link.classList.add("active");
             }

            link.addEventListener("click", function(event) {
                // This part might be less useful if the page reloads anyway
                // navLinks.forEach(function(link) {
                //     link.classList.remove("active");
                // });
                // this.classList.add("active");
            });
        });

        // If you want the Sign Up button to trigger the overlay on index.php
        const signUpButtonLanding = document.getElementById('signUpLanding');
        if (signUpButtonLanding) {
            signUpButtonLanding.addEventListener('click', function(e) {
                // Prevent default jump
                e.preventDefault();
                // Store in session storage that sign up should be shown
                sessionStorage.setItem('showSignUp', 'true');
                // Redirect to index.php
                window.location.href = 'index.php';
            });
        }

         // Check session storage on index.php load (add this script to index.php as well)
         /*
         document.addEventListener('DOMContentLoaded', (event) => {
            if (sessionStorage.getItem('showSignUp') === 'true') {
                document.getElementById('container').classList.add("right-panel-active");
                sessionStorage.removeItem('showSignUp'); // Clear the flag
            }
         });
         */

    </script>
</body>
</html>
