<?php
require 'api.php'; // importe l'api Simple HTML DOM

function insert_topic(array $topic, $pdo){
  try{
		// il faut évidemment modifier la requête sql ci-dessous, ici elle a été écrite pour un forum invision power board
        $insert = $pdo->prepare('INSERT INTO `topics`(`title`, `description`, `state`, `starter_id`, `start_date`, `last_poster_id`, `last_post`, `icon_id`, `starter_name`, `last_poster_name`, `poll_state`, `last_vote`, `views`, `forum_id`, `approved`, `author_mode`, `pinned`, `topic_firstpost`, `title_seo`, `seo_last_name`, `seo_first_name`) VALUES (:title, :description, :state, :starter_id, :start_date, :last_poster_id, :last_post, :icon_id, :starter_name, :last_poster_name, :poll_state, :last_vote, :views, :forum_id, :approved, :author_mode, :pinned, :topic_firstpost, :title_seo, :seo_last_name, :seo_first_name)');

        $insert->bindValue(':title', iconv("Windows-1252", "UTF-8//TRANSLIT", $topic['titre']), PDO::PARAM_STR);
        $insert->bindValue(':description', '', PDO::PARAM_STR);
        $insert->bindValue(':state', 'open', PDO::PARAM_STR);
        $insert->bindValue(':starter_id', $topic['id_auteur_premier'], PDO::PARAM_INT);
        $insert->bindValue(':start_date', $topic['date_auteur_premier'], PDO::PARAM_INT);
        $insert->bindValue(':last_poster_id', $topic['id_auteur_dernier'], PDO::PARAM_INT);
        $insert->bindValue(':last_post', $topic['date_auteur_dernier'], PDO::PARAM_INT);
        $insert->bindValue(':icon_id', 0, PDO::PARAM_INT);
        $insert->bindValue(':starter_name', iconv("Windows-1252", "UTF-8//TRANSLIT", $topic['pseudo_auteur_premier']), PDO::PARAM_STR);
        $insert->bindValue(':last_poster_name', iconv("Windows-1252", "UTF-8//TRANSLIT", $topic['pseudo_auteur_dernier']), PDO::PARAM_STR);
        $insert->bindValue(':poll_state', 0, PDO::PARAM_INT);
        $insert->bindValue(':last_vote', 0, PDO::PARAM_INT);
        $insert->bindValue(':views', (25*$topic['nb_messages']+rand($topic['nb_messages'], $topic['nb_messages']*15)), PDO::PARAM_INT);
        $insert->bindValue(':forum_id', $topic['localisation'], PDO::PARAM_INT);
        $insert->bindValue(':approved', 1, PDO::PARAM_INT);
        $insert->bindValue(':author_mode', 1, PDO::PARAM_INT);
        $insert->bindValue(':pinned', 0, PDO::PARAM_INT);
        $insert->bindValue(':topic_firstpost', $topic['id_message_premier'], PDO::PARAM_INT);
        $insert->bindValue(':title_seo', '', PDO::PARAM_STR);
        $insert->bindValue(':seo_last_name', iconv("Windows-1252", "UTF-8//TRANSLIT", strtolower($topic['pseudo_auteur_dernier'])), PDO::PARAM_STR);
        $insert->bindValue(':seo_first_name', iconv("Windows-1252", "UTF-8//TRANSLIT", strtolower($topic['pseudo_auteur_premier'])), PDO::PARAM_STR);
        $insert->execute();
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        echo 'Une erreur s\'est produite :'."\n", $e->getMessage();
    }
}

function insert_post(array $post, $pdo){
	try{
		// il faut évidemment modifier la requête sql ci-dessous, ici elle a été écrite pour un forum invision power board
        $insert = $pdo->prepare('INSERT INTO `posts`(`author_id`, `author_name`, `use_sig`, `use_emo`, `ip_address`, `post_date`, `icon_id`, `post`, `topic_id`, `new_topic`) VALUES (:id_auteur, :auteur, :use_signemo, :use_signemo, :adresse_ip, :date_post, :icon_id, :post, :id_topic, :nouveau_topic)');

		$insert->bindValue(':id_auteur', $post['id_membre'], PDO::PARAM_INT);
		$insert->bindValue(':auteur', iconv("Windows-1252", "UTF-8//TRANSLIT", $post['auteur']), PDO::PARAM_STR);
		$insert->bindValue(':use_signemo', 1, PDO::PARAM_INT); 
		$insert->bindValue(':adresse_ip', '', PDO::PARAM_STR);
		$insert->bindValue(':date_post', $post['date'], PDO::PARAM_INT);
		$insert->bindValue(':icon_id', 0, PDO::PARAM_INT);
        $insert->bindValue(':post', iconv("Windows-1252", "UTF-8//TRANSLIT", $post['message']), PDO::PARAM_STR);
        $insert->bindValue(':id_topic', $post['topic_id'], PDO::PARAM_INT);
        if(isset($post['premierpost']) && $post['premierpost'] == true)
        	$insert->bindValue(':nouveau_topic', 1, PDO::PARAM_INT);
        else
        	$insert->bindValue(':nouveau_topic', 0, PDO::PARAM_INT);
        $insert->execute();
        return $pdo->lastInsertId();

    } catch (Exception $e) {
        echo 'Une erreur s\'est produite :'."\n", $e->getMessage();
    }
}

