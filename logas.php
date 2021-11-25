<?php
include('inc/functions.php');
forceLogin(1);
top();

if(is_numeric($_GET['id'])) {
	$_SESSION['id'] = intval($_GET['id']);
}
redirect('index.php');

bottom();
?>