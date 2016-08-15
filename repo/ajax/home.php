<?php header('Content-type: application/json');
require_once('../includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('../includes/time-header.php');
	$lang_user = translation();
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT COUNT(udid) FROM users WHERE date_update LIKE :date AND date < :date");
	$req->execute(array(':date' => date('Y-m-d', time())."%"));
	$total_udid_jour = $req->fetchColumn();
	$req->closeCursor();

	$req = $pdo->prepare("SELECT COUNT(udid) FROM users WHERE date LIKE :date AND date_update IS NOT NULL");
	$req->execute(array(':date' => date('Y-m-d', time())."%"));
	$total_new_jour = $req->fetchColumn();
	$req->closeCursor();

	$req = $pdo->prepare("SELECT COUNT(udid) FROM users WHERE date_update >= DATE_SUB(NOW(), INTERVAL 1 WEEK) AND date < DATE_SUB(NOW(), INTERVAL 1 WEEK)");
	$req->execute();
	$total_udid_week = $req->fetchColumn();
	$req->closeCursor();

	$req = $pdo->prepare("SELECT COUNT(udid) FROM users WHERE date >= DATE_SUB(NOW(), INTERVAL 1 WEEK) AND date_update IS NOT NULL");
	$req->execute();
	$total_new_week = $req->fetchColumn();
	$req->closeCursor();
	$pdo = PDO2::closeInstance();
echo '{
	"total_udid_jour": '.json_encode($total_udid_jour).',
	"total_udid_week": '.json_encode($total_udid_week).',
	"total_new_jour": '.json_encode($total_new_jour).',
	"total_new_week": '.json_encode($total_new_week).'
}';
} else
	require_once('../404.php'); ?>