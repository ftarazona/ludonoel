<?php
include('inc/functions.php');
if(loggedIn()) unset($_SESSION['id']);
redirect('index.php');
?>