<?php header('Content-type: application/json');
require_once('../includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT COUNT(id) FROM download WHERE date LIKE :date");
	$req->execute(array(':date' => date('Y-m-d', time())."%"));
	$total_download_jour = $req->fetchColumn();
	$req->closeCursor();
	$pdo = PDO2::closeInstance();
echo '{
	"total_udid": '.json_encode(preg_replace('/[^0-9]+/', '', total_udid())).',
	"total_member": '.json_encode(preg_replace('/[^0-9]+/', '', totalMembre())).',
	"total_download_day": '.json_encode($total_download_jour).',
	"total_download": '.json_encode(preg_replace('/[^0-9]+/', '', totalDownload())).'
}';
} else
	require_once('404.php'); ?>