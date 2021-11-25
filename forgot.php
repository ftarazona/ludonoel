<?php
include('inc/functions.php');

if(isset($_SESSION['id'])) {
	redirect('index.php');
}

top('Mot de passe oublié');
echo '<h2>Changement de mot de passe</h2>';

include('inc/register.php');

$phase = 1;

$errs = [];
$pseudo = '';
if (isset($_POST['username'])) {
	$pseudo = $_POST['username'];
	
	if (!preg_match('/^[a-zA-Z0-9-]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if(empty($errs)) {
		$req = db()->prepare('SELECT id, change_pass, nom, prenom, email FROM users WHERE pseudo = ?');
		$req->execute(array($pseudo));
		if($data = $req->fetch()) {
			if($data['change_pass'] == '') {
				$email = $data['email']; $nom = $data['nom']; $prenom = $data['prenom'];
				$code = generateRandomString(20);
				$actUrl = 'http://'.$_SERVER['HTTP_HOST'].'/forgot.php?u='.$pseudo.'&c='.$code;
				$delUrl = 'http://'.$_SERVER['HTTP_HOST'].'/forgot.php?cancel&u='.$pseudo.'&c='.$code;
				$msg = '<p>Salut, tu peux changer ton mot de passe via le lien suivant : <a href="'.$actUrl.'">'.$actUrl.'</a><br/>';
				$msg .= 'Si ce n\'est pas toi qui as lancé cette procédure, annule la via ce lien : <a href="'.$delUrl.'">'.$delUrl.'</a></p>';
				$headers = 'From: Ludo Noël <noreply@ludo.tech>'."\r\n";
				$headers .='Content-Type: text/html; charset=utf8'."\r\n";
				
				if(LOCAL || mail("$prenom ".strtoupper($nom)." <$email>", 'LudoNoël - Changement de mot de passe', nl2br($msg), $headers)) {
					$req = db()->prepare('UPDATE users SET change_pass = ? WHERE id = ?');
					if($req->execute(array($code, $data['id']))) {
						$phase = 2;
					}
					else {
						$errs[] = "Impossible de sauvegarder la préparation d'activation";
					}
				}
				else {
					$errs[] = "Impossible d'envoyer le mail";
				}
			}
			else {
				$errs[] = "Une procédure de changement de mot de passe est déjà en cours, vérifiez vos mails.";
			}
		}
		else {
			$errs[] = 'Utilisateur inconnu';
		}
	}
}
else if (isset($_GET['u']) && isset($_GET['c'])) {
	$pseudo = $_GET['u'];
	$code = $_GET['c'];
	
	if (!preg_match('/^[a-zA-Z0-9-]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if(empty($errs)) {
		$req = db()->prepare('SELECT id, change_pass FROM users WHERE pseudo = ?');
		$req->execute(array($pseudo));
		if($data = $req->fetch()) {
			if(!empty($data['change_pass']) && $data['change_pass'] === $code) {
				if(isset($_GET['cancel'])) {
					db()->prepare('UPDATE users SET change_pass = "" WHERE id = ?')->execute(array($data['id']));
				}
				else {
					$phase = 3;
				}
			}
			else {
				$errs[] = "Code de changement de mot de passe invalide.";
			}
		}
		else {
			$errs[] = "Cet utilisateur n'existe pas.";
		}
	}
}
else if (isset($_POST['user']) && isset($_POST['token']) && isset($_POST['pass']) && isset($_POST['confirm'])) {
	$pseudo = $_POST['user'];
	$code = $_POST['token'];
	$pass = $_POST['pass'];
	$confirm = $_POST['confirm'];
	
	if (!preg_match('/^[a-zA-Z0-9-]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if (strlen($pass) < 8) $errs[] = 'Votre mot de passe doit faire au moins 8 caractères';
	if ($pass !== $confirm) $errs[] = 'Les mots de passe ne correspondent pas';
	
	if(empty($errs)) {
		$req = db()->prepare('SELECT id, change_pass FROM users WHERE pseudo = ?');
		$req->execute(array($pseudo));
		if($data = $req->fetch()) {
			if(!empty($data['change_pass']) && $data['change_pass'] === $code) {
				db()->prepare('UPDATE users SET change_pass = "", password = ? WHERE id = ?')->execute(array(genHash($pass), $data['id']));
				$phase = 4;
			}
			else {
				$errs[] = "Code de changement de mot de passe invalide.";
			}
		}
		else {
			$errs[] = "Cet utilisateur n'existe pas.";
		}
	}
	else {
		$phase = 3;
	}
}

if(!empty($errs)) {
	echo '<p class="err" >Erreurs : <ul>';
	foreach ($errs as $err) {
		echo '<li>'.$err.'</li>';
	}
	echo '</ul></p>';
}
if($phase == 1) {
	?>
	<form action="forgot.php" method="post">
		<label>Pseudo : <input type="text" name="username" value="<?php echo htmlspecialchars($pseudo); ?>" /></label><br/>
		<input type="submit" value="Envoyer le mail" />
	</form>
	<?php
}
else if($phase == 2) {
	echo "<p class=\"confirm\">Mail envoyé, suis les instructions à l'intérieur pour changer ton mot de passe.</p>";
}
else if($phase == 3) {
	?>
	<form action="forgot.php" method="post">
		<input type="hidden" name="user" value="<?php echo htmlspecialchars($pseudo); ?>" />
		<input type="hidden" name="token" value="<?php echo htmlspecialchars($code); ?>" />
		<label>Nouveau mot de passe : <input type="password" name="pass" /></label><br/>
		<label>Confirmation : <input type="password" name="confirm" /></label><br/>
		<input type="submit" value="Changer le mot de passe" />
	</form>
	<?php
}
else if($phase == 4) {
	echo "<p class=\"confirm\">Mot de passe changé, tu peux désormais te connecter avec ton nouveau mot de passe.</p>";
}
else echo '<h1>WTF?!</h1>';
bottom();
?>