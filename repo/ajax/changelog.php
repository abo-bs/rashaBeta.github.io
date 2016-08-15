<?php header('Content-type: application/json');
require_once('../includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('../includes/time-header.php');
	require_once('../includes/package.class.php');
	translation();
	$idverif = trim($_GET['id']);
	if(isset($_GET['id'])) {
		$paquet = new Paquet($idverif);
		$infos = $paquet->package_control(array('Name', 'id_membre'));		
		if(!$paquet->verifier_fiche())
			exit('{"numError": 0, "error": '.json_encode(_('This package does not exist !')).'}');
		elseif($membre->_level < 3 && $infos['id_membre'] != $membre->_id)
			exit('{"numError": 0, "error": '.json_encode(_('You are not allowed to edit this package !')).'}');
	} else
		exit('{numError": 0, "error": '.json_encode(_('Identifier not defined !')).'}');
	if(isset($_GET['id']) && isset($_POST['changelog'])) {
		$paquet->changer_control($_POST['changelog'], 'changelog');
		echo '{"numError": 1, "error": '.json_encode(sprintf(_('Recents changes about %s has been updated !'), $infos['Name'])).'}';
	} else
		echo '{"numError": 0, "error": '.json_encode(_('You need to post something !')).'}';
} else
	require_once('../404.php'); ?>