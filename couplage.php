<?php
include('inc/functions.php');
forceLogin(1);
top();

db()->query("DELETE FROM friends WHERE id = friend");
db()->query("UPDATE users SET gift = 0");

$sommets = array("S" => "S", "P" => "P");
$left = [];
$req = db()->query('SELECT id FROM users WHERE participe = 1');
$nbusers = $req->rowCount();
while($data = $req->fetch()) {
	$left[] = $data['id'];
	$right[] = $data['id'];
}
shuffle($left);
shuffle($right);
$successeurs = [];
$successeurs["S"] = [];
$successeurs["P"] = [];

$predecesseurs = [];
$predecesseurs["S"] = [];
$predecesseurs["P"] = [];

$flot = [];
$flot["S"] = [];

foreach($left as $p) {
	$flot['L#'.$p] = [];
	$sommets['L#'.$p] = $p;
	$successeurs["S"][] = 'L#'.$p;
	$flot["S"]['L#'.$p] = 0;
	$successeurs['L#'.$p] = [];
	$predecesseurs['L#'.$p] = [];
	$predecesseurs['L#'.$p][] = "S";
}
foreach($right as $p) {
	$flot['R#'.$p] = [];
	$sommets['R#'.$p] = $p;
	$predecesseurs["P"][] = 'R#'.$p;
	$flot['R#'.$p]["P"] = 0;
	$predecesseurs['R#'.$p] = [];
	$successeurs['R#'.$p] = [];
	$successeurs['R#'.$p][] = "P";
}

$req = db()->query('SELECT f.id, f.friend FROM friends f LEFT JOIN users u1 ON u1.id = f.id LEFT JOIN users u2 ON u2.id = f.friend WHERE u1.participe = 1 AND u2.participe = 1');
$datas = [];
while($data = $req->fetch()) $datas[] = $data;
shuffle($datas);
foreach ($datas as $data) {
	$successeurs['L#'.$data['id']][] = 'R#'.$data['friend'];
	$predecesseurs['R#'.$data['friend']][] = 'L#'.$data['id'];
	$flot['L#'.$data['id']]['R#'.$data['friend']] = 0;
}

// var_dump($successeurs);

// set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
	// echo "WTF L$errline $errstr";
	// var_dump($errcontext['flot']);
	// var_dump($errcontext['mark']);
	// var_dump($errcontext['curr']);
	// die(); });
function runFordFulk() {
	global $flot, $successeurs, $predecesseurs, $debug;
	while(true) {
		// echo 'START LOOP<br/>';
		// ALGO DE MARQUAGE
			$marked = ['S'];
			$mark = [];
			$mark['S'] = [INF, null];
			$examined = [];
			// TQ il existe un sommet marqué non examiné et que p n'est pas marqué
			while(!isset($mark["P"])) {
				$sommet = null;
				for($i = count($marked)-1; $i >= 0; $i--) {
					if(!isset($examined[$marked[$i]]))
					{
						$sommet = $marked[$i];
						break;
					}
				}
				if($sommet == null) {
					break;
				}
				// echo 'sommet marqué non examiné '; var_dump($sommet);
				// On a trouvé un sommet marqué non examiné
				$examined[$sommet] = true;
				foreach($successeurs[$sommet] as $s) {
					if(!isset($mark[$s]) && $flot[$sommet][$s] == 0) { // Le sommet n'est pas encore marqué et on peut le marquer
						$mark[$s] = [1, $sommet];
						$marked[] = $s;
					}
				}
				foreach($predecesseurs[$sommet] as $s) {
					if(!isset($mark[$s]) && $flot[$s][$sommet] == 1) { // Le sommet n'est pas encore marqué et on peut le marquer
						$mark[$s] = [-1, $sommet];
						$marked[] = $s;
					}
				}
				// On le marque
				// echo 'marked '; var_dump($marked);
				// die();
			}
		if($debug) { echo 'mark '; var_dump($mark); }
		// à répéter tq P est marqué
		if(!isset($mark['P'])) break;
		// P est marqué, on reconstitue la chaine augmentante et on met à jour le flot
		$curr = "P";
		if($debug) echo 'START chain<br/>';
		while($curr != 'S') {
			if($mark[$curr][0] < 0)
				$flot[$curr][$mark[$curr][1]] -= $mark['P'][0];
			else
				$flot[$mark[$curr][1]][$curr] += $mark['P'][0];
			$curr = $mark[$curr][1];
			if($debug) echo $curr.'<br/>';
		}
		if($debug) var_dump($flot);
	}
}
$debug = false;
runFordFulk();

$correctly_associated = 0;
$associated = 0;
$associations = [];
foreach($flot as $start => $arr) {
	if($start != "S") {
		foreach($arr as $end => $val) {
			if($end != "P") { // Si c'est un sommet de gauche
				if($val == 1) {
					$correctly_associated++;
					$associations[$start] = $end;
				}
			}
		}
	}
}
echo "<h3>Couplage</h3><p>$correctly_associated/$nbusers personnes correctement associées.<br/>";
// On prépare pour l'association non optimale en rajoutant des nouveaux liens entre tous les gens non associés
foreach($left as $l) {
	$start = 'L#'.$l;
	if($flot['S'][$start] == 0) {
		foreach($right as $r) {
			if($r != $l) { // Car on ne peut pas associer une personne à elle même
				$end = 'R#'.$r;
				if(!isset($flot[$start][$end]) && $flot[$end]['P'] == 0) {
					$successeurs[$start][] = $end;
					$predecesseurs[$end][] = $start;
					$flot[$start][$end] = 0;
				}
			}
		}
	}
}
//On associe ceux qui restent
//$debug = true;
runFordFulk();

foreach($flot as $start => $arr) {
	if($start != "S") {
		foreach($arr as $end => $val) {
			if($end != "P" && $val == 1) {
				$associated++;
				if(isset($associations[$start])) {
					if ($associations[$start] != $end) echo 'Erreur, association optimale modifiée lors de l\'association des gens restants, go debug.<br/>'; // Ceci ne devrait jamais arriver
				}
				else {
					$req = db()->prepare('SELECT prenom, nom FROM users WHERE id = ?');
					$req->execute(array(substr($start,2)));
					$data = $req->fetch();
					echo $data['prenom'].' '.$data['nom']." n'a pas pu offrir à qui il/elle souhaitait et a été(e) associé(e) aléatoirement.<br/>";
				}
				db()->prepare("UPDATE users SET gift = ? WHERE id = ?")->execute(array(substr($end,2), substr($start,2)));
			}
		}
	}
}

echo "$associated/$nbusers personnes associées.</p>";

bottom();
?>