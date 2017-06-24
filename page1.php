<?php
	session_start();
	//connect to database
	$db = mysqli_connect("localhost", "root", "vdxd", "examination");
	
	mysqli_autocommit($db, false);
	$flag = true;

	if(!isset($_SESSION['username'])){
    	header("location: home1.php");
	}

	if (isset($_POST['testregister_btn'])){
		$username = mysqli_real_escape_string($db, $_SESSION['username']);
		$subject = mysqli_real_escape_string($db, $_POST['subject']);
		$examtype = mysqli_real_escape_string($db, $_POST['examtype']);
		$examperiod = mysqli_real_escape_string($db, $_POST['examperiod']);
		$examarea = mysqli_real_escape_string($db, $_POST['examarea']);
		$textservice = mysqli_real_escape_string($db, $_POST['textservice']);

		$examareaname = explode(' ',$examarea);
		$examareaname = $examareaname[2].' '.$examareaname[3];
		

		if($examtype == 'written'){
			$sql = "SELECT wregstart,wregend FROM examperiodlist WHERE examperiod = '$examperiod'";
			$result = mysqli_query($db, $sql) or die(mysqli_error($db));
			
			$examperiod1 = mysqli_fetch_row($result);
			$startdate = DATE_FORMAT(date_Create($examperiod1[0]),'m-d');
			$enddate = DATE_FORMAT(date_Create($examperiod1[1]),'m-d');
			$cur = date('m-d');
			if(($cur < $startdate) || ($cur > $enddate)){
				$flag = false;
			}
		}else if($examtype == 'performance'){
			$sql = "SELECT pregstart,pregend FROM examperiodlist WHERE examperiod = '$examperiod'";
			$result = mysqli_query($db, $sql) or die(mysqli_error($db));
			
			$examperiod1 = mysqli_fetch_row($result);
			$startdate = DATE_FORMAT(date_Create($examperiod1[0]),'m-d');
			$enddate = DATE_FORMAT(date_Create($examperiod1[1]),'m-d');
			$cur = date('m-d');
			if(($cur < $startdate) || ($cur > $enddate)){
				$flag = false;
			}
		}

		$result = mysqli_query($db, "SELECT examareaid FROM examarea WHERE examareaname = '$examareaname'") or die(mysqli_error($db));
		$examareaid = mysqli_fetch_row($result);

		$result = mysqli_query($db, "SELECT subjectid FROM subject WHERE subjectname = '$subject'") or die(mysqli_error($db));
		$subjectid = mysqli_fetch_row($result);

		$sql = "INSERT INTO testregister(username,date,subjectid,examyear,examperiod,examtype,examareaid,textservice) VALUES('".$username."',curdate(),'".$subjectid[0]."', YEAR(curdate()),'".$examperiod."','".$examtype."','".$examareaid[0]."','".$textservice."');";
		$result = mysqli_query($db, $sql);

		if($examtype == 'written'){
			$sql = "SELECT DATE_FORMAT((SELECT writtentest FROM examperiodlist WHERE examperiod = '$examperiod'), '%M %D');";
			$result = mysqli_query($db, $sql);
			$examdate = mysqli_fetch_row($result);
			$_SESSION['examdate'] = $examdate[0].', '.date("Y");
		}else{
			$sql = "SELECT DATE_FORMAT((SELECT performancetest FROM examperiodlist WHERE examperiod = '$examperiod'), '%M %D');";
			$result = mysqli_query($db, $sql);
			$examdate = mysqli_fetch_row($result);
			$_SESSION['examdate'] = $examdate[0].', '.date("Y");
		}

		if($flag){
			mysqli_commit($db);
			$_SESSION['subject'] = $subject;
			$_SESSION['examareaname'] = $examareaname;
			$_SESSION['textservice'] = $textservice;
			header("location: page2.php");
		} else{
			mysqli_rollback($db);
			$_SESSION['msg'] = 'Check registration dates';
			echo $_SESSION['msg'];
		}		
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Register for the test</title>
</head>
<body>
<div class = "header">
	<h1>Register for the test</h1>
</div>


<form method="post" action="page1.php">
	<table>
		<tr>
			<td>username:</td>
			<td><?php echo $_SESSION['username'];?>
  			</td>
		</tr>
		<tr>
			<td>subject:</td>
			<td><select name = "subject">
				<option>Electric Railway</option>
				<option>Electricity</option>
				<option>Information Processing</option>
				<option>Office Automation</option>
				<option>Metal</option>	
				<option>Rolling</option></td>
		</tr>
		<tr>
			<td>exam period:</td>
			<td><select name = "examperiod">
				<?php
					$epresult = mysqli_query($db, "SELECT examperiod FROM examperiodlist") or die(mysqli_error($db));
					while($epresultrow = mysqli_fetch_assoc($epresult)){
						echo '<option>'.$epresultrow["examperiod"].'</option>';
					}
				?>				
			</td>
		</tr>
		<tr>
			<td>exam type:</td>
			<td><select name = "examtype">
				<option>written</option>
				<option>performance</option></td>
		</tr>
		<tr>
			<td>area:</td>
			<td><select name = "examarea">
				<option>Seoul - Sehwa HS</option>
				<option>Seoul - Ewha HS</option>
				<option>Anyang - Gwiin MS</option>	
				<option>Anyang - Sinki MS</option>	
				<option>Daegu - Kyungdong ES</option>
				<option>Daegu - Sungdong ES</option>
				<option>Busan - Haeundae MS</option></td>
		</tr>
		<tr>
			<td>text service:</td>
			<td><input type="radio" name="textservice" value = Yes> Yes<br>
  				<input type="radio" name="textservice" value = No> No<br></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="testregister_btn" value="Register"></td>
		</tr>

	</table>
</form>

<br><br>
<div><a href="home.php">Home</a></div>
<div><a href="logout.php">Logout</a></div>
</body>
</html>