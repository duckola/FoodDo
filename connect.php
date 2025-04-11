<?php 
	$conn = new mysqli('localhost', 'root','','foodo');
	
	if (!$conn){
		die (mysqli_error($mysqli));
	}
		
?>