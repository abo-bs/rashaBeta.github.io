<?php require_once('../includes/session.class.php');
$membre = new Session();
if($membre->_connected && $membre->_level > 0) {
	require_once('../includes/time-header.php');
	translation();
	$hint = '';
	$q = urldecode(addslashes(trim($_GET['q'])));
	if(!empty($q)) {
		$pdo = PDO2::getInstance();
		$req = $pdo->prepare('SELECT description.id, Name FROM description INNER JOIN description_meta ON description.id = description_meta.id Where Name LIKE :q');
		try {
			$req->execute(array(':q' => $q.'%'));
			$res = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();
			$pdo = PDO2::closeInstance();
		} catch (Exception $e) {
			$req->closeCursor();
			$res = array(array('id' => 'error', 'Name' => 'Error load data'));
			$pdo = PDO2::closeInstance();
		}
		$j = 0;
		$resultat = (count($res) < 2) ? _('result') : _('results');
		foreach($res as $id) {
			$j++;
			if(strlen($id['Name']) > 14)
				$nom_propre = substr($id['Name'], 0, 14).'...';
			else
				$nom_propre = $id['Name'];
			if(empty($hint))
				$hint = '<a class="resultats" href="manage-all.php?s='.preg_replace('/&amp/', '&', urlencode($id['Name'])).'">'.$nom_propre.'</a>';
			else
	 			$hint .= '<a class="resultats" href="manage-all.php?s='.urlencode($id['Name']).'">'.$nom_propre.'</a>';
		}
	}
	print empty($hint) ? '<span id="recherche" style="z-index:10"><span style="text-align:center;background:#00c;display:inherit;color:#fff">'._('No package found').'</span></span>' : '<span id="recherche" style="z-index:10"><span style="text-align:center;display:inherit;background:#00C;color:#fff">'.$j.' '.$resultat.'</span>'.$hint.'</span>';
} else
	require_once('../404.php'); ?>