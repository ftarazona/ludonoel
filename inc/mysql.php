<?php
try
{
	if(LOCAL)
		$db = new PDO('mysql:host=localhost;dbname=ludonoel;charset=utf8', 'root', '');
	else
		$db = new PDO('mysql://mysql:b96d5f4925aebdab@dokku-mysql-ludonoel-db:3306/ludonoel_db');
	if(DEBUG)
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
	die('Erreur : ' . $e->getMessage());
}

function db() { return $GLOBALS['db']; }
?>