function html_to_bbcode($message){ // il faut peut-être modifier certains détails, chaque plateforme a son bbcode même si ils sont relativement peu différents, ici les fonctions ont étés écrites pour un forum invision power board
	foreach($message->find('img') as $element)
			$element->innertext = '[img]'.$element->src.'[/img]';

	foreach($message->find('embed') as $element){
		$element->innertext = '[media]'.$element->src.'[/media]';
		$element->innertext = str_replace('/v/', '/watch?v=', $element->innertext);
	}

	foreach($message->find('a') as $element)
		$element->innertext = '[url="'.$element->href.'"]'.$element->plaintext.'[/url]';

	foreach($message->find('span') as $element)
		if (preg_match('/font-size: ([0-9]+)px;/', $element->style, $match))
			if($match[1] <= 10)
				$element->innertext = '[size="1"]'.$element->innertext.'[/size]';
			elseif($match[1] > 16)
				$element->innertext = '[size="4"]'.$element->innertext.'[/size]';
			elseif($match[1] > 20)
				$element->innertext = '[size="5"]'.$element->innertext.'[/size]';

	// balises [code], [quote] et [spoiler]
	foreach($message->find('table') as $element){

		$isquote = null;
		$iscode = null;
		if (preg_match('/([a-zA-Z0-9_éèàôçîöï@ù -]+) a .crit/', $element->plaintext, $match))
			$auteur = $match[1];

		foreach($element->find('td.quote') as $quote){
			if(isset($auteur))
				$element->innertext = '[quote name=\''.$auteur.'\']'.$quote->plaintext.'[/quote]';
			else
				$element->innertext = '[quote]'.$quote->plaintext.'[/quote]';
			$isquote = true;
		}

		foreach($element->find('td.code') as $code){
			$element->innertext = '[code]'.$code->plaintext.'[/code]';
			$iscode = true;
		}

		if(!isset($isquote)){
			foreach($element->find('td.spoiler_content') as $spoiler)
				$element->innertext = '[spoiler]'.$spoiler->plaintext.'[/spoiler]';
		}
	}

	foreach($message->find('div[align=right]') as $element)
		$element->innertext = '[right]'.$element->innertext.'[/right]';

	foreach($message->find('div[align=center]') as $element)
		$element->innertext = '[center]'.$element->innertext.'[/center]';
				
	$post['message'] = $message->find('div.postbody div', 0)->innertext;
	$post['message'] = str_replace('<strong>', '[b]', $post['message']);
	$post['message'] = str_replace('</strong>', '[/b]', $post['message']);
	$post['message'] = str_replace('<strike>', '[s]', $post['message']);
	$post['message'] = str_replace('</strike>', '[/s]', $post['message']);
	$post['message'] = str_replace('<i>', '[i]', $post['message']);
	$post['message'] = str_replace('</i>', '[/i]', $post['message']);
	$post['message'] = str_replace('<u>', '[u]', $post['message']);
	$post['message'] = str_replace('</u>', '[/u]', $post['message']);
	$post['message'] = str_replace('<ol type="1">', '[list=1]', $post['message']);
	$post['message'] = str_replace('<ul>', '[list]', $post['message']);
	$post['message'] = str_replace('<li>', '[*]', $post['message']);
	$post['message'] = str_replace('</li>', '', $post['message']);
	$post['message'] = str_replace('</ul>', '[/list]', $post['message']);
	$post['message'] = str_replace('</ol>', '[/list]', $post['message']);
	$post['message'] = strip_tags($post['message'], '<br /><br/><br>');
	return $post['message'];
}
