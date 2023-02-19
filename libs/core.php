<?php
include "conn.php";

function showArticles($searchQuery){
	global $conn;
	//Controlli injection
	$searchQuery = mysqli_real_escape_string($conn, $_GET["q"]);
	$query = "SELECT `id`, `title`, `text`, `thumbnail`, `writeDate`, `writer` FROM article WHERE (title LIKE '%$searchQuery%' OR text LIKE '%$searchQuery%') AND (deleted=0 AND hidden=0) ORDER BY writeDate DESC";
	$result = runQuery($query);
	$length = mysqli_num_rows($result);
	if($length==0){
		echo "Non sono stati trovati articoli con questi criteri.";
	} else {
		$i=0;
		while(($row = mysqli_fetch_array($result))!=null){
			$friendlyUrl = convertToUrlFriendly($row["title"]);
			echo "<div class='row no-gutters article " . ((++$i<$length)?"mb-3":"") . "'><div class='col-4' style='max-width: 210px;max-height:100%'><a href='https://www.glitchaio.it/articoli/$row[id]/$friendlyUrl'><img class='w-100 h-100' style='object-fit: cover;' src='$row[thumbnail]'></a></div><div class='col d-flex flex-column'><div class='row mx-0 title'><a class='text-decoration-none text-reset' href='https://www.glitchaio.it/articoli/$row[id]/$friendlyUrl' style='margin-left:5px;'><strong style='word-break:break-word;'>$row[title]</strong></a></div><div class='row mx-0 d-none d-sm-block'><div style='margin-left:5px;' class='description'><a>" . nullifyBbcode(shortenText($row["text"])) . "</a></div></div><div class='row mx-0 h-100' style='align-items: flex-end;font-size: .8rem;'><div class='col px-0' style='margin-left: 5px;'>Di $row[writer]</div><div class='col px-0' style='margin-right: 5px;text-align: right;'>Scritto il " . date_format(new DateTime($row["writeDate"]), "d-m-Y") . "</div></div></div></div>";
		}
	}
}

function shortenText($text){
	$text = substr($text, 0, 120); //Riduciamo la string fino a 120 caratteri.
	$text = substr($text, 0, strrpos($text, " ", 0)); //Riduciamo ulteriormente la string fino all'ultimo spazio.
	$text .= "..."; //Aggiungiamo puntini
	return $text;
}

function checkLogin($privileges){ //Rendere compatibile a più privilegi
	$check = false;
	for($i=0;$i<count($privileges);$i++){
		if(isset($_SESSION['privilege']) && $privileges[$i]==$_SESSION['privilege'])
			$check = true;
	}
	if(isset($_SESSION['privilege']) && !$check){
		header("Location: /admin/".$_SESSION['privilege'].".php"); //Commentato per debug.
	} else if(!isset($_SESSION['isLogged']) | $_SESSION['isLogged']!=1 | !isset($_SESSION['nickname']) | !$check){
		header("Location: ../admin/index.php");
	}
}

function checkAlreadyLogged(){
	if(isset($_SESSION['isLogged']) && $_SESSION['isLogged']==1 && isset($_SESSION['nickname']) && isset($_SESSION['privilege'])){
		if($_SESSION['privilege']==="admin")
			header("Location: ../admin/admin.php");
		else if($_SESSION['privilege']==="writer")
			header("Location: ../admin/writer.php");
	}
}

function showAllTagsAdd(){
	$query = "SELECT name FROM tag";
	$result = runQuery($query);
	while(($row = mysqli_fetch_array($result))!=null){
		echo "<option value='$row[0]'>$row[0]</option>";
	}
}

function showAllTags(){
	$query = "SELECT name FROM tag";
	$result = runQuery($query);
	while(($row = mysqli_fetch_array($result))!=null){
		echo "<button class='btn tag' onclick='removeTag(this)'>$row[0]</button>";
	}
}

