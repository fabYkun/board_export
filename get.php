<?php
// Cela peut être utile de modifier le temps maximal d'execution d'un script car les données peuvent rapidement être nombreuses, enlevez le commentaire si nécessaire
// set_time_limit(90); 

$host = ''; // l'adresse de votre serveur mysql
$dbname = ''; // Nom de la base à utiliser pour l'importation des données
$dbuser =''; // Utilisateur
$dbmdp = ''; // Mot de passe
$repertoire = ''; // Chemin contenant les fichiers html des topics à importer
/* Il est important de séparer en plusieurs dossiers vos fichiers, plus il y en a plus le script prendra de temps. 70fichiers par dossier semble être une quantité raisonnable
Lorsqu'une erreur apparait, le dernier sujet traité n'est pas importé, mais si vous relancez le script les fichiers déjà importés le serons à nouveau : il faut alors les supprimer du répertoire */
$doublons = array(); // Si plusieurs forums ont le même nom, il faut les ajouter à cet array, exemple : array('Cours', 'Atelier'), si c'est le cas il y a une modification a appliquer à votre array de ressources.php

try {
    $pdo = new PDO('mysql:host='.$host.';dbname='.$dbdame, $dbuser, $dbmdp);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
    $msg = 'ERREUR PDO dans ' . $e->getFile() . ' L.' . $e->getLine() . ' : ' . $e->getMessage();
    die('<h3>La base de donnée n\'est malheureusement pas disponible, ré-essayez peut-être plus tard ?</h3><br />Message pour le dév : '.$msg);
}

require 'fonctions.php'; // importe les fonctions importantes
require 'ressources.php'; // importe les variables

$topic = array('id' => 1, 'titre' => '', 'localisation' => 0, 'nb_messages' => 0, 'id_message_premier' => 0, 'pseudo_auteur_premier' => '', 'id_auteur_premier' => 0, 'date_auteur_premier' => 0, 'pseudo_auteur_dernier' => '', 'id_auteur_dernier' => 0, 'date_auteur_dernier' => 0);
$post = null;
$dir = 'topics/'.$_GET['topic'].'/';
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file != '.' && $file != '..'){
        		$premierpost = true;
        		$localisation = null;
        		$y ++;
	            $html = file_get_html($dir.$file);
	            echo ' - '.$file.'<br /> <br />';
	            
	            $emplacement = $html->find('table#info_close', 0)->plaintext;
				if(empty($emplacement)){ // ancienne méthode au cas où
					$i = 0;
					foreach($html->find('span.nav') as $categories){
						$i++;
						if($i == 2)
							$emplacement = $categories->plaintext;
					}
				}

				$emplacement = str_replace(' ', '', $emplacement);
				$emplacement = str_replace('&nbsp;', '', $emplacement);
				$emplacement = explode('::', $emplacement);
				$emplacementreverse = array_reverse($emplacement);

				if(isset($doublons[$emplacementreverse[0]]) // si il s'agit d'un forum dont le nom est utilisé autrepart
					if(isset($forums[$emplacementreverse[1].$emplacementreverse[0]]))
						$localisation = $forums[$emplacementreverse[1].$emplacementreverse[0]];

				if(!isset($localisation)) {
					foreach ($emplacement as $key => $value) {
						if(isset($forums[$value]))
							$localisation = $forums[$value];
					}
				}

				if(!empty($localisation)){ // on ne peux pas ajouter qqch si on sait pas où le mettre =/

					foreach($html->find('tr.post') as $message) {
						$ancienpost = $post;
						$post = array();

					    $post['auteur'] = trim($message->find('div.name', 0)->plaintext);
					    if(isset($membres[$post['auteur']]))
					    	$post['id_membre'] = $membres[$post['auteur']];
					    else
					    	$post['id_membre'] = 0; // il ne sera pas reconnu comme membre =(


					    $post['sujet'] = $message->find('tr td span.postdetails', 0)->plaintext;
					    if (preg_match('/[A-Za-z]{3,4} [0-9]{1,2} .{3,4} [0-9]{4} ?(-|,) [0-9]{1,2}:[0-9]{2}/', $post['sujet'], $match))
					    	$post['date'] = $match[0];
					    elseif(preg_match('/[A-Za-z]{3,4} [0-9]{1,2} .{3,4} ?(-|,) [0-9]{1,2}:[0-9]{2}/', $post['sujet'], $match))
					    	$post['date'] = $match[0];

					    if(!empty($post['date'])){
					    	$date_format_long = $post['date'];
					    	$date = str_replace('- ', '', $post['date']);
							$date = explode(' ', $date);
							$newstr = '';

							foreach ($date as $key => $value) {
								if($key != 0){ 
									if($key == 2) // les mois
										foreach ($mois as $mois => $month)
											if($value == $mois)
												$value = $month;
									$newstr .= ' '.$value;
								}
							}
							$post['date'] = strtotime($newstr);
					    }

						if($premierpost == true){ // c'est la première fois qu'on traite cette page
							$premierpost = false;
						    $current_topic = trim($html->find('h1.cattitle', 0)->plaintext, "&nbsp; ");
						    if(!empty($topic['titre']) && $current_topic != $topic['titre']){ // c'est n'est PAS une page d'un topic déjà existant, on envoi sur la bdd le topic puis on ré-initialise
						    	$premierpost = true;
						    	$topic['pseudo_auteur_dernier'] = $ancienpost['auteur'];
	 							$topic['id_auteur_dernier'] = $ancienpost['id_membre'];
	 							$topic['date_auteur_dernier'] = $ancienpost['date'];
						    	$topic['id'] = insert_topic($topic, $pdo) +1; // (ne pas oublier que si la boucle n'est pas redémarrée (qu'on atteind la fin de la liste) il faudra quand même poster le topic !)
						    	$topic = array('id' => $topic['id'], 'titre' => $current_topic, 'localisation' => $localisation, 'nb_messages' => 0, 'id_message_premier' => 0, 'pseudo_auteur_premier' => $post['auteur'], 'id_auteur_premier' => $post['id_membre'], 'date_auteur_premier' => $post['date'], 'pseudo_auteur_dernier' => '', 'id_auteur_dernier' => 0, 'date_auteur_dernier' => 0);
						    } elseif(empty($topic['titre'])){
						    	$premierpost = true;
						    	$topic = array('id' => 1, 'titre' => $current_topic, 'localisation' => $localisation, 'nb_messages' => 0, 'id_message_premier' => 0, 'pseudo_auteur_premier' => $post['auteur'], 'id_auteur_premier' => $post['id_membre'], 'date_auteur_premier' => $post['date'], 'pseudo_auteur_dernier' => '', 'id_auteur_dernier' => 0, 'date_auteur_dernier' => 0);
						    }
						    
						} elseif(empty($post['date']))
							$post['date'] = $ancienpost['date'] + rand(66, 666); // si jamais on arrive vraiment pas à définir une date on la définie comme étant approximativement proche du dernier post (si c'est sur un même sujet)
						
						if(empty($post['date'])) // sinon 1er janvier 1970 x)
							$post['date'] = 0;

						$post['premierpost'] = $premierpost;
					   	$post['message'] = html_to_bbcode($message);
					    $post['topic_id'] = $topic['id'];
					    
					    if($post['premierpost'] == true)
					    	$topic['id_message_premier'] = insert_post($post, $pdo);
					    else
					    	insert_post($post, $pdo);
					    $topic['nb_messages'] += 1;

				    }
				}
			}
			echo '<br /> <br />';
        }
        closedir($dh);
        insert_topic($topic, $pdo);
    }
}
?>
