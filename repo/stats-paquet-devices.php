<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	if(empty($_GET['id']))
		header('Location: /manage.php');
	$pdo = PDO2::getInstance();
	$verif = $pdo->prepare("SELECT name, date FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE description.id = :id");
	$verif->execute(array(':id' => $_GET['id']));
	$verif = $verif->fetch(PDO::FETCH_ASSOC);
	translation();
	$site_nom = config('nom');
	if($verif) {
		$devices = $pdo->prepare('SELECT device, COUNT(udid) AS udid FROM users INNER JOIN download ON download.user = users.id WHERE download.package = :id GROUP BY device ORDER BY device ASC');
		$devices->execute(array(':id' => $_GET['id']));
		$devices = $devices->fetchAll(PDO::FETCH_ASSOC);
		$data = '';
		foreach($devices as $device){
			if(!empty($device['device']))
				$data .= "{ y: '".$device['device']."', x: ".$device['udid']."},";
			else
				$data .= "{ y: '"._('Unknow')."', x: ".$device['udid']."},";
		}
	}
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
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
					<li>
						<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>"><?php echo _('Versions'); ?></a>
					</li>
					<li class="active">
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
								<a href="stats-paquet.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Versions'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-devices.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default active"><?php echo _('Devices'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-firmwares.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default">Firmwares</a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-paquet-date.php?id=<?php echo $_GET['id']; ?>" class="btn btn-default"><?php echo _('Date'); ?></a>
							</div>
						</div>
						<p class="text-center"><?php echo _('Package'); ?> : <?php echo $verif['name']; ?></p>
						<div id="stats_device" class="text-center"></div>
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
	<script>Morris.Bar({element: 'stats_device',barColors:['#f00'],data: [<?php echo $data; ?>], xkey: 'y', ykeys: ['x'], labels: ['<?php echo _('Devices'); ?>']});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>