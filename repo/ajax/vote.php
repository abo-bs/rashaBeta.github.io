<?php
if(isset($_GET['j']) && isset($_GET['q']) && isset($_GET['t'])) {
	header("Cache-Control: no-cache");
	header("Pragma: nocache");

	$rating_unitwidth = 30;
	$vote_sent = preg_replace("/[^0-5]/","",$_GET['j']);
	$id_sent = preg_replace("/[^0-9a-zA-Z-.]/","",$_GET['q']);
	$ip_num = preg_replace("/[^0-9\.]/","",$_GET['t']);
	require_once '../includes/package.class.php';

	if (isBot()) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');
	if (empty($vote_sent) || $vote_sent < 1 || $vote_sent > 5) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');
	if (empty($id_sent)) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');
	if (empty($ip_num)) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');
	if ($ip_num !== getIp()) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');

	$paquet = new Paquet($id_sent);
	if(!$paquet->verifier_fiche()) exit('unit_long'.htmlspecialchars($id_sent).'|Sorry, vote appears to be invalid.');
	$lang_user = translation();
	$numbers = $paquet->package_control(array('total_votes', 'total_value', 'used_ips'));
	$checkIP = unserialize($numbers['used_ips']);
	$count = $numbers['total_votes'];
	$current_rating = $numbers['total_value']; 
	$sum = $vote_sent + $current_rating;
	$added = ($sum == 0) ? 0 : $count + 1;
	((is_array($checkIP)) ? array_push($checkIP, $ip_num) : $checkIP = array($ip_num));
	$insertip = serialize($checkIP);
	if(is_array(unserialize($numbers['used_ips'])))
		$voted = (in_array($ip_num, unserialize($numbers['used_ips']))) ? 1 : 0;
	else
		$voted = ($ip_num == unserialize($numbers['used_ips'])) ? 1 : 0;
	if(!$voted) {
		$pdo = PDO2::getInstance();
		$req = $pdo->prepare("UPDATE description INNER JOIN description_meta ON description.id = description_meta.id SET description_meta.total_votes = :added, description_meta.total_value = :sum, description_meta.used_ips = :insertip WHERE description.id = :id_sent");
		$req->execute(array(':added' => $added, ':sum' => $sum, ':insertip' => $insertip, ':id_sent' => $id_sent));
		$req->closeCursor();
		$pdo = PDO2::closeInstance();
	}
	$count = $added;
	$current_rating = $sum;
	$tense = ($count == 1) ? _('vote') : _('votes');

	$new_back = array();
	$new_back[] .= '<ul class="unit-rating" style="width:150px;">';
	$new_back[] .= '<li class="current-rating" style="width:'.($current_rating / $count * 30).'px;">Current rating.</li>';
	$new_back[] .= '<li class="r1-unit">1</li>';
	$new_back[] .= '<li class="r2-unit">2</li>';
	$new_back[] .= '<li class="r3-unit">3</li>';
	$new_back[] .= '<li class="r4-unit">4</li>';
	$new_back[] .= '<li class="r5-unit">5</li>';
	$new_back[] .= '</ul>';
	if($lang_user == 'ar_AR')
		$new_back[] .= '<div class="voted text-center"><strong>'.@number_format($current_rating / $count, 2).'</strong><span dir="ltr">/5 ('.$count.' '.$tense.')</span> ';
	else
		$new_back[] .= '<div class="voted text-center"><strong>'.@number_format($current_rating / $count, 2).'</strong><span>/5 ('.$count.' '.$tense.')</span> ';
	$new_back[] .= '<span class="thanks">'._('Thanks for voting !').'</span></div>';

	$allnewback = join("\n", $new_back);

	$output = "unit_long$id_sent|$allnewback";
	echo $output;
} else
	exit("Sorry, vote appears to be invalid."); ?>