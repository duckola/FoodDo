<?php 
	$connection = new mysqli('localhost', 'root','','dbf3adolfo');
	
	if (!$connection){
		die (mysqli_error($mysqli));
	}
		
?>