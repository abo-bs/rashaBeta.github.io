<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	translation();
	$pdo = PDO2::getInstance();
	if(isset($_GET['peryear'])) {
		if(isset($_GET['year']) && $_GET['year'] >= 2014 && $_GET['year'] <= date('Y', time()))
			$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
		else
			$annee = date('Y', time());
		if($annee > 2014)
			$anneeprec = $annee - 1;
		else
			$anneeprec = $annee;

		if($annee < date('Y', time()))
			$anneesuiv = $annee + 1;
		else
			$anneesuiv = $annee;
		set_time_limit(0);
		ini_set('memory_limit', -1);
		$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user), COUNT(DISTINCT package) FROM download WHERE date LIKE :date GROUP BY MONTH(date) ORDER BY date ASC');
		$req->execute(array(':date' => $annee.'%'));
		$res = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		$data = '';
		foreach($res as $ligne){
			$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
			$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
			$packages = (!empty($ligne['COUNT(DISTINCT package)'])) ? $ligne['COUNT(DISTINCT package)'] : 0;
			$data .= '{ month: \''.$ligne['date'].'\', telechargement: '.$downloads.', utilisateur: '.$users.', paquet: '.$packages.' },';
		}
	} elseif(isset($_GET['permonth'])) {
		if(isset($_GET['year']) && $_GET['year'] >= 2014 && $_GET['year'] <= date('Y', time()))
			$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
		else
			$annee = date('Y', time());
		if(isset($_GET['month']) && (($_GET['month'] > 0 && $_GET['month'] <= 12 && $annee > 2014 && $annee < date('Y', time())) || ($_GET['month'] < date('n', time()) && $annee == date('Y', time())) || ($annee == 2014 && $_GET['month'] > 2)))
			$mois = preg_replace("/[^0-9]/", '', $_GET['month']);
		else {
			$mois = date('n', time());
			$annee = date('Y', time());
		}
		switch($mois) {
			case 1: $mois_affichage = 'Janvier';break;
			case 2: $mois_affichage = 'Février';break;
			case 3: $mois_affichage = 'Mars';break;
			case 4: $mois_affichage = 'Avril';break;
			case 5: $mois_affichage = 'Mai';break;
			case 6: $mois_affichage = 'Juin';break;
			case 7: $mois_affichage = 'Juillet';break;
			case 8: $mois_affichage = 'Août';break;
			case 9: $mois_affichage = 'Septembre';break;
			case 10: $mois_affichage = 'Octobre';break;
			case 11: $mois_affichage = 'Novembre';break;
			case 12: $mois_affichage = 'Décembre';break;
			default: $mois_affichage = 'Bug';break;
		}
		if($annee > 2014 && $mois == 1)
			$anneeprec = $annee - 1;
		else
			$anneeprec = $annee;

		if($annee < date('Y', time()) && $mois == 12)
			$anneesuiv = $annee + 1;
		else
			$anneesuiv = $annee;
		if($mois == 1 && $annee > 2014)
			$moisprec = 12;
		else
			$moisprec = $mois - 1;
		if($mois >= 12)
			$moissuiv = 1;
		else
			$moissuiv = $mois + 1;
		switch($mois) {
			case 1: $mois = '01';break;
			case 2: $mois = '02';break;
			case 3: $mois = '03';break;
			case 4: $mois = '04';break;
			case 5: $mois = '05';break;
			case 6: $mois = '06';break;
			case 7: $mois = '07';break;
			case 8: $mois = '08';break;
			case 9: $mois = '09';break;
			case 10: $mois = '10';break;
			case 11: $mois = '11';break;
			case 12: $mois = '12';break;
			default: $mois = date('m', time());break;
		}
		$jours = date('t',mktime(0, 0, 0, $mois, 1, $annee));
		$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user), COUNT(DISTINCT package) FROM download WHERE date LIKE :date GROUP BY DAY(date) ORDER BY date ASC');
		$req->execute(array(':date' => $annee.'-'.$mois.'%'));
		$res = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		$data = '';
		foreach($res as $ligne){
			$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
			$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
			$packages = (!empty($ligne['COUNT(DISTINCT package)'])) ? $ligne['COUNT(DISTINCT package)'] : 0;
			$data .= '{ day: \''.$ligne['date'].'\', telechargement: '.$downloads.', utilisateur: '.$users.', paquet: '.$packages.' },';
		}
	} else {
		$i=0;
		if(isset($_GET['year']) && $_GET['year'] >= 2014 && $_GET['year'] <= date('Y', time()))
			$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
		else
			$annee = date('Y', time());
		if(isset($_GET['month']) && (($_GET['month'] > 0 && $_GET['month'] <= 12 && $annee > 2014 && $annee < date('Y', time())) || ($_GET['month'] < date('n', time()) && $annee == date('Y', time())) || ($annee == 2014 && $_GET['month'] > 2)))
			$mois = preg_replace("/[^0-9]/", '', $_GET['month']);
		else {
			$mois = date('n', time());
			$annee = date('Y', time());
		}
		if(isset($_GET['day']) && (($_GET['day'] > 3 && $_GET['day'] <= date('t',mktime(0, 0, 0, 3, 1, 2014)) && $mois == 3 && $annee == 2014) || ($_GET['day'] > 0 && $_GET['day'] <= date('j', time()) && $mois == date('n', time()) && $annee == date('Y', time())) || ($_GET['day'] > 0 && $_GET['day'] <= date('t',mktime(0, 0, 0, $mois, 1, $annee)))))
			$jour = preg_replace("/[^0-9]/", '', $_GET['day']);
		else {
			$jour = date('j', time());
			$mois = date('n', time());
			$annee = date('Y', time());
		}
		if($annee > 2014 && $mois == 1 && $jour == 1)
			$anneeprec = $annee - 1;
		else
			$anneeprec = $annee;
		if($annee < date('Y', time()) && $mois == 12)
			$anneesuiv = $annee + 1;
		else
			$anneesuiv = $annee;
		if($jour == 1) {
			if($mois <= 1)
				$moisprec = 12;
			else
				$moisprec = $mois - 1;
			$jourprec = date('t',mktime(0, 0, 0, $moisprec, 1, $anneeprec));
		} else {
			$jourprec = $jour - 1;
			$moisprec = $mois;
		}
		if($jour < date('t',mktime(0, 0, 0, $mois, 1, $annee))) {
			$moissuiv = $mois;
			$joursuiv = $jour + 1;
		} else {
			$joursuiv = 1;
			if($mois >= 12)
				$moissuiv = 1;
			else
				$moissuiv = $mois + 1;
		}
		switch($mois) {
			case 1: $mois_affichage = 'Janvier';break;
			case 2: $mois_affichage = 'Février';break;
			case 3: $mois_affichage = 'Mars';break;
			case 4: $mois_affichage = 'Avril';break;
			case 5: $mois_affichage = 'Mai';break;
			case 6: $mois_affichage = 'Juin';break;
			case 7: $mois_affichage = 'Juillet';break;
			case 8: $mois_affichage = 'Août';break;
			case 9: $mois_affichage = 'Septembre';break;
			case 10: $mois_affichage = 'Octobre';break;
			case 11: $mois_affichage = 'Novembre';break;
			case 12: $mois_affichage = 'Décembre';break;
			default: $mois_affichage = 'Bug';break;
		}
		switch($mois) {
			case 1: $mois = '01';break;
			case 2: $mois = '02';break;
			case 3: $mois = '03';break;
			case 4: $mois = '04';break;
			case 5: $mois = '05';break;
			case 6: $mois = '06';break;
			case 7: $mois = '07';break;
			case 8: $mois = '08';break;
			case 9: $mois = '09';break;
			case 10: $mois = '10';break;
			case 11: $mois = '11';break;
			case 12: $mois = '12';break;
			default: $mois = date(m);break;
		}
		switch($jour) {
			case 1: $jour = '01';break;
			case 2: $jour = '02';break;
			case 3: $jour = '03';break;
			case 4: $jour = '04';break;
			case 5: $jour = '05';break;
			case 6: $jour = '06';break;
			case 7: $jour = '07';break;
			case 8: $jour = '08';break;
			case 9: $jour = '09';break;
			case 10: $jour = '10';break;
			case 11: $jour = '11';break;
			case 12: $jour = '12';break;
			case 13: $jour = '13';break;
			case 14: $jour = '14';break;
			case 15: $jour = '15';break;
			case 16: $jour = '16';break;
			case 17: $jour = '17';break;
			case 18: $jour = '18';break;
			case 19: $jour = '19';break;
			case 20: $jour = '20';break;
			case 21: $jour = '21';break;
			case 22: $jour = '22';break;
			case 23: $jour = '23';break;
			case 24: $jour = '24';break;
			case 25: $jour = '25';break;
			case 26: $jour = '26';break;
			case 27: $jour = '27';break;
			case 28: $jour = '28';break;
			case 29: $jour = '29';break;
			case 30: $jour = '30';break;
			case 31: $jour = '31';break;
			default: $jour = date(j);break;
		}
		$jours = date('t',mktime(0, 0, 0, $mois, 1, $annee));
		if($jour > $jours)
			$jour = $jours;
		$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user), COUNT(DISTINCT package) FROM download WHERE date LIKE :date GROUP BY HOUR(date) ORDER BY date ASC');
		$req->execute(array(':date' => $annee.'-'.$mois.'-'.$jour.'%'));
		$res = $req->fetchAll(PDO::FETCH_ASSOC);
		$data = '';
		foreach($res as $ligne){
			$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
			$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
			$packages = (!empty($ligne['COUNT(DISTINCT package)'])) ? $ligne['COUNT(DISTINCT package)'] : 0;
			$data .= '{ hour: \''.date_format(date_create($ligne['date']), "d-m-Y H:i").'\', telechargement: '.$downloads.', utilisateur: '.$users.', paquet: '.$packages.' },';
		}
	}
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Downloads').' ';if(isset($_GET['peryear']))echo strtolower(_('Per year'));elseif(isset($_GET['permonth']))echo strtolower(_('Per month'));else echo strtolower(_('Per day')); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Downloads').' ';if(isset($_GET['peryear']))echo strtolower(_('Per year')).' : '.$annee;elseif(isset($_GET['permonth']))echo strtolower(_('Per month')).' : '.$mois_affichage.' '.$annee;else echo strtolower(_('Per day')).' : '.$jour.' '.$mois_affichage.' '.$annee; ?></h2>
		</div>
		<div class="tabbable-panel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center hidden-xs hidden-sm hidden-md">
					<li>
						<a href="stats-admin.php"><?php echo _('Users'); ?></a>
					</li>
					<li>
						<a href="stats-admin-membres.php"><?php echo _('Members'); ?></a>
					</li>
					<li class="active">
						<a href="stats-download.php"><?php echo _('Downloads'); ?></a>
					</li>
					<li>
						<a href="stats-firmwares.php"><?php echo _('Versions'); ?></a>
					</li>
					<li>
						<a href="stats-devices.php"><?php echo _('Devices'); ?></a>
					</li>
				</ul>
				<div class="tab-content jumbotron">
					<div class="tab-pane active">
						<div class="btn-group btn-group-justified hidden-lg hidden-xl" role="group">
							<div class="btn-group" role="group">
								<a href="stats-admin.php" class="btn btn-default"><?php echo _('Users'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-admin-membres.php" class="btn btn-default"><?php echo _('Members'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-download.php" class="btn btn-default active"><?php echo _('Downloads'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-firmwares.php" class="btn btn-default"><?php echo _('Versions'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-devices.php" class="btn btn-default"><?php echo _('Devices'); ?></a>
							</div>
						</div>
						<div class="btn-group btn-group-justified" role="group">
							<div class="btn-group" role="group">
								<a href="stats-download.php" class="btn btn-default<?php if(!isset($_GET['peryear']) && !isset($_GET['permonth'])) echo ' active'; ?>"><?php echo _('Per day'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-download.php?permonth" class="btn btn-default<?php if(isset($_GET['permonth']) && !isset($_GET['peryear'])) echo ' active'; ?>"><?php echo _('Per month'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-download.php?peryear" class="btn btn-default<?php if(isset($_GET['peryear'])) echo ' active'; ?>"><?php echo _('Per year'); ?></a>
							</div>
						</div>
						<div id="stats_day" class="text-center"></div>
						<?php if(isset($_GET['peryear'])) { ?>
						<ul class="pager">
						<?php if($annee > 2014)
							echo '<li class="previous"><a class="alignleft" href="?year='.$anneeprec.'&peryear">'._('Previous year').'</a></li>';
						if($annee < date('Y', time()))
							echo '<li class="next"><a class="alignright" href="?year='.$anneesuiv.'&peryear">'._('Next year').'</a></li>'; ?>
						</ul>
						<?php } elseif(isset($_GET['permonth'])) { ?>
						<ul class="pager">
						<?php if($annee > 2014 || ($moisprec > 2 && $annee == 2014))
							echo '<li class="previous"><a class="alignleft" href="?month='.$moisprec.'&year='.$anneeprec.'&permonth">'._('Previous month').'</a></li>';
						if($mois < date('n', time()) || $annee < date('Y', time()))
							echo '<li class="next"><a class="alignright" href="?month='.$moissuiv.'&year='.$anneesuiv.'&permonth">'._('Next month').'</a></li>'; ?>
						</ul>
						<?php } else { ?>
						<ul class="pager">
						<?php if($annee == 2014 && $mois == 3 && $jour <= 3) {} else {
							echo '<li class="previous"><a class="alignleft" href="?year='.$anneeprec.'&month='.$moisprec.'&day='.$jourprec.'">'._('Previous day').'</a></li>';
						}
						if($annee == date('Y', time()) && $mois == date('m', time()) && $jour >= date('j', time())) {} else
							echo '<li class="next"><a class="alignright" href="?year='.$anneesuiv.'&month='.$moissuiv.'&day='.$joursuiv.'">'._('Next day').'</a></li>'; ?>
						</ul>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script src="/js/raphael.min.js"></script>
	<script src="/js/morris.min.js"></script>
	<script>new Morris.Area({element:'stats_day',lineColors:['#f00','#000','#00f'],data:[<?php echo $data; ?>],<?php if(isset($_GET['peryear'])) echo "xLabels:'month',";elseif(isset($_GET['permonth'])) echo "xLabels:'day',"; ?>xkey:'<?php if(isset($_GET['peryear'])) echo 'month';elseif(isset($_GET['permonth'])) echo 'day';else echo 'hour'; ?>',ykeys:['telechargement','utilisateur','paquet'],labels:['<?php echo _('Downloads'); ?>','<?php echo _('Users'); ?>','<?php echo _('Packages'); ?>'],behaveLikeLine:true});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>