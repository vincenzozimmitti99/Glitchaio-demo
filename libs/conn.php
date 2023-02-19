<?php
	$host = "localhost";
	$username = "root";
	$password = "n$*REdra7ucabusow4Af";
	$db = "glitchaio";
	
	$conn = mysqli_connect($host, $username, $password, $db);
	
	if(!$conn){
        die('Could not connect: ' . mysqli_error());
    }
	
	function runQuery($query){
		global $conn;
		return mysqli_query($conn, $query);
	}
	
	function runMultiQuery($query){
		global $conn;
		$result = mysqli_multi_query($conn, $query);
		while(mysqli_more_results($conn)){
			mysqli_next_result($conn);
		}
		return $result;
	}
	
	runQuery("SET NAMES utf8");
?>