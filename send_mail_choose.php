<?php
include('inc/functions.php');
include('inc/mail.php');
forceLogin(1);
top();

$req = db()->query('SELECT prenom, nom, email FROM users WHERE participe = 1');

$msg = '<p>Salut!
La phase d\'inscription pour le Secret Santa de la Ludo est terminée !
Maintenant, tu peux te connecter sur <a href="https://ludonoel.sebartyr.fr/" title="LudoNoël">le site du LudoNoël</a> pour enregistrer à qui tu préfères faire un cadeau !
Tu as jusqu\'au <strong>vendredi 29 novembre à 23h</strong> pour le faire, à défaut l\'attribution d\'à qui tu offres un cadeau sera aléatoire.

NB: Ce message n\'a été envoyé qu\'à ceux qui sont enregistrés comme participant sur le site, donc si vous avez parmi vos amis des gens qui souhaitent participer mais ont oublié de s\'inscrire, prévenez les !

--
Thomas Bessou, ingénieur IT pour la Licorne de Noël

<img src="cid:img" alt="" />
</p>';

echo '<p>';
while($data = $req->fetch()) {
	$nom = $data['nom']; $prenom = $data['prenom']; $email = $data['email'];
	if(mail_html($prenom, $nom, $email, 'LudoNoël - Phase 2', nl2br($msg), realpath('licorne-sapin.jpg'))) {
		echo "Mail envoyé à $prenom $nom ($email) ! :)<br/>";
	}
	else
		echo "<strong>ERREUR d'envoi du mail</strong> à $prenom $nom ($email) ! :(<br/>";
}
echo '</p>';

bottom();
?>