function convertToUrlFriendly($title){
	$title = strtolower($title);
    $title = preg_replace('/[^a-zA-Z0-9]/i','-',$title);
    $title = preg_replace("/(-){2,}/",'$1',$title);
    return $title;
}

function bbcodeToText($text){
	$search = array("[b]", "[/b]", "[i]", "[/i]", "[u]", "[/u]", "[s]", "[/s]");
	$replace = array("<strong>", "</strong>", "<em>", "</em>", "<ins>", "</ins>", "<del>", "</del>");
	$text = str_replace($search, $replace, $text);
	$text = preg_replace('@\[url=([^]]*)\]([^[]*)\[/url\]@', '<a href="$1" target="_blank">$2</a>', $text);
	$text = preg_replace('@\[url\]([^[]*)\[/url\]@', '<a href="$1" target="_blank">$1</a>', $text);
	return $text;
}

function nullifyBbcode($text){
	$search = array("[b]", "[/b]", "[i]", "[/i]", "[u]", "[/u]", "[s]", "[/s]");
	$replace = "";
	$text = str_replace($search, $replace, $text);
	$text = preg_replace('@\[url=([^]]*)\]([^[]*)\[/url\]@', '', $text);
	$text = preg_replace('@\[url\]([^[]*)\[/url\]@', '', $text);
	return $text;
}

function generateKeywords($id){
	$keywords = "";
	$query = "SELECT name FROM tag WHERE id IN (SELECT tag FROM articletags WHERE article='$id')";
	$result = runQuery($query);
	$i = 0;
	$numRows = mysqli_num_rows($result);
	while(($row = mysqli_fetch_array($result))!=null){
		if(++$i < $numRows)
			$keywords .= $row[0] . ",";
		else 
			$keywords .= $row[0];
	}
	return $keywords;
}

function showRecentArticles(){
	$query = "SELECT `id`, `thumbnail`, `title` FROM article WHERE deleted=0 AND hidden=0 ORDER BY writeDate DESC LIMIT 10";
	$result = runQuery($query);
	$i = 0;
	while(($row = mysqli_fetch_array($result))!=null){
		$url = "articoli/$row[id]/" . convertToUrlFriendly($row['title']);
		if($i==0)
			echo "<div class='carousel-item active'><a href='$url' class='responsive-parent'><img class='d-block w-100 responsive-child' src='$row[thumbnail]'></a><div class='px-2 py-1 secondary carousel-title'><a href='$url' class='text-decoration-none text-reset carousel-header'><strong>$row[title]</strong></a></div></div>";
		else echo "<div class='carousel-item'><a href='$url' class='responsive-parent'><img class='d-block w-100 responsive-child' src='$row[thumbnail]'></a><div class='px-2 py-1 secondary carousel-title'><a href='$url' class='text-decoration-none text-reset carousel-header'><strong>$row[title]</strong></a></div></div>";
		$i++;
	}
}

function showMostPopularArticle(){
	$query = "SELECT `id`, `thumbnail`, `title` FROM article WHERE deleted=0 AND hidden=0 ORDER BY views DESC, writeDate DESC LIMIT 1";
	$result = runQuery($query);
	if(($row = mysqli_fetch_array($result))!=null){
		$url = "articoli/$row[id]/" . convertToUrlFriendly($row['title']);
		echo "<a href='$url' class='responsive-parent'><img class='d-block w-100 responsive-child' src='$row[thumbnail]'></a><div class='px-2 py-1 secondary carousel-title'><a href='$url' class='text-decoration-none text-reset'><strong>$row[title]</strong></a></div>";
	}
}

function showMostVotedArticle(){
	$query = "SELECT `id`, `thumbnail`, `title` FROM article WHERE deleted=0 AND hidden=0 ORDER BY likes DESC, writeDate DESC LIMIT 1";
	$result = runQuery($query);
	if(($row = mysqli_fetch_array($result))!=null){
		$url = "articoli/$row[id]/" . convertToUrlFriendly($row['title']);
		echo "<a href='$url' class='responsive-parent'><img class='d-block w-100 responsive-child' src='$row[thumbnail]'></a><div class='px-2 py-1 secondary carousel-title'><a href='$url' class='text-decoration-none text-reset'><strong>$row[title]</strong></a></div>";
	}
}

