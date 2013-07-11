<?php
$salage1 = ''; // veuillez écrire quelque chose dans cette variable
$salage2 = ''; // ici également

$membres = "  Pseudo  email_membre1@gmail.com	nombre_messages	date_inscription	date_dernière_activité	
blopblo	blabla@hotmail.com	0	11 Jan 2011	29 Oct 2012	
twYnn	gmail@live.fr	17	28 Juil 2012	03 Juil 2013"; // La chaine doit ressembler à cela, sinon il faut modifier le $chaine = explode un peu plus bas

function date_replace($value){
	$value = str_replace('Jan', 'january', $value);
	$value = str_replace('Fév', 'february', $value);
	$value = str_replace('Mar', 'march', $value);
	$value = str_replace('Avr', 'april', $value);
	$value = str_replace('Mai', 'may', $value);
	$value = str_replace('Juin', 'june', $value);
	$value = str_replace('Juil', 'july', $value);
	$value = str_replace('Aoû', 'august', $value);
	$value = str_replace('Sep', 'september', $value);
	$value = str_replace('Oct', 'october', $value);
	$value = str_replace('Nov', 'november', $value);
	$value = str_replace('Déc', 'december', $value);
	return strtotime($value);
}

$mail = '';
$chaine = explode('	', $membres);
$compte = 0;
$id = 1; // On commence à partir de l'id 1 car normalement il y a déjà un utilisateur sur votre base de donnée : l'administrateur, si il n'y a vraiment personne mettez cette valeur à 0
echo 'Ce qui va suivre sont les commandes sql à executer sur votre interface de gestion bdd. Elles ne sont pas exécutés car les mots de pass sont aléatoirements choisis et il n\'est pas possible de retrouver le mot de pass original une fois que l\'enregistrement sera terminé et que vous aurez quitté cette page. Avant de les exécuter, copiez-collez les informations tout en bas de la page afin d\'envoyer un mail aux intéressés qui contiendra leurs mots de pass générés.<br /><br /> ';
foreach ($chaine as $key => $value) {
	$compte ++;
	switch ($compte) {
		case 1:
			$user = array();
			$user['pseudo'] = substr($value, 2);
			break;
		case 2:
			$user['email'] = $value;
			break;
		case 3:
			$user['msg'] = $value;
			break;
		case 4: 
			$user['inscription'] = date_replace($value);
			break;
		case 5:
			$id ++;
			$user['activite'] = date_replace($value);
			$mdp = str_split(md5(rand(0,666).$salage2.rand(0,666).$salage1.rand(0,666)));
			$salt = $mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)];
			$mdp = $mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)].$mdp[rand(0,20)];

			// a modifier selon la plateforme de forum où vous êtes, celle-ci a été faite pour un forum invision power board
			// NOTE : les mots de pass sont cryptés selon différentes méthodes, ipboard les crypte dans la bdd en utilisant un salage qui est stocké sur la même table de membre, mais ce n'est sans doute pas la même méthode utilisée partout ainsi renseignez-vous sur les méthodes de cryptage de votre forum
			echo "INSERT INTO `members` (`member_id`, `name`, `member_group_id`, `email`, `joined`, `ip_address`, `posts`, `title`, `allow_admin_mails`, `time_offset`, `hide_email`, `email_full`, `skin`, `warn_level`, `warn_lastwarn`, `language`, `last_post`, `restrict_post`, `view_sigs`, `view_img`, `view_avs`, `bday_day`, `bday_month`, `bday_year`, `msg_count_new`, `msg_count_total`, `msg_count_reset`, `msg_show_notification`, `misc`, `last_visit`, `last_activity`, `dst_in_use`, `view_prefs`, `coppa_user`, `mod_posts`, `auto_track`, `temp_ban`, `sub_end`, `login_anonymous`, `ignored_users`, `mgroup_others`, `org_perm_id`, `member_login_key`, `member_login_key_expire`, `subs_pkg_chosen`, `has_blog`, `has_gallery`, `members_editor_choice`, `members_auto_dst`, `members_display_name`, `members_seo_name`, `members_created_remote`, `members_cache`, `members_disable_pm`, `members_l_display_name`, `members_l_username`, `failed_logins`, `failed_login_count`, `members_profile_views`, `members_pass_hash`, `members_pass_salt`, `identity_url`, `member_banned`, `member_uploader`, `members_bitoptions`, `fb_uid`, `fb_emailhash`, `fb_lastsync`, `members_day_posts`, `live_id`, `twitter_id`, `twitter_token`, `twitter_secret`, `notification_cnt`, `tc_lastsync`, `fb_session`, `fb_token`, `ips_mobile_token`) VALUES ($id, '".addslashes($user['pseudo'])."', '3', '".$user['email']."', '".$user['inscription']."', '', '".$user['msg']."', '', '1', '1', '1', NULL, NULL, NULL, '0', '2', NULL, '0', '1', '1', '1', '0', '0', '0', '0', '0', '0', '1', NULL, '".$user['activite']."', '".$user['activite']."', '0', '-1&-1', '0', '0', '0', '0', '0', '0&0', 'a:0:{}', '', '', '', '1374039557', '0', NULL, '0', 'std', '0', '".addslashes($user['pseudo'])."', '".addslashes($user['pseudo'])."', '0', NULL, '0', '".addslashes($user['pseudo'])."', '".addslashes($user['pseudo'])."', NULL, '0', '0', '".md5(md5($salt).md5($mdp))."', '$salt', NULL, '0', 'default', '0', '0', '', '0', '0,0', NULL, '', '', '', '0', '0', '', NULL, NULL); <br />";

			echo "INSERT INTO `profile_portal` (`pp_member_id`, `pp_last_visitors`, `pp_rating_hits`, `pp_rating_value`, `pp_rating_real`, `pp_main_photo`, `pp_main_width`, `pp_main_height`, `pp_thumb_photo`, `pp_thumb_width`, `pp_thumb_height`, `pp_setting_moderate_comments`, `pp_setting_moderate_friends`, `pp_setting_count_friends`, `pp_setting_count_comments`, `pp_setting_count_visitors`, `pp_about_me`, `pp_reputation_points`, `notes`, `signature`, `avatar_location`, `avatar_size`, `avatar_type`, `pconversation_filters`, `fb_photo`, `fb_photo_thumb`, `fb_bwoptions`, `tc_last_sid_import`, `tc_photo`, `tc_bwoptions`, `pp_customization`) VALUES ('$id', NULL , '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '1', '1', '0', NULL , '0', NULL , '', NULL , '0', NULL , '', NULL , NULL , '0', '0', NULL , '0', NULL);<br /><br />";

			$mail .= '-'.$user['pseudo'].' '.$user['email'].' '.$mdp;
			$compte = 0;
			break;
	}
}
echo '<br /><br />Informations des membres : <br />'.$mail;
?>
