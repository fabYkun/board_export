<?php
$mois = array('Jan' => 'jan', iconv("UTF-8", "Windows-1252//TRANSLIT", 'Fév') => 'feb', 'Mar' => 'mar', 'Avr' => 'apr', 'Mai' => 'may', 'Juin' => 'jun', 'Juil' => 'jul', iconv("UTF-8", "Windows-1252//TRANSLIT", 'Aoû') => 'aug','Sep' => 'sep', 'Oct' => 'oct', 'Nov' => 'nov', iconv("UTF-8", "Windows-1252//TRANSLIT", 'Déc') => 'dec');

$forums = array(); // de la forme array('nomduforum' => id du forum), il ne doit pas y avoir d'espaces et si il y a un accent il faut convertir le nom en windows-1252 comme fait sur l'array précédant avec la fonction iconv
// NOTE : si vous avez des forums qui ont le même nom, rajoutez dans le nom de ces forums le forum ou la catégorie où il se trouve devant, par exemple si le forum 'cours' est dans le forum math, renommez le forum 'cours' en 'mathcours' dans cet array

$membres = array(); // de la forme array('nom du membre' => id du membre)
?>
