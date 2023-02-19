<?php
session_start();
unset($_SESSION["isLogged"]);
unset($_SESSION["nickname"]);
unset($_SESSION["privilege"]);
header("Location: ../admin/index.php");
?>