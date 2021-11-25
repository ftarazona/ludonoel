<?php
include('inc/functions.php');
include('inc/pbkdf2.php');

if(isset($_SESSION['id'])) {
	redirect('index.php');
}

function checkPass($hash, $pwd) {
	$spl = explode(':', $hash, 2);
	return (pbkdf2('sha256', $pwd, $spl[0]) === $spl[1]);
}

function isLoginOk($pseudo, $pass) {
	$req = db()->prepare('SELECT id, password, activation_code FROM users WHERE pseudo = ?');
	$req->execute(array($pseudo));
	if($data = $req->fetch()) {
		if(checkPass($data['password'], $pass)) {
			if(empty($data['activation_code'])) {
				return $data['id'];
			}
			else {
				return "Ce compte n'est pas activé. Vérifiez vos mails (y compris la boite à spam)";
			}
		}
		else {
			return "Mot de passe invalide. Si vous ne vous souvenez plus de vos identifiants, <a href='forgot.php'>cliquez ici</a>.";
		}
	}
	else {
		return "Cet utilisateur n'existe pas. Si vous ne vous souvenez plus de vos identifiants contactez moi à thomas.bessou@gmail.com";
	}
	return "unknown error in isLoginOk";
}

$errs = [];
$pseudo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	function f($s) { return trim($s, ' '); };
	$pseudo = f($_POST['username']);
	$pass = $_POST['pass'];
	//$recaptcha = $_POST['g-recaptcha-response'];
	
	if (!preg_match('/^[a-zA-Z0-9-_]+$/', $pseudo)) $errs[] = 'Pseudo invalide (ne peut contenir que chiffres, lettres et tirets)';
	if(empty($errs)) {
		/*include('inc/captcha.php');
		if(!isCaptchaValid($recaptcha)) {
			$errs[] = "Tu dois valider le captcha";
		}
		else {*/
			$id = isLoginOk($pseudo, $pass);
			if(is_numeric($id)) {
				$_SESSION['id'] = $id;
				redirect('index.php');
			}
			else {
				$errs[] = $id;
			}
		//}
	}
}

top('Login');
if(!empty($errs)) {
	echo '<p class="err" >Erreurs : <ul>';
	foreach ($errs as $err) {
		echo '<li>'.$err.'</li>';
	}
	echo '</ul></p>';
}
?>
<form action="login.php" method="post">
	<label>Pseudo : <input type="text" name="username" value="<?php echo $pseudo; ?>" /></label><br/>
	<label>Mot de passe : <input type="password" name="pass" /></label><br/>
	<?php /*<script src='https://www.google.com/recaptcha/api.js'></script><div class="g-recaptcha" data-sitekey="6Leh3xATAAAAAHB3CsuBFJCW7Xvw5SuXpXC5kj_8"></div> */ ?>
	<input type="submit" value="Connexion" /><br/>
</form>
<br/><a href="forgot.php" name="Mot de passe oublié"/>Mot de passe oublié</a>
<?php
bottom();
?>
