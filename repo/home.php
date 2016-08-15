<?php require_once('includes/session.class.php');
$membre = new Session();
if($membre->_connected && $membre->_level > 0) {
	function cpu() {
		$uptime = exec('uptime');
		$pos = strpos($uptime, 'load') + 14;
		$uptime = substr($uptime, $pos);
		$pos = strrpos($uptime, ',');
		$uptime = substr($uptime, $pos+1);
		return $uptime;
	}
	require_once('includes/time-header.php');
	translation();
	$pdo = PDO2::getInstance();
	if($membre->_level == 5) {
		$req = $pdo->prepare("SELECT COUNT(DISTINCT ip) FROM users WHERE ip != ''");
		$req->execute(array(':date' => date('Y-m-d', time())."%"));
		$total_distinct_ip = $req->fetchColumn();
		$req->closeCursor();

		$req = $pdo->prepare("SELECT COUNT(ip) FROM users WHERE ip != ''");
		$req->execute(array(':date' => date('Y-m-d', time())."%"));
		$total_ip = $req->fetchColumn();
		$req->closeCursor();

		$req = $pdo->prepare("SELECT COUNT(id) FROM membre WHERE last_date >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
		$req->execute();
		$total_online = $req->fetchColumn();
		$req->closeCursor();
	}

	switch($membre->_level) {
		case '1': $level = _('Simple uploader');break;
		case '2': $level = _('Official uploader');break;
		case '3': $level = _('Manager');break;
		case '4': $level = _('Assistant Director');break;
		case '5': $level = _('Administrator');break;
		default: $level = _('Suspect');break;
	}
	$site_url = config('url');
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html>
<head>
	<title><?php echo _('Administration').' - '.$site_nom; ?></title>
	<link rel="stylesheet" href="css/morris.css">
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Administration'); ?></h2>
		</div>
		<div class="jumbotron">
			<h3 class="text-center"><?php echo sprintf(_('Welcome to the administration %s'), '<span class="text-info">'.$membre->_pseudo.'</span>').' - '._('Access level').' : <span class="text-info">'.$level.'</span>'; ?></h3>
			<div class="row marketing text-center">
			</div>
			<?php if($membre->_level == 5) { ?>
			<div class="text-center lead">
				<p class="text-success">Totals IPs différentes : <?php echo print_number($total_distinct_ip); ?> / <?php echo print_number($total_ip); ?> connus</p>
			</div>
			<?php } ?>
			<div class="text-center lead">
				<div class="col-sm-3">
					<span id="dwn">0</span> <span class="glyphicon glyphicon-download"></span>
				</div>
				<div class="col-sm-3">
					<span id="device">0</span> <span class="glyphicon glyphicon-phone"></span>
				</div>
				<div class="col-sm-3">
					<span id="user">0</span> <span class="glyphicon glyphicon-user"></span>
				</div>
				<div class="col-sm-3">
					<span id="dwnDay">0</span> <span class="glyphicon glyphicon-download"></span> <?php echo _('today'); ?>
				</div>
			</div>
			<div class="row marketing text-center">
				<div class="col-lg-6">
					<h4><?php echo _('Total device of the day : '); ?><span id="total_device_day">0</span></h4>
					<div id="donut-jour"></div>
				</div>
				<div class="col-lg-6">
					<h4><?php echo _('Devices in the last 7 days : '); ?><span id="total_device_week">0</span></h4>
					<div id="donut-semaine"></div>
				</div>
			</div>
			<?php if($membre->_level == 5) {
				$cpu = cpu();
				echo '<div class="progress">
					<div id="cpu" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="'.$cpu.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$cpu.'%"></div>
					<p class="text-center" style="font-size:15px">'.$cpu.'% CPU utilisé</p>
				</div>';
				if(file_exists('includes/verifycron.html'))
					echo '<p class="text-center lead">'.file_get_contents('includes/verifycron.html').'</p>';
				if($total_online > 1)
					echo '<p class="text-center lead">'.$total_online.' utilisateurs en ligne</p>';
				else
					echo '<p class="text-center lead">'.$total_online.' utilisateur en ligne</p>';
			} ?>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script src="js/raphael.min.js"></script>
	<script src="js/morris.min.js"></script>
	<script>$(document).ready(function(){
	$.getJSON('ajax/home.json', function() {
	}).done(function(data){
		$('#total_device_day').text(spaceSeparateNumber(parseInt(data.total_new_jour) + parseInt(data.total_udid_jour)));
		$('#total_device_week').text(spaceSeparateNumber(parseInt(data.total_new_week) + parseInt(data.total_udid_week)));
		Morris.Donut({element: 'donut-jour',resize:true,colors:['#f00', '#000'],data: [{label: "<?php echo _('New'); ?>", value: data.total_new_jour},{label: "<?php echo _('Known'); ?>", value: data.total_udid_jour}]});Morris.Donut({element: 'donut-semaine',resize:true,colors:['#f00', '#000'],data: [{label: "<?php echo _('New'); ?>", value: data.total_new_week},{label: "<?php echo _('Known'); ?>", value: data.total_udid_week}]});
	});
	$.getJSON('ajax/stats.json', function() {
	}).done(function(data){
		replaceNumberDiv(data.total_udid, '#device');
		replaceNumberDiv(data.total_member, '#user');
		replaceNumberDiv(data.total_download, '#dwn');
		replaceNumberDiv(data.total_download_day, '#dwnDay');
	});
	function replaceNumberDiv(newNumber, div) {
		var number = $(div).attr('data-id');
		if(!number)
			number = 0;
		if(number != newNumber) {
			$({someValue: number}).animate({someValue: newNumber}, {
				duration: 1000,
				easing:'swing',
				step: function() {
					$(div).css('text-shadow', '0 0 5px #0f0');
					$(div).text(spaceSeparateNumber(Math.round(this.someValue)));
				},
				done: function() {
					$(div).css('text-shadow', '0 0 0 #fff');
					$(div).attr('data-id', newNumber);
				}
			});
		}
	}
	function spaceSeparateNumber(val){
		while (/(\d+)(\d{3})/.test(val.toString())){
			val = val.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1 ");
		}
		return val;
	}});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>