<?php
require_once '../src/config/config.php';
session_start();
$_SESSION = array();
session_destroy();
header("location: login.php");
exit;
?>