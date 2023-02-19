<?php
include "conn.php";

if(isset($_POST['submit'])){
	if($_POST['submit']==="adminLogin"){
		adminLogin($_POST["nickname"], $_POST["password"]);
	}
}

function adminLogin($nickname, $password){
	global $conn;
	$nickname = strip_tags(mysqli_real_escape_string($conn, trim($nickname)));
	$password = strip_tags(mysqli_real_escape_string($conn, $password));
	
	$query = "SELECT password, privilege FROM user WHERE nickname='$nickname'";
	//echo $query;
	$result = runQuery($query);
	$row = mysqli_fetch_array($result);
	if(password_verify($password, $row['password'])){
		session_start();
		$_SESSION['isLogged'] = 1;
		$_SESSION['nickname'] = $nickname;
		$_SESSION['privilege'] = $row['privilege'];
		if($row['privilege']==="admin")
			header("Location: ../admin/admin.php");
		else if($row['privilege']==="writer")
			header("Location: ../admin/writer.php");
	} else {
		header("Location: ../admin/");
	}
}
?>