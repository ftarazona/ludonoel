<?php
include('captcha.php');
include('pbkdf2.php');
include('mail.php');
function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function genHash($pwd) {
	$salt = generateRandomString();
	return $salt.':'.pbkdf2('sha256', $pwd, $salt);
}
function isWhitelisted($nom, $prenom, $email) {
	if (!$email) { // Empty email not allowed
		return false;
	}
	/*$req = db()->prepare('SELECT COUNT(*) FROM whitelist WHERE (nom = ? OR nom = "") AND (prenom = ? OR prenom = '') AND email = ?');
	$req->execute(array($nom, $prenom, $email));*/
	$req = db()->prepare('SELECT COUNT(*) FROM whitelist WHERE email = ?');
	$req->execute(array($email));
	$rep = $req->fetch(PDO::FETCH_NUM);
	$cnt = $rep[0];
	return $cnt > 0;
}
function isPseudoUsed($pseudo) {
	$req = db()->prepare('SELECT COUNT(*) FROM users WHERE pseudo = ?');
	$req->execute(array($pseudo));
	$rep = $req->fetch(PDO::FETCH_NUM);
	$cnt = $rep[0];
	return $cnt > 0;
}
function isEmailUsed($email) {
	$req = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
	$req->execute(array($email));
	$rep = $req->fetch(PDO::FETCH_NUM);
	$cnt = $rep[0];
	return $cnt > 0;
}
function register($pseudo, $pass, $email, $nom, $prenom) {
	$activation_code = generateRandomString(20);
	$actUrl = 'http://'.$_SERVER['HTTP_HOST'].'/activate.php?u='.$pseudo.'&a='.$activation_code;
	$delUrl = 'http://'.$_SERVER['HTTP_HOST'].'/activate.php?delete&u='.$pseudo.'&a='.$activation_code;
	$msg = '<p>Salut, tu peux activer ton compte via le lien suivant : <a href="'.$actUrl.'">'.$actUrl.'</a><br/>';
	$msg .= 'Si ce n\'est pas toi qui l\'a créé, supprime le via ce lien : <a href="'.$delUrl.'">'.$delUrl.'</a></p>';

	if(mail_html($prenom, $nom, $email, 'Inscription LudoNoel', $msg)) {
		$giftsAlreadyAttributed = db()->query('SELECT COUNT(*) FROM users WHERE gift != 0')->fetchColumn();
		$req = db()->prepare('INSERT INTO users(pseudo, password, nom, prenom, email, activation_code, ip, participe) VALUES(?,?,?,?,?,?,?,?)');
		if($req->execute(array($pseudo, genHash($pass), $nom, $prenom, $email, $activation_code, $_SERVER['REMOTE_ADDR'], $giftsAlreadyAttributed ? 0 : 1))) {
			return true;
		}
		else {
			return "sauvegarde";
		}
	}
	else {
		return "mail";
	}
}
?>
