<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	translation();
	$pdo = PDO2::getInstance();
	$errors = array();
	$success = array();
	$site_nom = config('nom');
	$devices = $pdo->prepare('SELECT device, COUNT(udid) AS udid FROM users GROUP BY device ORDER BY device ASC');
	$devices->execute();
	$devices = $devices->fetchAll(PDO::FETCH_ASSOC);
	$data = '';
	foreach($devices as $device){
		if(!empty($device['device']))
			$data .= "{ y: '".$device['device']."', x: ".$device['udid']."},";
	}
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Devices'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Devices'); ?></h2>
		</div>
		<?php if(!empty($errors)) {
			echo '<div class="alert alert-danger alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
			foreach($errors as $error) {
				echo '<p>'.$error.'</p>';
			}
			echo '</div>';
		}
		if(!empty($success)) {
			echo '<div class="alert alert-success alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
			foreach($success as $succes){
				echo '<p>'.$succes.'</p>';
			}
			echo '</div>';
		} ?>
		<div class="tabbable-panel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center hidden-xs hidden-sm hidden-md">
					<li>
						<a href="stats-admin.php"><?php echo _('Users'); ?></a>
					</li>
					<li>
						<a href="stats-admin-membres.php"><?php echo _('Members'); ?></a>
					</li>
					<li>
						<a href="stats-download.php"><?php echo _('Downloads'); ?></a>
					</li>
					<li>
						<a href="stats-firmwares.php"><?php echo _('Versions'); ?></a>
					</li>
					<li class="active">
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
								<a href="stats-download.php" class="btn btn-default"><?php echo _('Downloads'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-firmwares.php" class="btn btn-default"><?php echo _('Versions'); ?></a>
							</div>
							<div class="btn-group" role="group">
								<a href="stats-devices.php" class="btn btn-default active"><?php echo _('Devices'); ?></a>
							</div>
						</div>
						<div id="stats_device" class="text-center"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script src="/js/raphael.min.js"></script>
	<script src="/js/morris.min.js"></script>
	<script>Morris.Bar({element: 'stats_device',barColors:['#00f'],data: [<?php echo $data; ?>], xkey: 'y', ykeys: ['x'], labels: ['<?php echo _('Devices'); ?>']});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>