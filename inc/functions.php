<?php
include('config.php');

session_start();
header('Content-Type: text/html; charset=utf-8');

include('mysql.php');

function loggedIn($as = true) {
	return isset($_SESSION['id']) && $_SESSION['id'] == $as;
}

function id() {
	if(isset($_SESSION['id'])) return $_SESSION['id'];
	else return null;
}

function redirect($url) {
	header('Location: '.$url);
	exit('Nice try dude.');
}

function forceLogin($as = true) {
	if(!loggedIn($as)) redirect('login.php');
}

function top($title=null) {
	include('top.php');
}
function bottom() {
	include('bottom.php');
}
?>