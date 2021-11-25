<?php
include('inc/functions.php');
include('inc/mail.php');
forceLogin(1);
top();

$req = db()->query('SELECT prenom, nom, email FROM users WHERE participe = 1');

$msg = "<p>Salut!
L'attribution est terminée !
Destination <a href=\"https://ludonoel.sebartyr.fr/\" title=\"LudoNoël\">le site du LudoNoël</a> pour savoir à qui tu offres !
Si ce n'est pas déjà fait, tu peux également y laisser un message pour le cas où certains seraient en manque d'inspiration. ;)

A titre indicatif (pour éviter que les cadeaux n'aient des valeurs complètement disparates), comme chacun offre et reçoit un seul cadeau, ils sont généralement d'une valeur de l'ordre de 5-15€ (libre à vous bien sûr de respecter ou non cet intervalle).

Rendez-vous le <strong>14 décembre à 20h</strong> !

--
Thomas Bessou, ingénieur IT pour la Licorne de Noël


<img src=\"cid:img\" alt=\"\" />
</p>";

echo '<p>';
while($data = $req->fetch()) {
	$nom = $data['nom']; $prenom = $data['prenom']; $email = $data['email'];
	if(mail_html($prenom, $nom, $email, 'LudoNoël - Attribution terminée', nl2br($msg), realpath('licorne-sapin.jpg'))) {
		echo "Mail envoyé à $prenom $nom ($email) ! :)<br/>";
	}
	else
		echo "<strong>ERREUR d'envoi du mail</strong> à $prenom $nom ($email) ! :(<br/>";
}
echo '</p>';

bottom();
?>
