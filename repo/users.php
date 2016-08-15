<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 3) {
	require_once('includes/time-header.php');
	translation();
	$pdo = PDO2::getInstance();
	$errors = array();
	$success = array();
	$settings = Session::checkSettings('users', array('computer' => array('perPage' => 25, 'device' => true, 'firmware' => true, 'first_visit' => true, 'last_visit' => true, 'member' => true, 'total_download' => true), 'mobile' => array('perPage' => 25)));
	if(!empty($_POST)) {
		if($_POST['perPage'] < 1 || $_POST['perPage'] > 99 || !is_numeric($_POST['perPage']))
			$errors[] = _('Number of UDIDs must be an integer between 1 and 99 !');
		if(empty($errors)) {
			Session::setSettings('users');
			header('Location: users.php?settingsupdated');
		}
	}
	if(isset($_GET['settingsupdated']))
		$success[] = _('Your settings have been saved !');
	if($settings['perPage'] < 1 || $settings['perPage'] > 99 || !is_numeric($settings['perPage'])) {
		$settings = Session::checkSettings('users', array('computer' => array('perPage' => 25, 'device' => true, 'firmware' => true, 'first_visit' => true, 'last_visit' => true, 'member' => true, 'total_download' => true), 'mobile' => array('perPage' => 25)), true);
		header('Location: users.php');
	}
	if(!empty($_GET['id'])) {
		if(is_array($_GET['id'])) {
			foreach($_GET['id'] as $test_id) {
				$req = $pdo->prepare('SELECT udid, banni FROM users WHERE id = :id');
				$req->execute(array(':id' => $test_id));
				$count_udid = $req->rowCount();
				$test_udid = $req->fetch();
				$req->closeCursor();
				if($count_udid === 1) {
					if(isset($_GET['bannir']) && $membre->_level > 4) {
						if(!$test_udid['banni']) {
							$req = $pdo->prepare('INSERT INTO users (id, banni) VALUES (:id , :banni)
							ON DUPLICATE KEY UPDATE banni = :banni');
							$req->execute(array(':id' => $test_id, ':banni' => 1));
							$req->closeCursor();
							$success[] = sprintf(_('UDID %s has been banned !'), $test_udid['udid']);
						}
					} elseif(isset($_GET['debannir']) && $membre->_level > 4) {
						if($test_udid['banni']) {
							$req = $pdo->prepare('INSERT INTO users (id, banni) VALUES (:id , :banni)
							ON DUPLICATE KEY UPDATE banni = :banni');
							$req->execute(array(':id' => $test_id, ':banni' => 0));
							$req->closeCursor();
							$success[] = sprintf(_('UDID %s has been unbanned !'), $test_udid['udid']);
						}
					}
				} else
					$errors[] = _('UDID not found !');
				unset($test_udid);
				unset($count_udid);
			}
			unset($test_id);
		} else {
			$req = $pdo->prepare('SELECT udid, banni FROM users WHERE id = :id');
			$req->execute(array(':id' => $_GET['id']));
			$count_udid = $req->rowCount();
			$test_udid = $req->fetch();
			$req->closeCursor();
			if($count_udid === 1) {
				if(isset($_GET['bannir']) && $membre->_level > 4) {
					if(!$test_udid['banni']) {
						$req = $pdo->prepare('INSERT INTO users (id, banni) VALUES (:id , :banni)
						ON DUPLICATE KEY UPDATE banni = :banni');
						$req->execute(array(':id' => $_GET['id'], ':banni' => 1));
						$req->closeCursor();
						$success[] = sprintf(_('UDID %s has been banned !'), $test_udid['udid']);
					}
				} elseif(isset($_GET['debannir']) && $membre->_level > 4) {
					if($test_udid['banni']) {
						$req = $pdo->prepare('INSERT INTO users (id, banni) VALUES (:id , :banni)
						ON DUPLICATE KEY UPDATE banni = :banni');
						$req->execute(array(':id' => $_GET['id'], ':banni' => 0));
						$req->closeCursor();
						$success[] = sprintf(_('UDID %s has been unbanned !'), $test_udid['udid']);
					}
				}
			} else
				$errors[] = _('UDID not found !');
			unset($test_udid);
			unset($count_udid);
		}
	}
	if(isset($_GET['ord']) && $_GET['ord'] == 0) {
		$ordre = 0;$rangement = "ASC";
	} else {
		$ordre = 1;$rangement = "DESC";
	}
	if(isset($_GET['type'])) {
		switch($_GET['type']) {
			case 9:$type = 9;$order = 'total_download';break;
			case 8:$type = 8;$order = 'banni';break;
			case 6:$type = 6;$order = 'pseudo';break;
			case 4:$type = 4;$order = 'device';break;
			case 3:$type = 3;$order = 'firmware';break;
			case 2:$type = 2;$order = 'users.udid';break;
			case 1:$type = 1;$order = 'date';break;
			default: $type = 0;$order = 'date_update';break;
		}
	} else {
		$type = 0;
		$order = 'date_update';
	}

	$device_search = (!empty($_GET['device'])) ? urldecode(utf8_decode(trim($_GET['device']))) : urldecode('all');
	$device_req = (!empty($device_search) && $device_search != 'all') ? ' AND device = "'.$device_search.'"' : '';

	$firmware_search = (!empty($_GET['firmware'])) ? urldecode(utf8_decode(trim($_GET['firmware']))) : urldecode('all');
	$firmware_req = (!empty($firmware_search) && $firmware_search != 'all') ? ' AND firmware = "'.$firmware_search.'"' : '';

	$search = (!empty($_GET['s'])) ? strtolower(addslashes(preg_replace('#\%#', '\%', trim($_GET['s'])))) : '';

	$req = $pdo->prepare('SELECT COUNT(udid) FROM users WHERE udid LIKE :search'.$firmware_req.''.$device_req);
	$req->execute(array(':search' => '%'.$search.'%'));
	$req1 = $req->fetchColumn();
	$req->closeCursor();
	$fin = ceil($req1 / $settings['perPage']);

	$page = (!empty($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] - 1) < $fin && $_GET['page'] > 0) ? preg_replace("/[^0-9]/", '', $_GET['page']) : 1;

	$debut = ($page - 1) * $settings['perPage'];
	$resultat = ($req1 < 2) ? _('result') : _('results');
	$precedent = $page - 1;
	$suivant = $page + 1;
	$req = $pdo->prepare('SELECT users.id, udid, date, date_update, firmware, device, banni, (SELECT pseudo FROM membre WHERE users.membre = membre.id) AS pseudo, total_download FROM users WHERE udid LIKE :search'.$firmware_req.''.$device_req.' GROUP BY id ORDER BY '.$order.' '.$rangement.' LIMIT '.$debut.', '.$settings['perPage']);
	$req->execute(array(':search' => '%'.$search.'%'));
	$users = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	$req = $pdo->prepare('SELECT DISTINCT(firmware) FROM users ORDER BY firmware DESC');
	$req->execute();
	$firmwares = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	$req = $pdo->prepare('SELECT DISTINCT(device) FROM users ORDER BY device DESC');
	$req->execute();
	$devices = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();
/**
	$req = $pdo->prepare('SELECT COUNT(udid) FROM users WHERE device = "" OR device IS NULL OR device LIKE "% ?" OR firmware IS NULL OR firmware = ""');
	$req->execute();
	$inconnus = $req->fetchColumn();
	$req->closeCursor();
	if($inconnus > 1)
		$errors[] = 'Il y a encore '.@number_format($inconnus, 0, ', ', ' ').' UDIDs sans informations.';
	elseif($inconnus > 0)
		$errors[] = 'Il y a encore '.@number_format($inconnus, 0, ', ', ' ').' UDID sans informations.';
	else
		$success[] = 'Il y a '.@number_format($inconnus, 0, ', ', ' ').' udid sans infos. Changes la fonction enregistrer_udid() !';
**/
	$pdo = PDO2::closeInstance();
	$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php if(!empty($_GET['s'])) echo _('Search an UDID');else echo _('Manage UDIDs');if($page>1){echo ' - '._('Page').' '.$page;} ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div id="accordion" role="tablist" aria-multiselectable="true">
			<div class="panel" style="margin:0;background:transparent;border:0;box-shadow:0">
				<div id="filter" class="collapse jumbotron cleanTop" aria-labelledby="filterLabel">
					<div>
						<form method="post" action="<?php echo 'users.php?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$precedent.'&s='.urlencode(stripslashes($search)); ?>">
							<h5 style="margin-top:0;padding-top:10px"><?php echo _('On the screen'); ?></h5>
							<div class="checkbox">
								<ul class="list-inline">
									<li>
										<div class="checkbox">
											<input id="banned" type="checkbox" name="banned"<?php if(isset($settings['banned']))echo' checked'; ?>></input>
											<label for="banned"><?php echo _('Banned'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="device" type="checkbox" name="device"<?php if(isset($settings['device']))echo' checked'; ?>></input>
											<label for="device"><?php echo _('Device'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="firmware" type="checkbox" name="firmware"<?php if(isset($settings['firmware']))echo' checked'; ?>></input>
											<label for="firmware">Firmware</label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="first_visit" type="checkbox" name="first_visit"<?php if(isset($settings['first_visit']))echo' checked'; ?>></input>
											<label for="first_visit"><?php echo _('First visit'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="last_visit" type="checkbox" name="last_visit"<?php if(isset($settings['last_visit']))echo' checked'; ?>></input>
											<label for="last_visit"><?php echo _('Last visit'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="member" type="checkbox" name="member"<?php if(isset($settings['member']))echo' checked'; ?>></input>
											<label for="member"><?php echo _('Member'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="total_download" type="checkbox" name="total_download"<?php if(isset($settings['total_download']))echo' checked'; ?>></input>
											<label for="total_download"><?php echo _('Download(s)'); ?></label>
										</div>
									</li>
								</ul>
								<?php if(type_device() == 'iPhone' || type_device() == 'iPad' || type_device() == 'iPod')
									echo '<p class="help-block">'._('On mobile, you can\'t display alot of columns, sorry').'</p>'; ?>
							</div><hr />
							<div class="form-group">
								<ul class="list-inline">
									<li>
										<div class="checkbox">
											<input id="perPage" type="number" value="<?php echo $settings['perPage']; ?>" maxlength="3" name="perPage" max="999" min="1" step="1"></input>
											<label for="perPage">UDIDs</label>
										</div>
									</li>
									<li>
										<button type="submit" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-ok"></span></button>
									</li>
								</ul>
							</div>
						</form>
					</div>
				</div>
				<div id="help" class="collapse jumbotron cleanTop" aria-labelledby="helpLabel">
					<div>
						<h4 style="margin-top:0;padding-top:10px"><?php echo _('Help'); ?></h4>
						<p><?php echo _('Here, you can manage members.');
						switch($membre->_level) {
							case 5:
								echo _('You\'re allowed to edit and see all informations about members.');
								break;
							case 4:
								echo _('You\'re allowed to edit and see some informations about members.');
								break;
							default:
								echo _('You can\'t see this part, what\'s the fuck ?!');
						} ?></p>
					</div>
				</div>
			</div>
			<div class="tabbable-line pull-right">
				<ul class="nav nav-tabs text-center">
					<li role="tab" id="filterLabel" class="cleanBottom">
						<a class="cleanBottom collapsed" data-toggle="collapse" data-parent="#accordion" href="#filter" aria-expanded="false" aria-controls="filter"><span class="glyphicon glyphicon-filter"></span></a>
					</li>
					<li role="tab" id="helpLabel" class="cleanBottom">
						<a class="cleanBottom collapsed" data-toggle="collapse" data-parent="#accordion" href="#help" aria-expanded="false" aria-controls="help"><span class="glyphicon glyphicon-question-sign"></span></a>
					</li>
				</ul>
			</div>
		</div>
		<div class="panel-heading">
			<h2 class="text-primary"><?php if(!empty($_GET['s'])) echo _('Search an UDID').' <small>'.@number_format($req1, 0, ', ', ' ').' '.$resultat.'</small>';else echo _('Manage UDIDs').' <small>'.@number_format($req1, 0, ', ', ' ').'</small>'; ?></h2>
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
		<div class="jumbotron nopadd">
			<form class="navbar-form text-center" style="box-shadow:0 0 0">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="<?php echo _('Search an UDID'); ?>" name="s" value="<?php echo htmlspecialchars(stripslashes(preg_replace('#\\\%#', '%', $search))); ?>" />
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="device" class="form-control">
						<option value="all"><?php echo _('All devices'); ?></option>
						<?php foreach($devices as $device) {
							if(!empty($device['device'])) {
								echo '<option value="'.$device['device'].'"';
								if($device_search == $device['device'])
									echo ' selected';
								echo '>'.$device['device'];
								echo '</option>';
							}
						} ?>
					</select>
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="firmware" class="form-control">
						<option value="all"><?php echo _('All firmwares'); ?></option>
						<?php foreach($firmwares as $firmware) {
							if(!empty($firmware['firmware'])) {
								echo '<option';
								if($firmware_search === $firmware['firmware'])
									echo ' selected';
								echo '>'.$firmware['firmware'];
								echo '</option>';
							}
						} ?>
					</select>
				</div>
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php if(!empty($users)) { ?>
				<form id="multiAction">
					<p style="margin:-10px auto 5px" class="text-center visible displayMulti"><button class="btn btn-xs btn-primary" type="button" onclick="$('.hidden').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('hidden visible', 1000 );"><?php echo _('Multiple choise'); ?></button></p>
					<p style="margin:-10px auto 5px" class="text-center hidden">
						<select class="btn-xs btn-primary selectActionTop">
							<option><?php echo _('Select an option'); ?></option>
							<?php if($membre->_level > 4) { ?>
							<option value="bannir"><?php echo _('Banish'); ?></option>
							<option value="debannir"><?php echo _('Unban'); ?></option>
							<option value="delete"><?php echo _('Delete'); ?></option>
							<?php } ?>
						</select>
						<button class="btn btn-xs btn-primary" type="submit"><?php echo _('Action'); ?></button>
						<button class="btn btn-xs btn-primary" type="button" onclick="$('.visible').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('visible hidden', 1000 );"><?php echo _('Hide'); ?></button>
					</p>
					<table class="table table-condensed table-hover" style="border-bottom:1px solid;margin:0">
						<th style="border-color:#000 !important" class="hidden text-center allMulti"><input type="checkbox" name="allMulti" id="allMulti" /> <label for="allMulti"><?php echo _('Multi'); ?></label></th>
						<?php if($ordre == 0 && $type == 8 && isset($settings['banned']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=8&ord=1&s='.urlencode(stripslashes($search)).'">'._('Banned').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 8 && isset($settings['banned']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=8&ord=0&s='.urlencode(stripslashes($search)).'">'._('Banned').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['banned']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=8&ord=0&s='.urlencode(stripslashes($search)).'">'._('Banned').'</a></th>';
						if($ordre == 0 && $type == 2)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=2&ord=1&s='.urlencode(stripslashes($search)).'">UDID</a> <span class="glyphicon glyphicon-sort-by-alphabet"></span></th>';
						elseif($ordre == 1 && $type == 2)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=2&ord=0&s='.urlencode(stripslashes($search)).'">UDID</a> <span class="glyphicon glyphicon-sort-by-alphabet-alt"></span></th>';
						else
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=2&ord=0&s='.urlencode(stripslashes($search)).'">UDID</a></th>';
						if($ordre == 0 && $type == 4 && isset($settings['device']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=4&ord=1&s='.urlencode(stripslashes($search)).'">'._('Device').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 4 && isset($settings['device']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=4&ord=0&s='.urlencode(stripslashes($search)).'">'._('Device').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['device']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=4&ord=0&s='.urlencode(stripslashes($search)).'">'._('Device').'</a></th>';
						if($ordre == 0 && $type == 3 && isset($settings['firmware']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=3&ord=1&s='.urlencode(stripslashes($search)).'">Firmware</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 3 && isset($settings['firmware']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=3&ord=0&s='.urlencode(stripslashes($search)).'">Firmware</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['firmware']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=3&ord=0&s='.urlencode(stripslashes($search)).'">Firmware</a></th>';
						if($ordre == 0 && $type == 1 && isset($settings['first_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=1&ord=1&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 1 && isset($settings['first_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=1&ord=0&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['first_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=1&ord=0&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a></th>';
						if($ordre == 0 && $type == 0 && isset($settings['last_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=0&ord=1&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 0 && isset($settings['last_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=0&ord=0&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['last_visit']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=0&ord=0&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a></th>';
						if($ordre == 0 && $type == 6 && isset($settings['member']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=6&ord=1&s='.urlencode(stripslashes($search)).'">'._('Member').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 6 && isset($settings['member']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=6&ord=0&s='.urlencode(stripslashes($search)).'">'._('Member').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['member']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=6&ord=0&s='.urlencode(stripslashes($search)).'">'._('Member').'</a></th>';
						if($ordre == 0 && $type == 9 && isset($settings['total_download']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=9&ord=1&s='.urlencode(stripslashes($search)).'">'._('Download(s)').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 9 && isset($settings['total_download']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=9&ord=0&s='.urlencode(stripslashes($search)).'">'._('Download(s)').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['total_download']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type=9&ord=0&s='.urlencode(stripslashes($search)).'">'._('Download(s)').'</a></th>';
						if ($membre->_level > 2)
							echo '<th style="border-color:#000 !important" class="text-center">'._('Action').'</th>';
						foreach ($users as $key) {
							$date = date_format(date_create($key['date']), "d/m/Y - H:i");
							$date_update = date_format(date_create($key['date_update']), "d/m/Y - H:i");
							$nom_firmware = (empty($key['firmware'])) ? _('Unknow') : $key['firmware'];
							$nom_device = (empty($key['device'])) ? _('Unknow') : $key['device'];
							if(empty($key['pseudo']))
								$pseudo = _('No');
							else {
								$pseudo = $key['pseudo'];
							}
							$etat = ($key['banni']) ? '<span class="label label-danger">'._('Yes').'</span>' : '<span class="label label-primary">'._('No').'</span>';
							$etat_link = ($key['banni']) ? '&debannir' : '&bannir';
							$etat_glyphicon = ($key['banni']) ? 'ok' : 'remove';
							$etat_texte = ($key['banni']) ? _('Unban') : _('Banish');
							echo '<tr>
								<td style="border-color:#000 !important" class="hidden text-center"><input type="checkbox" value="'.rawurlencode($key['id']).'" name="id[]" /></td>';
							if(isset($settings['banned']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$etat.'</td>';
								echo '<td style="border-color:#000 !important;word-break:break-all;" class="text-center"><small>'.$key['udid'].'</small></td>';
							if(isset($settings['device']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$nom_device.'</td>';
							if(isset($settings['firmware']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$nom_firmware.'</td>';
							if(isset($settings['first_visit']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$date.'</td>';
							if(isset($settings['last_visit']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$date_update.'</td>';
							if(isset($settings['member']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$pseudo.'</td>';
							if(isset($settings['total_download']))
								echo '<td style="border-color:#000 !important" class="text-center">'.print_number($key['total_download']).'</td>';
								echo '<td style="border-color:#000 !important" class="text-center"><div class="btn-group">
									<button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown">'._('Action').' <span class="caret"></span></button>
									<ul class="dropdown-menu pull-right text-left" role="menu">
										<li><a href="user.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon glyphicon-stats"></span> '._('Statistics').'</a></li>';
									if ($membre->_level > 4) {
										echo '<li><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page=1&s='.urlencode(stripslashes($search)).'&id='.$key['id'].''.$etat_link.'" class="btn-xs"><span class="glyphicon glyphicon glyphicon-'.$etat_glyphicon.'"></span> '.$etat_texte.'</a></li>';
										echo '<li class="divider"></li>';
										echo '<li class="deconnexion"><a href="delete-user.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon glyphicon-trash"></span> '._('Delete').'</a></li>';
									}
									echo '</ul>
								</div></td>
							</tr>';
						} ?>
					</table>
					<p style="margin:0 auto 5px" class="text-center visible displayMulti"><button class="btn btn-xs btn-primary" type="button" onclick="$('.hidden').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('hidden visible', 1000 );"><?php echo _('Multiple choise'); ?></button></p>
					<p style="margin:0 auto 5px" class="text-center hidden">
						<select class="btn-xs btn-primary selectActionBottom">
							<option><?php echo _('Select an option'); ?></option>
							<?php if($membre->_level > 4) { ?>
							<option value="bannir"><?php echo _('Banish'); ?></option>
							<option value="debannir"><?php echo _('Unban'); ?></option>
							<option value="delete"><?php echo _('Delete'); ?></option>
							<?php } ?>
						</select>
						<button class="btn btn-xs btn-primary" type="submit"><?php echo _('Action'); ?></button>
						<button class="btn btn-xs btn-primary" type="button" onclick="$('.visible').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('visible hidden', 1000 );"><?php echo _('Hide'); ?></button>
					</p>
					<?php echo '<div class="text-center"><ul class="pagination">';
					if($page > 1)
						echo '<li><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page=1&s='.urlencode(stripslashes($search)).'">1</a></li>';
					if($precedent > 2)
						echo '<li class="disabled"><a>...</a></li>';
					if($page > 2)
						echo '<li><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$precedent.'&s='.urlencode(stripslashes($search)).'">'.$precedent.'</a></li>';
					echo '<li class="active"><a>'.$page.'</a></li>';
					if($fin > $suivant)
						echo '<li><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$suivant.'&s='.urlencode(stripslashes($search)).'">'.$suivant.'</a></li>';
					if($fin > $suivant + 1)
						echo '<li class="disabled"><a>...</a></li>';
					if($fin >= $suivant)
						echo '<li><a href="?firmware='.$firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$fin.'&s='.urlencode(stripslashes($search)).'">'.$fin.'</a></li>';
					echo '</ul></div>'; ?>
				</form>
			<?php } else
				echo '<p class="text-center">'._('UDID not found !').'</p>'; ?>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script>jQuery(document).ready(function(e){

		$("#multiAction").submit(function(e){
			e.preventDefault();
			var checked = false;
			$("input[name='id[]']").map(function(){
				if($(this).prop('checked') == true)
					checked = true;
			});
			if(checked == true) {
				var selectedTop = $('.selectActionTop').find(":selected").val();
				var selectedBottom = $('.selectActionBottom').find(":selected").val();
				var ids = $("input[name='id[]']").map(function(){if($(this).is(':checked'))return $(this).val();}).get();
				<?php if($membre->_level > 4) { ?>
				if(selectedTop == 'bannir' || selectedBottom == 'bannir')
					window.location.replace("users.php?firmware=<?php echo $firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$page.'&s='.urlencode(stripslashes($search)); ?>&id[]="+ids.join("&id[]=")+"&bannir");
				else if(selectedTop == 'debannir' || selectedBottom == 'debannir')
					window.location.replace("users.php?firmware=<?php echo $firmware_search.'&device='.$device_search.'&type='.$type.'&ord='.$ordre.'&page='.$page.'&s='.urlencode(stripslashes($search)); ?>&id[]="+ids.join("&id[]=")+"&debannir");
				else if(selectedTop == 'delete' || selectedBottom == 'delete')
					window.location.replace("delete-user.php?id[]="+ids.join("&id[]="));
				else
					alert('<?php echo _('Please choose an action !'); ?>');
				<?php } ?>
			} else
				alert('<?php echo _('Please select a package !'); ?>');
		});
		$(".allMulti").click(function(){
			var is_checked = $("input[name='allMulti']").is(":checked");
			$("input[name='id[]']").map(function(){
				$(this).prop('checked', is_checked);
			});
			$("input[name='allMulti']").prop('checked', is_checked);
		});
		$("input[name='id[]']").click(function(){
			var total_boxes = $("input[name='id[]']").length;
			var checked_boxes = $("input[name='id[]']:checked").length;
			var $checkall = $("input[name='allMulti']");
			if (total_boxes == checked_boxes)
				$checkall.prop({checked: true, indeterminate: false});
			else if (checked_boxes > 0)
				$checkall.prop({checked: true, indeterminate: true});
			else
				$checkall.prop({checked: false, indeterminate: false});
		});
	});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>