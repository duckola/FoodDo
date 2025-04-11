<?php
include 'connect.php';

// Check if the 'uid' parameter is set in the URL
if (isset($_GET['uid'])) {
    $id = $_GET['uid'];

    // delete from tblstudent
    $sql_student = "DELETE FROM tblstudent WHERE uid = ?";
    $stmt_student = mysqli_prepare($connection, $sql_student);

    if ($stmt_student) {
        mysqli_stmt_bind_param($stmt_student, "i", $id);
        if (mysqli_stmt_execute($stmt_student)) {
            //delete user
            $sql_user = "DELETE FROM tbluser WHERE uid = ?";
            $stmt_user = mysqli_prepare($connection, $sql_user);

            if ($stmt_user) {
                mysqli_stmt_bind_param($stmt_user, "i", $id);
                if (mysqli_stmt_execute($stmt_user)) {
                    echo "<script language='javascript'>
                            alert('Record deleted successfully.');
                            window.location.href = 'dashboard.php';
                          </script>";
                } else {
                    echo "Error deleting from tbluser: " . mysqli_error($connection);
                }
                mysqli_stmt_close($stmt_user);
            } else {
                echo "Error preparing statement for tbluser: " . mysqli_error($connection);
            }
        } else {
            echo "Error deleting from tblstudent: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt_student);
    } else {
        echo "Error preparing statement for tblstudent: " . mysqli_error($connection);
    }
} else {
    echo "Invalid request: Missing 'uid' parameter.";
}
?>
