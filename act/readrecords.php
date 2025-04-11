<?php
include 'connect.php';

if (!$connection) {
    die('Could not connect: ' . mysqli_connect_error());
}

$query = 'SELECT * from  tblstudent INNER JOIN tbluser ON tbluser.uid = tblstudent.uid'; // Use INNER JOIN for clarity
$resultset = mysqli_query($connection, $query);

// Check if the query was successful
if (!$resultset) {
    die('Error in query: ' . mysqli_error($connection)); 
}

?>
