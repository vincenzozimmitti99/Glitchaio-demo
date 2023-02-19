<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;
	
	include "core.php";

	if(isset($_GET['request'])){
		if($_GET['request']=="addTag"){
			addTag($_GET['name']);
		} else if($_GET['request']=="removeTag"){
			removeTag($_GET['name']);
		} else if($_GET['request']=="changePassword"){
			changePassword($_GET['nickname'], $_GET['password']);
		} else if($_GET['request']=="createNewAccount"){
			createNewAccount($_GET['nickname'], $_GET['email'], $_GET['privilege']);
		} else if($_GET['request']=="showAdminAccounts"){
			showAdminAccounts();
		} else if($_GET['request']=="requestNewPassword"){
			requestNewPassword($_GET['nickname'], $_GET['email']);
		} else if($_GET['request']=="deleteAccount"){
			deleteAccount($_GET['nickname']);
		} else if($_GET['request']=="showAdminArticles"){
			showAdminArticles();
		} else if($_GET['request']=="deleteArticle"){
			deleteArticle($_GET['id']);
		} else if($_GET['request']=="hideArticle"){
			hideArticle($_GET['id']);
		} else if($_GET['request']=="getArticle"){
			getArticle($_GET['id']);
		}
	}

	if(isset($_POST['request'])){
		if($_POST['request']=="uploadImage"){
			uploadImage();
		}
	}
	
	function addTag($name){
		global $conn;
		$name = mysqli_real_escape_string($conn, $name);
		$query = "SELECT `name` FROM tag WHERE name='$name'";
		$result = runQuery($query);
		if(($row = mysqli_fetch_array($result))!=null){ //Trova risultati
			//Tag già esistente
			echo "-1";
		} else { //Non trova risultati
			//Si può inserire la tag
			$query = "INSERT INTO tag(`name`) VALUES ('$name')";
			runQuery($query);
			if(mysqli_affected_rows($conn)==1){
				//Viene inserita la tag
				echo "1";
			} else {
				//Errore generico
				echo "0";
			}
		}
	}
	
	function removeTag($name){
		global $conn;
		$name = mysqli_real_escape_string($conn, $name);
		$query = "DELETE FROM tag WHERE name='$name'";
		runQuery($query);
		if(mysqli_affected_rows($conn)>0) //Se c'è una tag da rimuovere
			echo "1";
		else //Errore che non dovrebbe mai arrivare
			echo "0";
	}
	
	function changePassword($nickname, $password){
		global $conn;
		$nickname = strip_tags(mysqli_real_escape_string($conn, $nickname));
		$password = strip_tags(mysqli_real_escape_string($conn, $password));
		$password = password_hash($password, PASSWORD_DEFAULT);
		$query = "UPDATE user SET password='$password' WHERE nickname='$nickname'";
		$result = runQuery($query);
		if($result) echo "1"; else echo "0";
	}
	
	function sendInfoEmail($nickname, $email, $password){
		include "vendor/autoload.php";
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host = "smtps.aruba.it";
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->Port = 465;
		$mail->SMTPSecure = 'ssl';
		$mail->Username = "noreply@glitchaio.it";
		$mail->Password = "6Z5WPa}4";
		$mail->From = "noreply@glitchaio.it";
		$mail->FromName = "Glitchaio";
		$mail->AddAddress($email);
		$mail->Subject = "Registrazione account Glitchaio";
		$mail->Body = "In questa email troverai i dati necessari all'accesso nella <a href='https://www.glitchaio.it/admin'>parte admin</a> di Glitchaio.<br><br>Il tuo nickname: $nickname<br>La tua password: $password<br><br>È consigliato di cambiare la password il prima possibile.";
		$mail->IsHTML(true);
		if($mail->send()){
			return true;
		} else {
			return false;
		}
	}
	
	function sendNewPassword($nickname, $email, $password){
		include "vendor/autoload.php";
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host = "smtps.aruba.it";
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->Port = 465;
		$mail->SMTPSecure = 'ssl';
		$mail->Username = "noreply@glitchaio.it";
		$mail->Password = "6Z5WPa}4";
		$mail->From = "noreply@glitchaio.it";
		$mail->FromName = "Glitchaio";
		$mail->AddAddress($email);
		$mail->Subject = "Nuova password account Glitchaio";
		$mail->Body = "È stata fatta una richiesta per dei nuovi dati per l'accesso nella <a href='https://www.glitchaio.it/admin'>parte admin</a> di Glitchaio.<br><br>Il tuo nickname: $nickname<br>La tua nuova password: $password";
		$mail->IsHTML(true);
		if($mail->send()){
			return true;
		} else {
			return false;
		}
	}
	
	function createNewAccount($nickname, $email, $privilege){
		global $conn;
		$nickname = strip_tags(mysqli_real_escape_string($conn, $nickname));
		$email = strip_tags(mysqli_real_escape_string($conn, $email));
		$privilege = strip_tags(mysqli_real_escape_string($conn, $privilege));
		$query = "SELECT count(1) FROM user WHERE nickname='$nickname'";
		if(mysqli_fetch_array(runQuery($query))[0]){
			echo "nickname";
			return;
		}
		$query = "SELECT count(1) FROM user WHERE email='$email'";
		if(mysqli_fetch_array(runQuery($query))[0]){
			echo "email";
			return;
		}
		$password = random_str();
		if(sendInfoEmail($nickname, $email, $password)){
			$query = "INSERT INTO user(`email`, `nickname`, `password`, `privilege`) VALUES('$email', '$nickname', '" . password_hash($password, PASSWORD_DEFAULT) . "', '$privilege')";
			runQuery($query);
			if(mysqli_affected_rows($conn)===1){
				echo "1"; //Success
			} else {
				echo "0"; //Error
			}
		} else {
			echo "-1"; //Mailing error
		}
	}
	
	function requestNewPassword($nickname, $email){
		global $conn;
		$nickname = strip_tags(mysqli_real_escape_string($conn, $nickname));
		$email = strip_tags(mysqli_real_escape_string($conn, $email));
		$password = random_str();
		if(sendNewPassword($nickname, $email, $password)){
			$query = "UPDATE user SET password='" . password_hash($password, PASSWORD_DEFAULT) . "' WHERE nickname='$nickname'";
			runQuery($query);
			if(mysqli_affected_rows($conn)===1){
				echo "1"; //Success
			} else {
				echo "0"; //Error
			}
		} else {
			echo "-1"; //Mailing error
		}
	}
	
	function deleteAccount($nickname){
		global $conn;
		$nickname = strip_tags(mysqli_real_escape_string($conn, $nickname));
		$query = "DELETE FROM user WHERE nickname='$nickname'";
		runQuery($query);
		if(mysqli_affected_rows($conn)===1)
			echo "1"; //Success
		else echo "0"; //Error
	}

	function deleteArticle($id){
		global $conn;
		$id = strip_tags(mysqli_real_escape_string($conn, $id));
		$query = "UPDATE article SET deleted = 1 WHERE id='$id'";
		runQuery($query);
		if(mysqli_affected_rows($conn)===1)
			echo "1"; //Success
		else echo "0"; //Error
	}

	function hideArticle($id){
		global $conn;
		$id = strip_tags(mysqli_real_escape_string($conn, $id));
		$query = "UPDATE article SET hidden = !hidden WHERE id='$id'";
		runQuery($query);
		if(mysqli_affected_rows($conn)===1)
			echo "1"; //Success
		else echo "0"; //Error
	}

	function getArticle($id){
		if(!isset($id)){
			header("HTTP/1.0 400 Not Found");
			echo "-1";
			exit;
		}
	
		$query = "SELECT `id`, `title`, `text`, `thumbnail`, `alias` FROM article WHERE id = '$id'";
		$result = runQuery($query);
		if(($row = mysqli_fetch_array($result))!=null){
			$article = array(
				"title" => $row["title"],
				"text" => $row["text"],
				"thumbnail" => $row["thumbnail"],
				"alias" => (isset($row["alias"])?$row["alias"]:null)
			);
		} else {
			header("HTTP/1.0 404 Not Found");
			echo "-1";
			exit;
		}
		$query = "SELECT tag.id, name FROM tag JOIN articletags ON tag.id = articletags.tag WHERE articletags.article=$id;";
		$result = runQuery($query);
		$tags = array();
		while(($row = mysqli_fetch_array($result))!=null){
			array_push($tags, $row["name"]);
		}
		$article["tags"] = $tags;
		echo json_encode($article);
	}

	function uploadImage(){
		$file = $_FILES["image"];
		if($file!=null){
			$name = $file["name"];
			$newName = $name;
			//$directory = dirname(__DIR__) . "/imgs/articles/"; //Vecchio
			$directory = "../imgs/articles/";
			$path = $directory . $name;
			$i = 1;
			while(file_exists($path)){
				$newName = pathinfo($name, PATHINFO_FILENAME) . "-" . $i++ . "." . pathinfo($name, PATHINFO_EXTENSION);
				$path = $directory . $newName;
			}
			$name = $newName;
			if(move_uploaded_file($file["tmp_name"], $path)){
				echo("http://localhost/glitchaio-dev/imgs/articles/" . $name); //Locale
				//echo("https://www.glitchaio.it/imgs/articles/" . $name); //Online
			} else {
				echo("0"); //Errore
			}
		} else {
			echo("-1"); //File non passato
		}
	}
?>