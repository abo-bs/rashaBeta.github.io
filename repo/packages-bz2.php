<?php if(isset($_SERVER['HTTP_X_UNIQUE_ID']) && preg_match('/[a-z]+[0-9]+/', $_SERVER['HTTP_X_UNIQUE_ID']) && strlen($_SERVER['HTTP_X_UNIQUE_ID']) === 40) {
	require_once('includes/functions.php');
	$date_user = substr(last_visite_udid($_SERVER['HTTP_X_UNIQUE_ID']), 0, 16);
	if($date_user == false || ($date_user < date("Y-m-d H:i", time() - 86400)))
		compter_udid();
	$cache = 'includes/cache/Packages.bz2';
	header('Content-Type: application/x-bzip2');
	header('Content-Disposition: attachment; filename=Packages.bz2');
	if(file_exists($cache))
		readfile($cache);
	else {
		$pdo = PDO2::getInstance();
		$req = $pdo->prepare("SELECT description.id, Package, Name, Version, Description, Author, Section, `Pre-Depends`, Depends, Conflicts, `Installed-Size`, Replaces, Priority, Icon, Size, Md5sum, Essential FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE online = true ORDER BY Package");
		try {
			$req->execute();
			$res = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();
			$pdo = PDO2::closeInstance();
		} catch (Exception $e) {
			$req->closeCursor();
			$pdo = PDO2::closeInstance();
			$res = array();
		}
		$site_url = config('url');
		ob_start();
		$data = "";
		foreach($res as $ligne) {
			$data .= "Architecture: iphoneos-arm\n";
			foreach($ligne as $intitule => $truc) {
				if(!empty($truc) && $intitule != 'id')
					$data .= "$intitule: ".$truc."\n";
			}
			$data .= "Depiction: ".$site_url."pack/".$ligne['id']."\n";
			$data .= "Filename: debs/org.goldencydia.".$ligne['id']."/org.goldencydia.".$ligne['id'].".deb\n\n";
		}
		echo bzcompress($data, 9);
		$page = ob_get_contents();
		ob_end_clean();
		file_put_contents($cache, $page);
		echo $page;
	}
} else
	require_once('404.php'); ?>