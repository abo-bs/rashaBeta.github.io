<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 4) {
	require_once('includes/time-header.php');
	translation();
	if(empty($_GET['id']) || !is_numeric($_GET['id']))
		header('Location: membres.php');
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT pseudo, password, mail, level, ip, date, last_date, newsletter FROM membre WHERE id = :id");
	$req->execute(array(':id' => $_GET['id']));
	$verif = $req->fetch(PDO::FETCH_ASSOC);
	$req->closeCursor();
	$req = $pdo->prepare("SELECT COUNT(id) FROM description_meta WHERE id_membre = :id");
	$req->execute(array(':id' => $_GET['id']));
	$paquets = $req->fetchColumn();
	$req->closeCursor();
	$req = $pdo->prepare("SELECT COUNT(id) FROM users WHERE membre = :id");
	$req->execute(array(':id' => $_GET['id']));
	$whitelist = $req->fetchColumn();
	$req->closeCursor();
	$date_user = $verif['date'];
	if(isset($_GET['year']) && is_numeric($_GET['year']) && $_GET['year'] >= date_format(date_create($date_user), 'Y') && $_GET['year'] <= date('Y', time()))
		$annee = preg_replace("/[^0-9]/", '', $_GET['year']);
	else
		$annee = date('Y', time());
	if(isset($_GET['month']) && is_numeric($_GET['month']) && (($_GET['month'] > 0 && $_GET['month'] <= 12 && $annee > date_format(date_create($date_user), 'Y') && $annee < date('Y', time())) || ($_GET['month'] < date('n', time()) && $annee == date('Y', time())) || ($annee == date_format(date_create($date_user), 'Y') && $_GET['month'] >= date_format(date_create($date_user), 'n'))))
		$mois = preg_replace("/[^0-9]/", '', $_GET['month']);
	else {
		$mois = date('n', time());
		$annee = date('Y', time());
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
	$req = $pdo->prepare('SELECT download.date, COUNT(download.id), COUNT(DISTINCT download.user) FROM download INNER JOIN description_meta ON download.package = description_meta.id WHERE id_membre = :id AND download.date LIKE :date GROUP BY DAY(download.date)');
	$req->execute(array(':id' => $_GET['id'], ':date' => $annee.'-'.$mois.'%'));
	$stats = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();
	if($verif && $_GET['connect'] == 'on') {
		$membre->delete();
		$membre->connexion($verif['pseudo'], $verif['password'], true, true);
		header('Location: /dpt-account.php');
		exit();
	}
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Informations about a member').' - '.$site_nom; ?></title>
	<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Informations about a member'); ?></h2>
		</div>
		<?php if($verif) { ?>
		<div role="tabpanel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center" role="tablist">
					<li role="statistics" class="active" style="float:none;display:inline-block;*display:inline;zoom:1;"><a href="#statistics" aria-controls="statistics" role="tab" data-toggle="tab">Statistiques</a></li>
					<li role="informations" style="float:none;display:inline-block;*display:inline;zoom:1;"><a href="#informations" aria-controls="informations" role="tab" data-toggle="tab">Informations</a></li>
				</ul>
				<div class="tab-content jumbotron">
					<div role="tabpanel" class="tab-pane active" id="statistics">
						<?php if(!empty($stats))
							echo '<div id="statistiques-graff" class="text-center"></div>';
						else
							echo '<div class="text-center lead">'._('Any data to display.').'</div>'; ?>
						<ul class="pager">
							<?php if($annee > date_format(date_create($date_user), 'Y') || ($moisprec >= date_format(date_create($date_user), 'n') && $annee == date_format(date_create($date_user), 'Y')))
								echo '<li class="previous"><a class="alignleft" href="?month='.$moisprec.'&year='.$anneeprec.'">'._('Previous month').'</a></li>';
							if($mois < date('n', time()) || $annee < date('Y', time()))
								echo '<li class="next"><a class="alignright" href="?month='.$moissuiv.'&year='.$anneesuiv.'">'._('Next month').'</a></li>'; ?>
						</ul>
					</div>
					<div role="tabpanel" class="tab-pane" id="informations">
						<table class="table">
							<tr>
								<td>Pseudo</td>
								<td><?php echo $verif['pseudo'];if($verif['prive'])echo ' <small>Privé</small>'; ?> <a href="?id=<?php echo $_GET['id']; ?>&connect=on" class="btn btn-success btn-xs"><?php echo _('Sign in'); ?></a></td>
							</tr>
							<tr>
								<td>Mail</td>
								<td><?php echo $verif['mail']; ?></td>
							</tr>
							<tr>
								<td>IP</td>
								<td><?php echo $verif['ip']; ?></td>
							</tr>
							<tr>
								<td>Niveau</td>
								<td><?php if($verif['level'] == 5) echo 'Administrateur'; elseif($verif['level'] === '4') echo 'Sous-Administrateur'; elseif($verif['level'] === '3') echo 'Gérant'; elseif($verif['level'] === '2') echo 'Uploadeur officiel'; elseif($verif['level'] === '1') echo 'Uploadeur'; else echo 'Membre'; ?></td>
							</tr>
							<tr>
								<td>Date d'inscription</td>
								<td><?php echo date_format(date_create($verif['date']), "d/m/Y - H:i"); ?></td>
							</tr>
							<tr>
								<td>Dernière visite</td>
								<td><?php echo date_format(date_create($verif['last_date']), "d/m/Y - H:i"); ?></td>
							</tr>
							<tr>
								<td>Newsletter</td>
								<td><?php echo !empty($verif['newsletter']) ? 'Oui' : 'Non'; ?></td>
							</tr>
							<tr>
								<td>Publicités</td>
								<td><?php echo !empty($verif['ads']) ? 'Oui' : 'Non'; ?></td>
							</tr>
							<tr>
								<td>Paquets uploadés</td>
								<td><a href="manage-all.php?champs=pseudo&s=<?php echo $verif['pseudo'].'">'.$paquets; ?></a></td>
							</tr>
							<tr>
								<td>Udid(s) autorisé(s)</td>
								<td><?php echo $whitelist; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<p class="text-center">Aucun membre n'a été trouvé !</p>
	<?php } ?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script src="js/raphael.min.js"></script>
	<script src="js/morris.min.js"></script>
	<script>new Morris.Area({element:'statistiques-graff',lineColors:['#f00','#000'],data:[<?php foreach($stats as $stat){$downloads = (!empty($stat['COUNT(download.id)'])) ? $stat['COUNT(download.id)'] : 0;$users=(!empty($stat['COUNT(DISTINCT download.user)'])) ? $stat['COUNT(DISTINCT download.user)'] : 0;$date=explode('-', $stat['date']);echo '{ day: \''.date('Y-m-d', mktime(0, 0, 0, $date['1'], $date['2'], $date['0'])).'\', downloads: '.$downloads.', users: '.$users.' },';} ?>],xLabels:'day',xkey:'day',ykeys:['downloads','users'],labels:['<?php echo _('Downloads'); ?>','<?php echo _('Users'); ?>'],behaveLikeLine:true});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>