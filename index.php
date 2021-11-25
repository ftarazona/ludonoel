<?php
include('inc/functions.php');
forceLogin();
top();

$req = db()->prepare('SELECT participe FROM users WHERE id = ?');
$req->execute(array(id()));
$data = $req->fetch();
$participe = $data['participe'];

if(isset($_POST['change_participation'])) {
	$participe = !$participe;
	db()->prepare('UPDATE users SET participe = ? WHERE id = ?')->execute(array($participe, id()));
}

$participation = 'Actuellement, <i>je '.($participe ? 'participe' : 'ne participe pas').'</i>';
$participation .= ' <form action="index.php" method="post" style="display: inline;"><input type="submit" name="change_participation" value="Changer" /></form>';

echo "<p style=\"display: inline;\" >Votre compte est créé. Les enregistrements des gens à qui vous voulez bien faire des cadeaux, c'est bientôt ! On vous enverra un mail. Tâchez de vous souvenir de vos codes d'ici là :P<br/>
Au moment où la sélection sera ouverte, vous ne pourrez sélectionner que les gens inscrits, alors assurez vous que tous vos amis invités s'inscrivent rapidement !<br/>
S'il est impossible de satisfaire vos affinités, l'attribution sera aléatoire.<br/><br/>
Si vous ne souhaitez pas participer au Secret Santa, c'est possible, il suffit de l'indiquer ici : $participation<br/><br/>
A titre indicatif (pour éviter que les cadeaux n'aient des valeurs complètement disparates), comme chacun offre et reçoit un seul cadeau, ils sont généralement d'une valeur de l'ordre de 5-15€ (libre à vous bien sûr de respecter ou non cet intervalle).<br/><br/>
Thomas Bessou, ingénieur IT pour la Licorne de Noël <3</p>";



$req = db()->query('SELECT nom, prenom, participe FROM users');
$participe = [];
$participe_pas = [];
while($data = $req->fetch()) {
	if($data['participe']) $participe[] = $data;
	else $participe_pas[] = $data;
}

echo "<h3>Liste des participants au Secret Santa :</h3><p>";
if(empty($participe)) {
	echo '<i>Personne</i>';
}
else {
	foreach($participe as $data) {
		echo $data['prenom'].' '.$data['nom'].'<br/>';
	}
}
echo '</p>';

echo "<h3>Ne participeront pas au Secret Santa (mais seront présents) :</h3><p>";
if(empty($participe_pas)) {
	echo '<i>Personne</i>';
}
else {
	foreach($participe_pas as $data) {
		echo $data['prenom'].' '.$data['nom'].'<br/>';
	}
}
echo '</p>';

bottom();
?>
