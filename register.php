<?php
include('inc/functions.php');

if(isset($_SESSION['id'])) {
	redirect('index.php');
}

$errs = []; $pseudo = $pass = $email = $nom = $prenom = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	function f($s) { return trim($s, ' '); };
	function fn($s) { return ucwords(strtolower(f($s)), ' -'); };
	
	$pseudo = f($_POST['username']);
	$pass = $_POST['pass'];
	$confirm = $_POST['confirm'];
	$email = strtolower(f($_POST['email']));
	$nom = fn($_POST['nom']);
	$prenom = fn($_POST['prenom']);
	$recaptcha = $_POST['g-recaptcha-response'];
	
	$regex_nom = "/^([A-Za-zàáâäçèéêëìíîïñòóôöùúûü]+(( |')[A-Za-zàáâäçèéêëìíîïñòóôöùúûü]+)*)+([-]([A-Za-zàáâäçèéêëìíîïñòóôöùúûü]+(( |')[A-Za-zàáâäçèéêëìíîïñòóôöùúûü]+)*)+)*$/";
	
	// Début vérifs
	
	if (!preg_match('/^[a-zA-Z0-9-_]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if (strlen($pass) < 8) $errs[] = 'Votre mot de passe doit faire au moins 8 caractères';
	if ($pass !== $confirm) $errs[] = 'Les mots de passe ne correspondent pas';
	if (!preg_match($regex_nom, $nom)) $errs[] = 'Nom invalide';
	if (!preg_match($regex_nom, $prenom)) $errs[] = 'Prénom invalide';
	if (!preg_match('/^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,10}$/', $email)) $errs[] = 'Format de l\'email invalide';
	
	if(empty($errs)) {
		include('inc/register.php'); // Fonctions nécessaires pour la suite
		if(!isCaptchaValid($recaptcha)) {
			$errs[] = "Tu dois valider le captcha";
		}
		else if(!isWhitelisted($nom, $prenom, $email)) {
			$errs[] = "Cet e-mail n'est pas whitelisté, si il s'agit d'une erreur contacte moi à thomas.bessou@telecom-paristech.fr (ou IRL Thomas BESSOU). Tu dois utiliser l'e-mail auquel tu as reçu l'invitation au LudoNoël.";
		}
		else {
			if(isPseudoUsed($pseudo)) $errs[] = "Ce pseudo est déjà pris";
			if(isEmailUsed($email)) $errs[] = "Cet email est déjà utilisé. Si ce n'est pas toi qui l'a enregistrée, contacte moi à thomas.bessou@telecom-paristech.fr (ou IRL Thomas BESSOU)";
			if(empty($errs)) {
				// On enregistre enfin
				$ret = register($pseudo, $pass, $email, $nom, $prenom);
				if($ret === true) {
					top('Enregistrement terminé');
					echo '<h2>Enregistrement terminé</h2>';
					echo '<p>Un mail d\'activation vous a été envoyé à <strong>'.$email.'</strong>, vérifiez votre boite mail, <strong>ainsi que votre boite à spams</strong>.</p>';
					bottom();
					exit();
				}
				else {
					$errs[] = 'Erreur serveur ('.$ret.'), contacte moi à thomas.bessou@telecom-paristech.fr (ou IRL Thomas BESSOU)';
				}
			}
		}
	}
}


top('S\'enregistrer');

if(!empty($errs)) {
	echo '<p class="err" >Erreurs : <ul>';
	foreach ($errs as $err) {
		echo '<li>'.$err.'</li>';
	}
	echo '</ul></p>';
}
?>
<form action="register.php" method="post">
	<label>Pseudo : <input type="text" name="username" value="<?php echo htmlspecialchars($pseudo); ?>" /></label><br/>
	<label>Mot de passe : <input type="password" name="pass" /></label><br/>
	<label>Mot de passe (confirmation) : <input type="password" name="confirm" /></label><br/>
	<label>Email : <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" /></label><br/>
	<label>Prénom : <input type="text" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" /></label><br/>
	<label>Nom : <input type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" /></label><br/>
	<script src='https://www.google.com/recaptcha/api.js'></script><div class="g-recaptcha" data-sitekey="6Leh3xATAAAAAHB3CsuBFJCW7Xvw5SuXpXC5kj_8"></div>
	<input type="submit" />
</form>
<?php
bottom();
?>
