<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	translation();
	if(empty($_GET['id']))
		header('Location: /manage.php');
	$pdo = PDO2::getInstance();
	$verif = $pdo->prepare("SELECT name, date FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE description.id = :id");
	$verif->execute(array(':id' => $_GET['id']));
	$verif = $verif->fetch(PDO::FETCH_ASSOC);
	if($verif) {
		if(isset($_GET['peryear'])) {
			if(isset($_GET['year']) && $_GET['year'] >= date('Y', strtotime($verif['date'])) && $_GET['year'] <= date('Y', time()))
				$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
			else
				$annee = date('Y', time());
			if($annee > date('Y', strtotime($verif['date'])))
				$anneeprec = $annee - 1;
			else
				$anneeprec = $annee;
			if($annee < date('Y', time()))
				$anneesuiv = $annee + 1;
			else
				$anneesuiv = $annee;
			$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user) FROM download WHERE date LIKE :date AND package = :id GROUP BY MONTH(date) ORDER BY date ASC');
			$req->execute(array(':date' => $annee.'-'.$mois.'%', ':id' => $_GET['id']));
			$res = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();
			$data = '';
			foreach($res as $ligne){
				$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
				$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
				$data .= '{ month: \''.$ligne['date'].'\', telechargement: '.$downloads.', utilisateur: '.$users.' },';
			}
		} elseif(isset($_GET['permonth'])) {
			if(isset($_GET['year']) && $_GET['year'] >= date('Y', strtotime($verif['date'])) && $_GET['year'] <= date('Y', time()))
				$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
			else
				$annee = date('Y', time());
			if(isset($_GET['month']) && (($_GET['month'] > 0 && $_GET['month'] <= 12 && $annee > date('Y', strtotime($verif['date'])) && $annee <= date('Y', time())) || ($annee == date('Y', strtotime($verif['date'])) && $_GET['month'] > date('n', strtotime($verif['date'])))))
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
			if($annee > date('Y', strtotime($verif['date'])) && $mois == 1)
				$anneeprec = $annee - 1;
			else
				$anneeprec = $annee;
			if($annee < date('Y', time()) && $mois == 12)
				$anneesuiv = $annee + 1;
			else
				$anneesuiv = $annee;
			if($mois == 1 && $annee > date('Y', strtotime($verif['date'])))
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
			$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user) FROM download WHERE date LIKE :date AND package = :id GROUP BY DAY(date) ORDER BY date ASC');
			$req->execute(array(':date' => $annee.'-'.$mois.'%', ':id' => $_GET['id']));
			$res = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();
			$data = '';
			foreach($res as $ligne){
				$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
				$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
				$data .= '{ day: \''.$ligne['date'].'\', telechargement: '.$downloads.', utilisateur: '.$users.'},';
			}
		} else {
			$i=0;
			if(isset($_GET['year']) && $_GET['year'] >= date('Y', strtotime($verif['date'])) && $_GET['year'] <= date('Y', time()))
				$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
			else
				$annee = date('Y', time());
			if(isset($_GET['month']) && (($_GET['month'] > 0 && $_GET['month'] <= 12 && $annee > date('Y', strtotime($verif['date'])) && $annee < date('Y', time())) || ($_GET['month'] < date('n', time()) && $annee == date('Y', time())) || ($annee == date('Y', strtotime($verif['date'])) && $_GET['month'] > date('n', strtotime($verif['date'])))))
				$mois = preg_replace("/[^0-9]/", '', $_GET['month']);
			else {
				$mois = date('n', time());
				$annee = date('Y', time());
			}
			if(isset($_GET['day']) && (($_GET['day'] > date('t', strtotime($verif['date'])) && $_GET['day'] <= date('t', strtotime($verif['date'])) && $mois == date('n', strtotime($verif['date'])) && $annee == date('Y', strtotime($verif['date']))) || ($_GET['day'] > 0 && $_GET['day'] <= date('j', time()) && $mois == date('n', time()) && $annee == date('Y', time())) || ($_GET['day'] > 0 && $_GET['day'] <= date('t',mktime(0, 0, 0, $mois, 1, $annee)))))
				$jour = preg_replace("/[^0-9]/", '', $_GET['day']);
			else {
				$jour = date('j', time());
				$mois = date('n', time());
				$annee = date('Y', time());
			}
			if($annee > date('Y', strtotime($verif['date'])) && $mois == 1 && $jour == 1)
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
			$req = $pdo->prepare('SELECT date, COUNT(id), COUNT(DISTINCT user) FROM download WHERE date LIKE :date AND package = :id GROUP BY HOUR(date) ORDER BY date ASC');
			$req->execute(array(':date' => $annee.'-'.$mois.'-'.$jour.'%', ':id' => $_GET['id']));
			$res = $req->fetchAll(PDO::FETCH_ASSOC);
			$data = '';
			foreach($res as $ligne){
				$downloads = (!empty($ligne['COUNT(id)'])) ? $ligne['COUNT(id)'] : 0;
				$users = (!empty($ligne['COUNT(DISTINCT user)'])) ? $ligne['COUNT(DISTINCT user)'] : 0;
				$data .= '{ hour: \''.date_format(date_create($ligne['date']), "d-m-Y H:i").'\', telechargement: '.$downloads.', utilisateur: '.$users.' },';
			}
		}
	}
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html>
<head>
	<title><?php echo _('Package statistics').' ';if(isset($_GET['peryear']))echo strtolower(_('Per year'));elseif(isset($_GET['permonth']))echo strtolower(_('Per month'));else echo strtolower(_('Per day')); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Package statistics').' ';if(isset($_GET['peryear']))echo strtolower(_('Per year')).' : '.$annee;elseif(isset($_GET['permonth']))echo strtolower(_('Per month')).' : '.$mois_affichage.' '.$annee;else echo strtolower(_('Per day')).' : '.$jour.' '.$mois_affichage.' '.$annee; ?></h2>
		</div>
		<?php if($verif) { ?>
		<div class="tabbable-panel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center hidden-xs hidden-sm hidden-md">
					<li>
						<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Versions'); ?></a>
					</li>
					<li>
						<a href="stats-paquet-devices.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Devices'); ?></a>
					</li>
					<li>
						<a href="stats-paquet-firmwares.php?id=<?php echo $_GET['id']; ?>">Firmwares</a>
					</li>
					<li class="active">
						<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Date'); ?></a>
					</li>
				</ul>
				<div class="tab-content jumbotron">
					<div class="tab-pane active">
						<div class="btn-group btn-group-justified hidden-lg hidden-xl" role="group">
							<div class="btn-group" role="group">
								<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Versions'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-devices.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Devices'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-firmwares.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default">Firmwares</a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default active"><?php echo _('Date'); ?></a>
							</div>
						</div>
						<div class="btn-group btn-group-justified" role="group">
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default<?php if(!isset($_GET['peryear']) && !isset($_GET['permonth'])) echo ' active'; ?>"><?php echo _('Per day'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>&permonth" class="btn btn-default<?php if(isset($_GET['permonth']) && !isset($_GET['peryear'])) echo ' active'; ?>"><?php echo _('Per month'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>&peryear" class="btn btn-default<?php if(isset($_GET['peryear'])) echo ' active'; ?>"><?php echo _('Per year'); ?></a>
							</div>
						</div>
						<p class="text-center"><?php echo _('Package'); ?> : <?php echo $verif['name']; ?></p>
						<div id="stats_day" class="text-center"></div>
						<?php if(isset($_GET['peryear'])) { ?>
						<ul class="pager">
						<?php if($annee > date('Y', strtotime($verif['date'])))
							echo '<li class="previous"><a class="alignleft" href="?id='.$_GET['id'].'&year='.$anneeprec.'&peryear">'._('Previous year').'</a></li>';
						if($annee < date('Y', time()))
							echo '<li class="next"><a class="alignright" href="?id='.$_GET['id'].'&year='.$anneesuiv.'&peryear">'._('Next year').'</a></li>'; ?>
						</ul>
						<?php } elseif(isset($_GET['permonth'])) { ?>
						<ul class="pager">
						<?php if($annee > date('Y', strtotime($verif['date'])) || ($mois > date('n', strtotime($verif['date'])) && $annee == date('Y', strtotime($verif['date']))))
							echo '<li class="previous"><a class="alignleft" href="?id='.$_GET['id'].'&month='.$moisprec.'&year='.$anneeprec.'&permonth">'._('Previous month').'</a></li>';
						if($mois < date('n', time()) || $annee < date('Y', time()))
							echo '<li class="next"><a class="alignright" href="?id='.$_GET['id'].'&month='.$moissuiv.'&year='.$anneesuiv.'&permonth">'._('Next month').'</a></li>'; ?>
						</ul>
						<?php } else { ?>
						<ul class="pager">
						<?php if($annee == date('Y', strtotime($verif['date'])) && $mois == date('n', strtotime($verif['date'])) && $jour <= date('n', strtotime($verif['date']))) {} else {
							echo '<li class="previous"><a class="alignleft" href="?id='.$_GET['id'].'&year='.$anneeprec.'&month='.$moisprec.'&day='.$jourprec.'">'._('Previous day').'</a></li>';
						}
						if($annee == date('Y', time()) && $mois == date('m', time()) && $jour >= date('j', time())) {} else
							echo '<li class="next"><a class="alignright" href="?id='.$_GET['id'].'&year='.$anneesuiv.'&month='.$moissuiv.'&day='.$joursuiv.'">'._('Next day').'</a></li>'; ?>
						</ul>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php } else { ?>
			<div class="jumbotron">
				<p class="text-center"><?php echo _('Package not found'); ?></p>
			</div>
		<?php }?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script src="/js/raphael.min.js"></script>
	<script src="/js/morris.min.js"></script>
	<script>new Morris.Area({element:'stats_day',lineColors:['#f00','#000'],data:[<?php echo substr($data, 0, -1); ?>],<?php if(isset($_GET['peryear'])) echo "xLabels:'month',";elseif(isset($_GET['permonth'])) echo "xLabels:'day',"; ?>xkey:'<?php if(isset($_GET['peryear'])) echo 'month';elseif(isset($_GET['permonth'])) echo 'day';else echo 'hour'; ?>',ykeys:['telechargement','utilisateur'],labels:['<?php echo _('Downloads'); ?>','<?php echo _('Users'); ?>'],behaveLikeLine:true});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>