<?php require_once('includes/session.class.php');
$membre = new Session();
if($membre->_connected && $membre->_level > 0) {
	if(empty($_GET['id']))
		exit(_('No package found'));
	require_once('includes/time-header.php');
	$file = trim($_GET['id']);
	$path = 'debs/org.goldencydia.'.$file.'/org.goldencydia.'.$file.'.deb';
	if(file_exists($path)) {
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$file.'_iphoneos-arm.deb');
		header('Content-Length: '.filesize($path));
		header('Cache-Control: private');
		ini_set('memory_limit', -1);
		readfile($path);
		ini_restore('memory_limit');
	} else
		exit(_('No package found'));
} else
	require_once('404.php'); ?>