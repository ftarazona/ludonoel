<?php
include('inc/functions.php');
include('inc/mail.php');
forceLogin(1);
top();

$req = db()->query('SELECT prenom, nom, email FROM whitelist WHERE invitation_sent = 0');

$msg = "<p> Bonjour !

La LudoTech va fêter son 159ème anniversaire qui sera l'un des plus spéciaux, en effet ce LudoNoël sera le premier qui aura lieu dans le magnifique campus que tout le monde aime pour son accessibilité et sa fonctionnalité dès sa livraison: Saclay. <strong> Local 0A311, au 19 place Marguerite Perey, PALAISEAU 91120 </strong> pour les vieuxAs qui ne connaissent que Paris

Vous êtes invités ce <strong> samedi 14 décembre </strong> au LudoNoël ! Comme d'habitude, vous y trouverez des plats parmi les plus exquis que cette terre puisse offrir . Du salé au sucré, vos palais seront gâtés. La boisson coulera à flot, et les esprits seront libérés, le tout dans la bonne humeur.

Comme tous les ans, le LudoNoël permettra aux jeunes et anciens de se rencontrer lors d'une soirée magique, où d'innombrables générations pourront partager jeux, rires, histoires et légendes, ou les uns et les autres pourront faire connaissance ou renforcer des liens d'amitié qui dureront à jamais.

Inscris-toi maintenant à cet événement du turfu sur <a href='https://ludonoel.sebartyr.fr/' title='LudoNoël'>ce magnifique site</a> <strong> avant le 25 novembre à 23h </strong> ! Vous n'êtes pas encore convaincus par cet événement ? Sache que tu y trouveras aussi l'occasion de répondre à tes désirs matérialistes dans le traditionnel Secret Santa (participation optionnelle) qui est tellement disruptif que c'est vous qui choisissez à qui vous donnerez possiblement un cadeau. Si vous participez vous aurez <strong> 4 jours </strong> après le 25 novembre pour choisir l'ensemble des personnes à qui vous voulez potentiellement offrir un cadeau. Notre algo disruptif vous dira à laquelle de ces personnes offrir un cadeau. Montant conseillé pour les cadeaux : 5-15 €
Bien sûr vous pouvez toujours offrir un cadeau à notre belle Ludo et à sa communauté ! Offrir Kingdom Death peut être une bonne initiative par exemple (non).

Pour financer nourriture et boissons, nous vous demanderons une participation de 8€, que vous pouvez payer en liquide ou par Lydia à Thierry Bécart (06 64 77 59 76). Si vous voulez participer au repas en apportant ou préparant quelque chose, contactez nous à l'avance, et nous vous rembourserons les frais engagés.
Dans l'espoir de vous retrouver le <strong> samedi 14 décembre à partir de 20h </strong> pour relancer l'histoire de la Ludo dans ces nouvelles terres.
</p>";

$unicorn = "<pre>
_______\)%%%%%%%%._									  _.%%%%%%%%(/_______
`''''-'-;   % % % % %'-.           		     La              	     	     .-'% % % % %   ;-'-''''`
       :b) \            '-.       		  LudoTech                        .-'            / (d:
       : :__)'    .'    .'							   '.    '.    '(__: :
       :.::/  '.'   .'		    						       '.   '.'  \::.:	
       o_i/   :    ;  					 				 ;    :   \i_o		
              :   .'									 '.   :		
               ''`									   `''		
</pre>";
$msg = nl2br($msg).$unicorn;

echo '<p>';
while($data = $req->fetch()) {
	$nom = $data['nom']; $prenom = $data['prenom']; $email = $data['email'];
	if(mail_html($prenom, $nom, $email, 'LudoNoël', $msg)) {
		echo "Mail envoyé à $prenom $nom ($email) ! :)<br/>";
	}
	else
		echo "<strong>ERREUR d'envoi du mail</strong> à $prenom $nom ($email) ! :(<br/>";
}
echo '</p>';
$req = db()->query('UPDATE whitelist SET invitation_sent = 1');
bottom();
?>
