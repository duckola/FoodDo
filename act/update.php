<?php
include 'connect.php';

// Check if the 'uid' parameter is set in the URL
if (isset($_GET['uid'])) {
    $id = $_GET['uid'];

    // Fetch the existing record from the database
    $sql_select = "SELECT tbluser.firstname, tbluser.lastname, tbluser.gender, tbluser.usertype, tbluser.username, tblstudent.program, tblstudent.yearlevel
                   FROM tbluser
                   INNER JOIN tblstudent ON tbluser.uid = tblstudent.uid
                   WHERE tbluser.uid = ?";
    $stmt_select = mysqli_prepare($connection, $sql_select);

    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id);
        mysqli_stmt_execute($stmt_select);
        $result = mysqli_stmt_get_result($stmt_select);

        if ($row = mysqli_fetch_assoc($result)) {
            // Data found, display the form
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $gender = $row['gender'];
            $usertype = $row['usertype'];
            $username = $row['username'];
            $program = $row['program'];
            $yearlevel = $row['yearlevel'];
        } else {
            echo "Record not found.";
            exit;
        }
        mysqli_stmt_close($stmt_select);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
        exit;
    }
} else {
    echo "Invalid request: Missing 'uid' parameter.";
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $new_firstname = $_POST['txtfirstname'];
    $new_lastname = $_POST['txtlastname'];
    $new_gender = $_POST['txtgender'];
    $new_usertype = $_POST['txtusertype'];
    $new_username = $_POST['txtusername'];
    $new_program = $_POST['txtprogram'];
    $new_yearlevel = $_POST['txtyearlevel'];

    // Update tbluser
    $sql_update_user = "UPDATE tbluser SET firstname = ?, lastname = ?, gender = ?, usertype = ?, username = ? WHERE uid = ?";
    $stmt_update_user = mysqli_prepare($connection, $sql_update_user);

    if ($stmt_update_user) {
        mysqli_stmt_bind_param($stmt_update_user, "sssssi", $new_firstname, $new_lastname, $new_gender, $new_usertype, $new_username, $id);
        if (mysqli_stmt_execute($stmt_update_user)) {
            // Update tblstudent
            $sql_update_student = "UPDATE tblstudent SET program = ?, yearlevel = ? WHERE uid = ?";
            $stmt_update_student = mysqli_prepare($connection, $sql_update_student);

            if ($stmt_update_student) {
                mysqli_stmt_bind_param($stmt_update_student, "sii", $new_program, $new_yearlevel, $id);
                if (mysqli_stmt_execute($stmt_update_student)) {
                    echo "<script language='javascript'>
                            alert('Record updated successfully.');
                            window.location.href = 'dashboard.php';
                          </script>";
                } else {
                    echo "Error updating tblstudent: " . mysqli_error($connection);
                }
                mysqli_stmt_close($stmt_update_student);
            } else {
                echo "Error preparing statement for tblstudent: " . mysqli_error($connection);
            }
        } else {
            echo "Error updating tbluser: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt_update_user);
    } else {
        echo "Error preparing statement for tbluser: " . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student Record</title>
    <link rel="stylesheet" href="css/stylesDemo.css">
</head>
<body>
    <div class="header-container">
        <p><h2>Update Student Record</h2></p>
    </div>

    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label for="txtfirstname">Firstname:</label>
                <input type="text" name="txtfirstname" id="txtfirstname" value="<?php echo $firstname; ?>">
            </div>
            <div class="form-group">
                <label for="txtlastname">Lastname:</label>
                <input type="text" name="txtlastname" id="txtlastname" value="<?php echo $lastname; ?>">
            </div>
            <div class="form-group">
                <label for="txtgender">Gender:</label>
                <select name="txtgender" id="txtgender">
                    <option value="">----</option>
                    <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="txtusertype">User Type:</label>
                <select name="txtusertype" id="txtusertype">
                    <option value="">----</option>
                    <option value="student" <?php if ($usertype == 'student') echo 'selected'; ?>>Student</option>
                    <option value="employee" <?php if ($usertype == 'employee') echo 'selected'; ?>>Employee</option>
                </select>
            </div>
            <div class="form-group">
                <label for="txtusername">Username:</label>
                <input type="text" name="txtusername" id="txtusername" value="<?php echo $username; ?>">
            </div>
            <div class="form-group">
                <label for="txtprogram">Program:</label>
                <select name="txtprogram" id="txtprogram">
                    <option value="">----</option>
                    <option value="bsit" <?php if ($program == 'bsit') echo 'selected'; ?>>BSIT</option>
                    <option value="bscs" <?php if ($program == 'bscs') echo 'selected'; ?>>BSCS</option>
                </select>
            </div>
            <div class="form-group">
                <label for="txtyearlevel">Year Level:</label>
                <select name="txtyearlevel" id="txtyearlevel">
                    <option value="">----</option>
                    <option value="1" <?php if ($yearlevel == 1) echo 'selected'; ?>>1</option>
                    <option value="2" <?php if ($yearlevel == 2) echo 'selected'; ?>>2</option>
                    <option value="3" <?php if ($yearlevel == 3) echo 'selected'; ?>>3</option>
                    <option value="4" <?php if ($yearlevel == 4) echo 'selected'; ?>>4</option>
                </select>
            </div>
            <div class="form-group">
                <input class="btn" type="submit" name="btnUpdate" value="Update">
            </div>
        </form>
    </div>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
