<?php require_once('includes/session.class.php');
$membre = new Session();
if($membre->_connected) {
	require_once('includes/time-header.php');
	require_once('includes/package.class.php');
	$lang_user = translation();
	$pdo = PDO2::getInstance();
	$devices = $pdo->prepare('SELECT id, udid, device FROM users WHERE membre = :membre');
	$devices->execute(array(':membre' => $membre->_id));
	$devices = $devices->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($_GET['device'])) {
		$test_devices = $pdo->prepare('SELECT udid FROM users WHERE membre = :membre AND id = :id');
		$test_devices->execute(array(':membre' => $membre->_id, ':id' => $_GET['device']));
		$test_devices = $test_devices->fetchColumn();
		$device_select = $test_devices;
		if($device_select) {
			if(isset($_GET['type'])) {
				switch($_GET['type']) {
					case 'download': $type = 'COUNT(download.id)';break;
					// case 'date': $type = 'date';break;
					default: $type = 'Name';break;
				}
			} else
				$type = 'Name';
			if(isset($_GET['ord']) && $_GET['ord'] == 1)
				$ordre = 'DESC';
			else
				$ordre = 'ASC';
			$count_paquets = $pdo->prepare('SELECT COUNT(download.id) FROM description_meta INNER JOIN download ON download.package = description_meta.id WHERE online = true AND user = :user');
			$count_paquets->execute(array(':user' => $_GET['device']));
			$count_paquets = $count_paquets->fetchColumn();
			$count_paquet = $pdo->prepare('SELECT COUNT(DISTINCT package) FROM download INNER JOIN description_meta ON download.package = description_meta.id WHERE online = true AND user = :user');
			$count_paquet->execute(array(':user' => $_GET['device']));
			$count_paquet = $count_paquet->fetchColumn();
			$fin = ceil($count_paquet / 25);
			$page = (isset($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] - 1) < $fin && $_GET['page'] > 0) ? preg_replace("/[^0-9]/", '', $_GET['page']) : 1;
			$debut = ($page - 1) * 25;
			$precedent = $page - 1;
			$suivant = $page + 1;
			$paquets = $pdo->prepare('SELECT DISTINCT download.package, COUNT(download.id), Name, Author, description.Version, Section FROM description INNER JOIN description_meta ON description.id= description_meta.id INNER JOIN download ON download.package = description_meta.id WHERE online = true AND user = :user GROUP BY package ORDER BY '.$type.' '.$ordre.' LIMIT '.$debut.', 25');
			$paquets->execute(array(':user' => $_GET['device']));
			$paquets = $paquets->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	$pdo = PDO2::closeInstance();
	$site_nom = config('nom');
	$site_url = config('url'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/style.min.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<title><?php echo _('Your downloads'); ?> | <?php echo $site_nom; ?></title>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0;" href="./"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0;" href="login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<h3 class="text-center"><span class="glyphicon glyphicon-user"></span> <?php echo _('Your downloads');if($device_select) echo ' <small>'.$count_paquets.'</small>' ?></h3><hr />
			<?php if($devices) { ?>
				<form class="navbar-form text-center" style="box-shadow:0 0 0">
					<select style="margin-bottom:10px" name="device" class="form-control">
						<?php foreach($devices as $device) {
							$type_appareil = (!empty($device['device'])) ? $device['device'] : 'unknow';
							echo '<option style="overflow:hidden"';
							if($device_select == $device['udid'])
								echo ' selected';
							echo ' value="'.$device['id'].'">'.$type_appareil.' - '.substr($device['udid'], 0, 10).'...</option>';
						} ?>
					</select>
					<?php echo _('order by'); ?>
					<select style="margin-bottom:10px" name="type" class="form-control">
						<option value="name"<?php if($type === 'Name') echo ' selected'; ?>><?php echo _('Name'); ?></option>
						<!--option value="date"<?php if($type === 'date') echo ' selected'; ?>><?php echo _('Date of last download'); ?></option-->
						<option value="download"<?php if($type === 'COUNT(download.id)') echo ' selected'; ?>><?php echo _('Number of downloads'); ?></option>
					</select>
					<?php echo _('in order'); ?>
					<select style="margin-bottom:10px" name="ord" class="form-control">
						<option value="0"<?php if($ordre === 'ASC') echo ' selected'; ?>><?php echo _('Ascending'); ?></option>
						<option value="1"<?php if($ordre === 'DESC') echo ' selected'; ?>><?php echo _('Descending'); ?></option>
					</select>
					<button style="margin-bottom:10px" type="submit" class="btn btn-primary">OK</button>
				</form>
				<?php if($device_select) { ?>
					<?php if($count_paquet > 0) {
						foreach($paquets as $paquet) {
							$pack = new Paquet($paquet['package']);
							if(file_exists('images/debs/'.$paquet['package'].'.png'))
								$icone_paquet = 'images/debs/'.$paquet['package'].'.png';
							else
								$icone_paquet = 'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($paquet['Section']))).'.png';

							echo '<span class="list-group-item media text-center">
							<span class="pull-left"><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px"  class="media-object" src="'.$icone_paquet.'" alt="'.$paquet['Name'].'"></span>
							<div class="media-body">
								<h4 class="media-heading"><a href="pack/'.$paquet['package'].'">'.$paquet['Name'].'</a> <small>'.$paquet['Version'].'</small> '.$pack->favorite_bar($membre->_id).'</h4>
								<span class="glyphicon glyphicon-user"></span> '.$paquet['Author'].' <a href="section/'.rawurlencode($paquet['Section']).'"><span class="glyphicon glyphicon-folder-open"></span> '.$paquet['Section'].' </a><span class="glyphicon glyphicon-download"></span> '.$paquet['COUNT(download.id)'].'
							</div>
							</span>';
							$pack = NULL;
						}
						echo '<div class="text-center"><ul class="pagination">';
						if($page > 1)
							echo '<li><a href="?device='.$_GET['device'].'&type='.$type.'&ord='.$ordre.'&page=1">1</a></li>';
						if($precedent > 2)
							echo '<li class="disabled"><a>...</a></li>';
						if($page > 2)
							echo '<li><a href="?device='.$_GET['device'].'&type='.$type.'&ord='.$ordre.'&page='.$precedent.'">'.$precedent.'</a></li>';
						echo '<li class="active"><a>'.$page.'</a></li>';
						if($fin > $suivant)
							echo '<li><a href="?device='.$_GET['device'].'&type='.$type.'&ord='.$ordre.'&page='.$suivant.'">'.$suivant.'</a></li>';
						if($fin > $suivant + 1)
							echo '<li class="disabled"><a>...</a></li>';
						if($fin >= $suivant)
							echo '<li><a href="?device='.$_GET['device'].'&type='.$type.'&ord='.$ordre.'&page='.$fin.'">'.$fin.'</a></li>';
						echo '</ul></div>';
					} else
					echo '<div class="lead">
						<p class="text-center">'._('You have nothing downloaded yet.').'</p>
					</div>';
				} else { ?>
					<div class="lead">
						<p class="text-center"><?php echo _('Select one of your devices to view the downloads.'); ?></p>
					</div>
				<?php }
			} else { ?>
				<div class="lead">
					<p class="text-center"><?php echo _('Start by registering a device to your account.'); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script async type="text/javascript" src="js/behavior.js"></script>
		<script async type="text/javascript" src="js/favorite.js"></script>
	</body>
</html>
<?php } else
	header('Location: login.php'); ?>