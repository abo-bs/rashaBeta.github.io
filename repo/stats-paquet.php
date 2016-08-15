<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	translation();
	if(empty($_GET['id']))
		header('Location: /manage.php');
	$pdo = PDO2::getInstance();
	$verif = $pdo->prepare("SELECT Name, Version, date, date_update, visits FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE description.id = :id");
	$verif->execute(array(':id' => $_GET['id']));
	$verif = $verif->fetch(PDO::FETCH_ASSOC);
	$versions = $pdo->prepare('SELECT version, COUNT(id) AS total, COUNT(user) AS users FROM download WHERE package = :id GROUP BY version ORDER BY date DESC');
	$versions->execute(array(':id' => $_GET['id']));
	$versions = $versions->fetchAll(PDO::FETCH_ASSOC);
	if($membre->_level > 4 && isset($_GET['debug'])) {
		$users = $pdo->prepare('SELECT users.udid, users.id, COUNT(download.id) AS total FROM download INNER JOIN users ON download.user = users.id WHERE download.package = :id GROUP BY users.id ORDER BY total DESC');
		$users->execute(array(':id' => $_GET['id']));
		$users = $users->fetchAll(PDO::FETCH_ASSOC);
		$data2 = '';
		foreach($users as $user) {
			$data2 .= '<p class="text-center">'.$user['udid'].' - '.$user['total'].'</p>';
		}
	}
	$data = '';
	$download = 0;
	foreach($versions as $version) {
		$download += $version['total'];
		$data .= '{ w: \''.$version['version'].'\', x: '.$version['total'].', y: '.$version['users'].'},';
	}
	$pdo = PDO2::closeInstance();
	$site_nom = config('nom');
	$jours = ceil((time() - strtotime($verif['date'])) / 86400);
	if($jours == 0)
		$jours = 1; ?>
<!doctype html>
<html>
<head>
	<title><?php echo _('Package statistics'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Package statistics'); ?></h2>
		</div>
		<?php if($verif) { ?>
		<div class="tabbable-panel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center hidden-xs hidden-sm hidden-md">
					<li class="active">
						<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Versions'); ?></a>
					</li>
					<li>
						<a href="stats-paquet-devices.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Devices'); ?></a>
					</li>
					<li>
						<a href="stats-paquet-firmwares.php?id=<?php echo $_GET['id']; ?>">Firmwares</a>
					</li>
					<li>
						<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Date'); ?></a>
					</li>
				</ul>
				<div class="tab-content jumbotron">
					<div class="tab-pane active">
						<div class="btn-group btn-group-justified hidden-lg hidden-xl" role="group">
							<div class="btn-group" role="group">
								<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default active"><?php echo _('Versions'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-devices.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Devices'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-firmwares.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default">Firmwares</a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Date'); ?></a>
							</div>
						</div>
						<p class="text-center"><?php echo _('Package'); ?> : <?php echo $verif['Name']; ?></p>
						<p class="text-center"><?php echo _('Version'); ?> : <?php echo $verif['Version']; ?></p>
						<p class="text-center"><?php echo _('Added'); ?> : <?php echo date_format(date_create($verif['date']), "d/m/Y - H:i"); ?></p>
						<?php if(date_format(date_create($verif['date']), "d/m/Y - H:i") != date_format(date_create($verif['date_update']), "d/m/Y - H:i"))
							echo '<p class="text-center">'._('Updated').' : '.date_format(date_create($verif['date_update']), "d/m/Y - H:i").'</p>'; ?>
						<p class="text-center"><?php echo _('Total visits'); ?> : <?php echo $verif['visits']; ?></p>
						<?php if($membre->_level > 4 && !isset($_GET['debug'])) echo '<p class="text-center"><a href="?id='.$_GET['id'].'&debug">Afficher les détails</a></p>'; ?>
						<?php echo $data2; ?>
						<div id="stats_paquet"></div>
						<ul class="list-inline text-center">
							<li><span class="glyphicon glyphicon-download"></span> <?php echo $download; ?></li>
							<li><span class="glyphicon glyphicon-time"></span> <?php echo print_timeago($verif['date']); ?></li>
							<li><span class="glyphicon glyphicon-stats"></span> <?php echo '~'.ceil($download / $jours).' '._('Per day'); ?></li>
						</ul>
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
	<script>Morris.Bar({element:'stats_paquet',data:[<?php echo $data; ?>],xkey:'w',ykeys:['x','y'],labels:['<?php echo _('Downloaded'); ?>','<?php echo _('Users'); ?>']});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>