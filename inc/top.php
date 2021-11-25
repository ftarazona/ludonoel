<!DOCTYPE html>
<html>
	<head>
	<meta charset="UTF-8">
	<title>LudoNoël<?php if(isset($title) and !empty($title)) { echo ' > '.$title; } ?></title>
	<style type="text/css">
	body {
		background-color: white;
		background-image: url('licorne-sapin.jpg');
		background-repeat: no-repeat;
		background-attachment: fixed;
		background-position: calc(100% - 70px) center;
	}
	</style>
	</head>
	<body>
		<header>
			<h1>LudoNoël</h1>
			<nav>
				<a href="index.php" />Accueil</a>
				<?php if(loggedIn()) { ?>
				<a href="logout.php" />Logout</a>
				<?php } else { ?>
				<a href="login.php" />Login</a>
				<a href="register.php" />S'enregistrer</a>
				<?php } ?>
			</nav>
		</header>
		<section>
