<?php    
    include 'connect.php';    
    //require_once 'includes/header.php'; 
?>
<!-- css -->
<link rel="stylesheet" href="css/stylesDemo.css">

<div class = "header-container">
    <p><h2>User Registration Page</h2></p>
</div>  

<div class ="form-container">
	<form method="post">
		<pre>
Firstname:<input type="text" name="txtfirstname">
Lastname:<input type="text" name="txtlastname">			
Gender:
			<select name="txtgender">
			 <option value="">----</option>
			 <option value="Male">Male</option>
			 <option value="Female">Female</option>
			</select>
User Type:
			<select name="txtusertype">
			  <option value="">----</option>
			  <option value="student">Student</option>
			  <option value="employee">Employee</option>
			</select>
Username:<input type="text" name="txtusername">	
Password:<input type="password" name="txtpassword">	
Program:
			<select name="txtprogram">
			 <option value="">----</option>
			 <option value="bsit">BSIT</option>
			 <option value="bscs">BSCS</option>
			</select>
Year Level:
				<select name="txtyearlevel">
				<option value="">----</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
			</select>
			
		<div class="form-group">
            <input class="btn" type="submit" name="btnRegister" value="Register">
        </div>		
	</pre>
	</form>
</div>

<?php require_once 'includes/footer.php'; ?>


<?php	
	if(isset($_POST['btnRegister'])){		
		//retrieve data from form and save the value to a variable
		//for tbluser
		$fname=$_POST['txtfirstname'];		
		$lname=$_POST['txtlastname'];
		$gender=$_POST['txtgender'];
		$utype=$_POST['txtusertype'];
		$uname=$_POST['txtusername'];		
		$pword=$_POST['txtpassword'];	
		$hashedpw = password_hash($pword, PASSWORD_DEFAULT);
		
		//for tblstudent
		$prog=$_POST['txtprogram'];		
		$yearlevel=$_POST['txtyearlevel'];		
		
						
		//save data to tbluser	
		$sql1 ="Insert into tbluser(firstname,lastname,gender, usertype, username, password) 
			values('".$fname."','".$lname."','".$gender."','".$utype."', '".$uname."', '".$hashedpw."')";
		mysqli_query($connection,$sql1);
				
		$last_id = mysqli_insert_id($connection);
		 
		$sql2 ="Insert into tblstudent(program, yearlevel, uid) values('".$prog."','.$yearlevel.','.$last_id.')";
		mysqli_query($connection,$sql2);
		echo "<script language='javascript'>
			alert('New record saved.');
		      </script>";
		header("location: dashboard.php");
		
			
		
	}
		

?>
