<?php
include('inc/functions.php');

$errs = [];
$pseudo = '';
if (isset($_GET['u']) && isset($_GET['a'])) {
	$pseudo = $_GET['u'];
	$code = $_GET['a'];
	
	if (!preg_match('/^[a-zA-Z0-9-_]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if(empty($errs)) {
		$req = db()->prepare('SELECT id, activation_code FROM users WHERE pseudo = ?');
		$req->execute(array($pseudo));
		if($data = $req->fetch()) {
			if(!empty($data['activation_code']) && $data['activation_code'] === $code) {
				if(isset($_GET['delete'])) {
					db()->prepare('DELETE FROM users WHERE id = ?')->execute(array($data['id']));
				}
				else {
					db()->prepare('UPDATE users SET activation_code = "" WHERE id = ?')->execute(array($data['id']));
				}
			}
			else {
				$errs[] = "Code d'activation invalide.";
			}
		}
		else {
			$errs[] = "Cet utilisateur n'existe pas.";
		}
	}
}
else {
	$errs[] = "Collez correctement l'adresse d'activation.";
}

top('Activation');
if(!empty($errs)) {
	echo '<p class="err" >Erreurs : <ul>';
	foreach ($errs as $err) {
		echo '<li>'.$err.'</li>';
	}
	echo '</ul></p>';
}
else {
	if(isset($_GET['delete'])) {
		echo '<p>Compte supprimé. Vous pouvez en recréer un.</p>';
	}
	else {
		echo '<p>Compte activé. Vous pouvez à présent vous connecter.</p>';
	}
}

bottom();
?>
