board_export
============

Récupère les données primordiales d'un forum (membres et topics) dont la base de données est inaccessible, vous ne devez en aucun cas utiliser ces scripts pour voler littéralement un forum, si vous n'avez pas l'accord des administrateurs ne vous risquez pas dans cette opération. Ces scripts sont basés sur les forums phpbb de forumactif et sont donc susceptibles de devoir être modifiés pour d'autres forums. 
Ces scripts utilisent l'api [Simple HTML DOM](http://simplehtmldom.sourceforge.net/) pour identifier puis isoler les données clés. 

Pré-requis
------------

Il est recommandé de savoir un minimum programmer car des modifications sont nécessaires au niveau du code, il n'est en effet pas adapté a tout type de forums. 
Pour correctement fonctionner, l'importation des données nécessite deux variables, toutes deux sous la forme d'un tableau associatif. Nous avons en effet besoin d'un array reliant chaque membre à son identifiant de la nouvelle base de données et un array reliant chaque nom de forum à son identifiant également. 

Afin de récupérer les utilisateurs de votre forum et, étant donné que vous n'avez pas accès à la bdd, il faut une liste qui comprend au moins les adresses emails et les pseudos. Sur forumactif une liste, fournissant également d'autres informations, est disponible en cliquant sur "Gestion des utilisateurs". Elle se présente sous cette forme :
- [Nom d'utilisateur]  [E-mail]	[Messages]	[Inscrit le]	[Dernière visite]	[Actif]	[Action]

Je n'ai pas trouvé de meilleure technique que de copier/coller chaque page une par une pour coller les données dans une chaine de caractère. Ce qui nous donne quelque chose du style pour chaque utilisateur :
- twYnn  email@domaine.fr	5346	09 Fév 2008	11 Juil 2013	Oui	Modifier un utilisateur  Permissions

Quand vous aurez rassemblé les données des utilisateurs a exporter, vous pourrez lancer le script membre.php


Vous avez probablement moins d'une centaine de forums et catégories sur votre forum, vous gagnerez du temps à construire par vous même les forums et sous-forums sur votre nouvelle plateforme, à la main. N'oubliez pas de les nommer exactement comme le forum original ou jamais l'export ne marchera comme prévu. 
Une fois reconstruits, un simple fetch de `SELECT * FROM forums` combiné a une boucle while vous permettra de construire votre array. 

!! À noter : Si vous exportez un forumactif, il y a de fortes chances qu'il soit (hélas) encore encodé en windows-1251, l'utilisation de iconv est fortement recommandée dans votre array pour chaque clé comportant un accent, par exemple : `array([...], iconv("UTF-8", "Windows-1252//TRANSLIT", 'ForumScénario')=>4, [...]);`

Ces deux variables devrons êtres stockés dans un fichier nommé ressources.php

Pour finir, il faut évidemment récupérer le contenu de vos pages. De nombreux outils le permettent mais le plus simple et le plus efficace est probablement wget. C'est une fonction de la console linux mais elle est utilisable sur windows également, pour cela rendez-vous sur la page de [GNU Windows](http://gnuwin32.sourceforge.net/install.html) et téléchargez wget pour windows. 
Afin d'exécuter correctement le logiciel et qu'il puisse écrire les fichiers analysés, il faut ouvrir le cmd de windows en mode administrateur, accéder au dossier d'installation puis, une fois dans le dossier parent de wget, l'executer avec ces commandes : `wget.exe -r -e robots=off --wait=1 "http://monforum.com"`. Si tout se passe bien vous devriez vous retrouver avec une tonne de fichier html regroupés dans un dossier à l'intérieur du dossier parant de wget. Le nom des fichiers qui nous intéressent commencent par t puis d'un nombre et enfin le nom du topic. 
Les noms de fichiers comportant une lettre juste après le nombre, comme `t28n` par exemple sont des topics inexistants mais vous pouvez les laisser, par contre s'il y a des doublons le script incomportera les réponses de ces doublons aux topics déjà crées... Cela arrive rarement mais ça peut arriver, lorsque wget crée un fichier dont la terminaison est `@highlight=[mot-clé]`

Utilisation
------------

get.php est le script à lancer lorsque vous aurez complété les variables de ressources.php et que vos dossiers comportant les topics serons crées. Veuillez lire les premières lignes de chaque fichier, sauf api.php, il faudra probablement modifier beaucoup de lignes de codes. 
