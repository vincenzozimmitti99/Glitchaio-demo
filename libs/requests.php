<?php
	session_start();
	include "../libs/core.php";
	global $conn;

	if(isset($_POST['request'])){
		if($_POST['request']=="addArticle"){
			addArticle($_POST['title'], $_POST['thumbnail'], $_POST['text'], $_POST['alias'], $_POST['tags']);
		} else if($_POST['request']=="editArticle"){
			editArticle($_POST['article'], $_POST['title'], $_POST['thumbnail'], $_POST['text'], $_POST['alias'], $_POST['tags']);
		}
	}
	
	function addArticle($title, $thumbnail, $text, $alias, $tags){
		global $conn;
		//Sicurezza injection
		$title = mysqli_real_escape_string($conn, trim($title));
		$thumbnail = mysqli_real_escape_string($conn, trim($thumbnail));
		$text = mysqli_real_escape_string($conn, trim($text));
		$writer = mysqli_real_escape_string($conn, trim($_SESSION["nickname"]));
		$alias = mysqli_real_escape_string($conn, trim($alias));
		
		$tags = json_decode($tags);
		$length = count($tags);
		
		$query = "INSERT INTO article(`title`, `text`, `thumbnail`, `writer`, `alias`) VALUES('$title', '$text', '$thumbnail', '$writer', " . (isset($alias) && $alias!=""?"'$alias'":"NULL") . ")";
		$result = runQuery($query);
		
		$id = mysqli_insert_id($conn);
		if($result){
			if($length>0){
				$query = "INSERT INTO articletags(`article`, `tag`) VALUES ";
				for($i=0;$i<$length;$i++){
					if($i<$length-1){
						$query .= "('$id', (SELECT id FROM tag WHERE name='$tags[$i]')), ";
					} else $query .= "('$id', (SELECT id FROM tag WHERE name='$tags[$i]'))";
				}
				$result = runQuery($query);
				if($result){
					header("Location: ../admin/admin.php?result=addsuccess");
				} else {
					header("Location: ../admin/admin.php?result=addfailure");
				}
			} else {
				header("Location: ../admin/admin.php?result=addsuccess");
			}
		} else {
			header("Location: ../admin/admin.php?result=addfailure");
		}
	}

	function editArticle($id, $title, $thumbnail, $text, $alias, $tags){
		global $conn;
		//Sicurezza injection
		$id = mysqli_real_escape_string($conn, trim($id));
		$title = mysqli_real_escape_string($conn, trim($title));
		$thumbnail = mysqli_real_escape_string($conn, trim($thumbnail));
		$text = mysqli_real_escape_string($conn, trim($text));
		$alias = mysqli_real_escape_string($conn, trim($alias));
		//$writer = mysqli_real_escape_string($conn, trim($_SESSION["nickname"])); //Dato per ultima modifica forse?
		
		$tags = json_decode($tags);
		$length = count($tags);
		
		$query = "UPDATE article SET `title`='$title', `text`='$text', `thumbnail`='$thumbnail', `alias`=" . (isset($alias) && $alias!=""?"'$alias'":"NULL") . " WHERE id=$id;";
		$result = runQuery($query);
		
		if($result){
			$query = "DELETE FROM articletags WHERE article=$id";
			runQuery($query);
			if($length>0){
				$query = "INSERT INTO articletags(`article`, `tag`) VALUES ";
				for($i=0;$i<$length;$i++){
					if($i<$length-1){
						$query .= "('$id', (SELECT id FROM tag WHERE name='$tags[$i]')), ";
					} else $query .= "('$id', (SELECT id FROM tag WHERE name='$tags[$i]'))";
				}
				$result = runQuery($query);
				if($result){
					header("Location: ../admin/admin.php?result=editsuccess");
				} else {
					header("Location: ../admin/admin.php?result=editfailure");
				}
			} else {
				header("Location: ../admin/admin.php?result=editsuccess");
			}
		} else {
			header("Location: ../admin/admin.php?result=editfailure");
		}
	}
?>