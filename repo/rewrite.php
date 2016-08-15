<?php $file = trim($_GET['id']);
if(isset($file) && isset($_SERVER['HTTP_X_UNIQUE_ID']) && preg_match('/[a-z]+[0-9]+/', $_SERVER['HTTP_X_UNIQUE_ID']) && strlen($_SERVER['HTTP_X_UNIQUE_ID']) === 40 && strpos($_SERVER['HTTP_USER_AGENT'], 'Telesphoreo') !== FALSE) {
	require_once("includes/package.class.php");
	$id_user = verifier_udid($_SERVER['HTTP_X_UNIQUE_ID']);
	$paquet = new Paquet($file);
	if($paquet->verifier_deb() && $id_user != false) {
		$path = 'debs/org.goldencydia.'.$file.'/org.goldencydia.'.$file.'.deb';
		$paquet->compter_telechargement($id_user);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$file.'_iphoneos-arm.deb');
		header('Content-Length: '.filesize($path));
		header('Cache-Control: private');

		ini_set('memory_limit', -1);
		$f = fopen($path, 'r');
		if($f) {
			set_time_limit(0);
			fpassthru($f);
			fclose($f);
		} else
			exit('Couldn\'t read file.');
		ini_restore('memory_limit');
	} else {
		require_once('404.php');
		file_put_contents('includes/cache/test.txt', $_SERVER['HTTP_X_UNIQUE_ID']);
	}
} else
	require_once('404.php'); ?>