<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 3) {
	require_once('includes/time-header.php');
	translation();
	if(empty($_GET['id']) || !is_numeric($_GET['id'])) {
		header('Location: users.php');
		exit;
	}
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT udid, device, firmware, users.ip, users.date, date_update, banni, pseudo, membre.id AS membreId, membre.ip AS ipmembre FROM users LEFT JOIN membre ON membre.id = users.membre WHERE users.id = :id");
	$req->execute(array(':id' => $_GET['id']));
	$verif = $req->fetch(PDO::FETCH_ASSOC);
	$req->closeCursor();

	$etat = ($verif['banni']) ? _('Yes') : _('No');
	$req = $pdo->prepare("SELECT COUNT(id) FROM download WHERE user = :id");
	$req->execute(array(':id' => $_GET['id']));
	$download = $req->fetchColumn();
	$req->closeCursor();

	if($download) {
		$req = $pdo->prepare('SELECT DISTINCT package FROM download WHERE user = :id AND package IS NOT NULL ORDER BY date DESC');
		$req->execute(array(':id' => $_GET['id']));
		$paquets = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
	}
	if($download) {
		$data = '';
		foreach($paquets as $paquet) {
			$data .= '<tr colspan="3">';
			$infos_paquet = $pdo->prepare('SELECT id, Name FROM description WHERE id = :id');
			$infos_paquet->execute(array(':id' => $paquet['package']));
			$infos_paquet = $infos_paquet->fetch();
			$download_paquet = $pdo->prepare("SELECT COUNT(id) FROM download WHERE user = :udid AND package = :id");
			$download_paquet->execute(array(':udid' => $_GET['id'], ':id' => $paquet['package']));
			$download_paquet = $download_paquet->fetchColumn();
			$version_paquets = $pdo->prepare('SELECT DISTINCT version FROM download WHERE user = :id AND package = :paquet ORDER BY date DESC');
			$version_paquets->execute(array(':id' => $_GET['id'], ':paquet' => $paquet['package']));
			$version_paquets = $version_paquets->fetchAll(PDO::FETCH_ASSOC);
			$data .= '<td  colspan="1" rowspan="'.$download_paquet.'"><a href="/stats-paquet.php?id='.$infos_paquet['id'].'">'.$infos_paquet['Name'].'</a> <small>'._('Downloaded').' '.$download_paquet.' '._('times').'</small></td>';
			foreach($version_paquets as $version_paquet) {
				$date_paquets = $pdo->prepare('SELECT date FROM download WHERE user = :id AND package = :paquet AND version = :version ORDER BY date DESC');
				$date_paquets->execute(array(':id' => $_GET['id'], ':paquet' => $paquet['package'], ':version' => $version_paquet['version']));
				$date_count = $date_paquets->rowCount();
				$date_paquets = $date_paquets->fetchAll(PDO::FETCH_ASSOC);
				$data .= '<td  colspan="1" rowspan="'.$date_count.'"><b>'.$version_paquet['version'].'</b> <small>'._('Downloaded').' '.$date_count.' '._('times').'</small></td>';
				foreach($date_paquets as $date_paquet) {
					$data .= '<td  colspan="1">'.$date_paquet['date'].'</td></tr>';
				}
			}
		}
	}
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('UDID statistics').' - '.$site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('UDID statistics'); ?></h2>
		</div>
		<?php if($verif) { ?>
			<div class="jumbotron form-horizontal">
				<p class="text-center">UDID : <?php echo $verif['udid']; ?></p>
				<?php if(!empty($verif['pseudo']))
					echo '<p class="text-center">'._('Member').' : <a href="membre.php?id='.$verif['membreId'].'">'.$verif['pseudo'].'</a></p>';
				if($membre->_level > 4)
					echo '<p class="text-center">'._('Banned').' : '.$etat.'</p>';
				if($membre->_level > 4 && !empty($verif['ip']))
					echo '<p class="text-center">IP device : '.$verif['ip'].'</p>';
				if($membre->_level > 4 && !empty($verif['ipmembre']))
					echo '<p class="text-center">IP Membre : '.$verif['ipmembre'].'</p>'; ?>
				<p class="text-center"><?php echo _('Device'); ?> : <?php echo $verif['device']; ?></p>
				<p class="text-center">Firmware : <?php echo $verif['firmware']; ?></p>
				<p class="text-center"><?php echo _('First visit'); ?> : <?php echo date_format(date_create($verif['date']), "d/m/Y - H:i"); ?></p>
				<p class="text-center"><?php echo _('Last visit'); ?> : <?php echo date_format(date_create($verif['date_update']), "d/m/Y - H:i"); ?></p>
				<p class="text-center"><?php echo _('Total purchases'); ?> : <?php echo $purchase; ?></p>
				<p class="text-center"><?php echo _('Total downloads'); ?> : <?php echo $download; ?></p>
				<?php if($download) { ?>
					<p class="text-center"><?php echo _('Packages downloaded'); ?> : <?php echo count($paquets); ?></p>
					<table class="text-center table table-condensed table-hover table-bordered" colspan="3">
					<tr colspan="3">
						<th class="text-center"><?php echo _('Package'); ?></th>
						<th class="text-center"><?php echo _('Version'); ?></th>
						<th class="text-center"><?php echo _('Date'); ?></th>
					</tr>
					<?php echo $data; ?>
					</table>
				<?php } ?>
			</div>
		<?php } else { ?>
			<div class="jumbotron">
				<p class="text-center"><?php echo _('UDID not found !'); ?></p>
			</div>
		<?php }?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>