function showRandomDailyArticle(){
	$query = "SELECT `id`, `thumbnail`, `title` FROM article WHERE deleted=0 AND hidden=0 ORDER BY RAND(CURDATE()) LIMIT 1";
	$result = runQuery($query);
	if(($row = mysqli_fetch_array($result))!=null){
		$url = "articoli/$row[id]/" . convertToUrlFriendly($row['title']);
		echo "<a href='$url' class='responsive-parent'><img class='d-block w-100 responsive-child' src='$row[thumbnail]'></a><div class='px-2 py-1 secondary carousel-title'><a href='$url' class='text-decoration-none text-reset'><strong>$row[title]</strong></a></div>";
	}
}

function showAdminAccounts(){
	$query = "SELECT `email`, `nickname`, `privilege` FROM user";
	$result = runQuery($query);
	while(($row = mysqli_fetch_array($result))!=null){
		if($row['nickname']!="admin"){
			echo "<tr><td class='p-0' style='vertical-align: middle;'><div class='icon d-inline-block align-middle' onclick=\"deleteAccount('$row[nickname]')\">&times</div></td><td class='align-middle'>$row[email]</td><td class='align-middle'>$row[nickname]</td><td class='align-middle'>$row[privilege]</td><td class='align-middle'><button class='btn btn-primary' onclick=\"requestNewPassword('$row[nickname]', '$row[email]')\">Rigenera</td></tr>";
		}
	}
}

function showReviewArticles(){
	$query = "SELECT `id`, `title`, `writer`
		FROM article
		WHERE `onReview`=1 AND `deleted`=0
		ORDER BY `id` DESC";
	$result = runQuery($query);
	while(($row = mysqli_fetch_array($result))!=null){
		echo "<tr><td class='align-middle'><a href='reviewarticle.php?article=$row[id]'>$row[title]</a></td><td class='align-middle'>$row[writer]</td><td class='align-middle' style='text-align: center;'><button class='btn btn-success mb-1 mb-lg-0 mr-0 mr-lg-2' onclick=\"acceptArticle('$row[id]')\">&#10004;</button><button class='btn btn-danger' onclick=\"refuseArticle('$row[id]')\">&#10006;</button></td></tr>";
	}
}

function showAdminArticles($search = ""){
	if($search==""){
		$query = "SET @limit = 10;";
		runQuery($query);
		$query = "SELECT `id`, `title`, `hidden` FROM (
			SELECT `id`, `title`, `hidden`, `deleted`, IF(deleted=0, @rownum := @rownum + 1, NULL) AS `rank`
			FROM article, (SELECT @rownum := 0) r
			ORDER BY id DESC) d
			WHERE `rank` < @limit;";
	} else
		$query = "SELECT `id`, `title`, `text`, `hidden`, `deleted` FROM article WHERE title LIKE '%$search%' OR text LIKE '%$search%' OR id LIKE '%$search%' ORDER BY id DESC";
	$result = runQuery($query);
	while(($row = mysqli_fetch_array($result))!=null){
		echo "<tr><td class='p-0' style='vertical-align: middle;'><div class='icon d-inline-block align-middle' onclick=\"deleteArticle('$row[id]')\">&times</div></td><td class='align-middle'><a href='editarticle.php?article=$row[id]'>$row[title]</a></td><td class='align-middle' style='text-align: center;'><input type='checkbox' onchange=\"hideArticle('$row[id]')\"" . ($row["hidden"]==1?" checked":"") . "></td></tr>";
	}
}

function random_str(
    int $length = 8,
    string $keyspace = '!"#$%&\'()*+?@0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}
?>