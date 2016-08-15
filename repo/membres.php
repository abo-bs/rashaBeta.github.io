<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 3) {
		require_once('includes/time-header.php');
		translation();
		$pdo = PDO2::getInstance();
		$errors = array();
		$success = array();
		if(isset($_GET['id'])) {
			$test_membre = $pdo->prepare('SELECT pseudo, mail FROM membre WHERE id = :id');
			$test_membre->execute(array(':id' => $_GET['id']));
			$count_membre = $test_membre->rowCount();
			$test_membre = $test_membre->fetch();
			if($count_membre === 1) {
				if(isset($_GET['reset'])) {
					$site_nom = config('nom');
					$site_url = config('url');
					$recovery = substr(sha1(time() * rand(0, 999)), 0, 10);
					$update_user = $pdo->prepare('INSERT INTO membre (id, recovery) VALUES (:id, :recovery)
					ON DUPLICATE KEY UPDATE recovery = :recovery');
					$update_user->execute(array(':id' => $_GET['id'], ':recovery' => $recovery));
					$headers   = array();
					$headers[] = "MIME-Version: 1.0";
					$headers[] = "Content-type: text/html; charset=utf-8";
					$headers[] = "From: ".$site_nom." Robot <noreply@goldencydia.org>";
					$headers[] = "Subject: "._('Lost password')." | ".$site_nom;
					$headers[] = "Return-Path: <".$test_membre['mail'].">";
					$headers[] = "X-Mailer: PHP/".phpversion();
					$headers[] = "X-Sender: <www.goldencydia.org>";
					$headers[] = "X-auth-smtp-user: contact@goldencydia.org";
					$headers[] = "X-abuse-contact: abuse@goldencydia.org";
					$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'._('Lost password').'</title></head><body><div style="margin:0 auto;text-align:center"><a href="'.$site_url.'"><img src="'.$site_url.'images/logo.png" /></a></div><p>'.$test_membre['pseudo'].',<br />'._('To complete the phase resetting your password, you will need to go to the following URL in your web browser.').'</p><p><a href="'.$site_url.'forgot-password.php?id='.$_GET['id'].'&recovery='.$recovery.'">'.$site_url.'forgot-password.php?id='.$_GET['id'].'&recovery='.$recovery.'</a></p><p>'._('You will receive another mail with your new password.').'</p><br /><p>'.$site_nom.' Staff</p></body></html>';
					mail($test_membre['mail'], _('Lost password'), $message, implode("\r\n", $headers));
					$success[] = sprintf(_('%s\'s password has been reset !'), $test_membre['pseudo']);
				}
			} else
				$errors[] = _('No member found !');
		}
		$settings = Session::checkSettings('membres', array('computer' => array('perPage' => 25, 'level' => true, 'last_visit' => true, 'first_visit' => true, 'packages' => true, 'udid' => true), 'mobile' => array('perPage' => 25)));
		if(!empty($_POST)) {
			if($_POST['perPage'] < 1 || $_POST['perPage'] > 99 || !is_numeric($_POST['perPage']))
				$errors[] = _('Number of packages must be an integer between 1 and 99 !');
			if(empty($errors)) {
				Session::setSettings('membres');
				header('Location: membres.php?settingsupdated');
				exit;
			}
		}
		if(isset($_GET['settingsupdated']))
			$success[] = _('Your settings have been saved !');
		if($settings['perPage'] < 1 || $settings['perPage'] > 99 || !is_numeric($settings['perPage'])) {
			$settings = Session::checkSettings('membres', array('computer' => array('perPage' => 25, 'level' => true, 'last_visit' => true, 'first_visit' => true, 'packages' => true, 'udid' => true), 'mobile' => array('perPage' => 25)), true);
			header('Location: membres.php');
			exit;
		}
		if(isset($_GET['ord']) && $_GET['ord'] == 0) {
			$ordre = 0;$rangement = "DESC";
		} else {
			$ordre = 1;$rangement = "ASC";
		}
		if(isset($_GET['type'])) {
			switch($_GET['type']) {
				case 7:$type = 7;$order = "membre.date";break;
				case 5:$type = 5;$order = "udid";break;
				case 3:$type = 3;$order = "package";break;
				case 2:$type = 2;$order = "membre.last_date";break;
				case 1:$type = 1;$order = "level";break;
				default: $type = 0;$order = "pseudo";break;
			}
		} else {
			$type = 0;$order = "pseudo";
		}
		$level_search = (is_numeric($_GET['level'])) ? urlencode(addslashes(trim($_GET['level']))) : urlencode('all');
		$level_req = (is_numeric($_GET['level']) && $_GET['level'] != 'all') ? ' AND level = "'.$level_search.'"' : '';
		$search = (isset($_GET['s'])) ? addslashes(trim($_GET['s'])) : $search = '';
		if(isset($_GET['champs'])) {
			switch($_GET['champs']) {
				case 'all': $champs = '(pseudo LIKE "%'.$search.'%" OR mail LIKE "%'.$search.'%")';$champ = 'all';break;
				case 'mail': $champs = 'mail LIKE "%'.$search.'%"';$champ = 'mail';break;
				case 'pseudo': $champs = 'pseudo LIKE "%'.$search.'%"';$champ = 'pseudo';break;
				default: $champs = 'pseudo LIKE "%'.$search.'%"';$champ = 'pseudo';break;
			}
		} else {
			$champs = 'pseudo LIKE "%'.$search.'%"';
			$champ = 'pseudo';
		}
		$req1 = $pdo->prepare('SELECT COUNT(id) FROM membre WHERE '.$champs.''.$level_req);
		$req1->execute();
		$req1 = $req1->fetchColumn();
		$fin = ceil($req1 / $settings['perPage']);
		if(!empty($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] - 1) < $fin && $_GET['page'] > 0)
			$page = preg_replace("/[^0-9]/", '', $_GET['page']);
		else
			$page = 1;
		$debut = ($page - 1) * $settings['perPage'];
		$resultat = ($req1 < 2) ? _('result') : _('results');
		$precedent = $page - 1;
		$suivant = $page + 1;
		$req = $pdo->prepare('SELECT membre.id, pseudo, level, membre.last_date, membre.date, (SELECT COUNT(id_membre) FROM description_meta WHERE id_membre = membre.id) AS package, (SELECT COUNT(users.id) FROM users WHERE membre = membre.id) AS udid FROM membre LEFT JOIN description_meta ON membre.id = description_meta.id_membre WHERE '.$champs.''.$level_req.' GROUP BY membre.id ORDER BY '.$order.' '.$rangement.' LIMIT '.$debut.', '.$settings['perPage']);
		$req->execute();
		$req = $req->fetchAll(PDO::FETCH_ASSOC);
		$site_nom = config('nom');
		$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php if(!empty($_GET['s'])) echo _('Search a member');else echo _('Manage members');if($page>1){echo ' - '._('Page').' '.$page;} ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">

		<div id="accordion" role="tablist" aria-multiselectable="true">
			<div class="panel" style="margin:0;background:transparent;border:0;box-shadow:0">
				<div id="filter" class="collapse jumbotron cleanTop" aria-labelledby="filterLabel">
					<div>
						<form method="post" action="<?php echo 'membres.php?champs='.$champ.'&type='.$type.'&ord='.$ordre.'&page='.$precedent.'&s='.urlencode(stripslashes($search)); ?>">
							<h5 style="margin-top:0;padding-top:10px"><?php echo _('On the screen'); ?></h5>
							<div class="checkbox">
								<ul class="list-inline">
									<li>
										<div class="checkbox">
											<input id="level" type="checkbox" name="level"<?php if(isset($settings['level']))echo' checked'; ?>></input>
											<label for="level"><?php echo _('Level'); ?></label>
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
											<input id="packages" type="checkbox" name="packages"<?php if(isset($settings['packages']))echo' checked'; ?>></input>
											<label for="packages"><?php echo _('Packages'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="udid" type="checkbox" name="udid"<?php if(isset($settings['udid']))echo' checked'; ?>></input>
											<label for="udid">Udid(s)</label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="notification" type="checkbox" name="notification"<?php if(isset($settings['notification']))echo' checked'; ?>></input>
											<label for="notification"><?php echo _('Notification'); ?></label>
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
											<label for="perPage"><?php echo _('Members'); ?></label>
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
			<h2 class="text-primary"><?php if(!empty($_GET['s'])) echo _('Search a member').' <small>'.@number_format($req1, 0, ', ', ' ').' '.$resultat.'</small>';else echo _('Manage members').' <small>'.@number_format($req1, 0, ', ', ' ').'</small>'; ?></h2>
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
					<input type="text" class="form-control" placeholder="<?php echo _('Search'); ?>" name="s" value="<?php echo htmlspecialchars(stripslashes($search)); ?>" />
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="champs" class="form-control">
						<option value="all"><?php echo _('All fields'); ?></option>
						<option value="pseudo"<?php if($champ == "pseudo") echo ' selected'; ?>><?php echo _('Nicknames'); ?></option>
						<option value="mail"<?php if($champ == "mail") echo ' selected'; ?>><?php echo _('Mails'); ?></option>
					</select>
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="level" class="form-control">
						<option value="all"><?php echo _('All levels'); ?></option>
						<option value="0"<?php if($level_search == '0') echo ' selected'; ?>><?php echo _('Members'); ?></option>
						<option value="1"<?php if($level_search == '1') echo ' selected'; ?>><?php echo _('Uploaders'); ?></option>
						<option value="2"<?php if($level_search == '2') echo ' selected'; ?>><?php echo _('Official uploaders'); ?></option>
						<option value="3"<?php if($level_search == '3') echo ' selected'; ?>><?php echo _('Managers'); ?></option>
						<option value="4"<?php if($level_search == '4') echo ' selected'; ?>><?php echo _('Assistants Director'); ?></option>
						<option value="5"<?php if($level_search == '5') echo ' selected'; ?>><?php echo _('Administrators'); ?></option>
					</select>
				</div>
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php if(!empty($req)) { ?>
					<table class="table table-condensed table-hover" style="border-bottom:1px solid;margin:0">
					<?php if($ordre == 0 && $type == 0)
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=0&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Nickname').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 0)
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=0&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Nickname').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					else
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=0&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Nickname').'</a></th>';

					if($ordre == 0 && $type == 1 && isset($settings['level']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=1&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Access level').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 1 && isset($settings['level']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=1&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Access level').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['level']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=1&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Access level').'</a></th>';

					if($ordre == 0 && $type == 2 && isset($settings['last_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=2&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 2 && isset($settings['last_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=2&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['last_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=2&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Last visit').'</a></th>';

					if($ordre == 0 && $type == 7 && isset($settings['first_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=7&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 7 && isset($settings['first_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=7&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['first_visit']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=7&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('First visit').'</a></th>';

					if($ordre == 0 && $type == 3 && isset($settings['packages']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=3&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Packages').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 3 && isset($settings['packages']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=3&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Packages').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['packages']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=3&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Packages').'</a></th>';

					if($ordre == 0 && $type == 5 && isset($settings['udid']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=5&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">UDID(s)</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 5 && isset($settings['udid']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=5&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">UDID(s)</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['udid']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=5&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">UDID(s)</a></th>';

					if($ordre == 0 && $type == 9 && isset($settings['notification']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=9&ord=1&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Notification').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
					elseif($ordre == 1 && $type == 9 && isset($settings['notification']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=9&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Notification').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
					elseif(isset($settings['notification']))
						echo '<th style="border-color:#000 !important" class="text-center"><a href="?type=9&ord=0&level='.$level_search.'&champs='.$champ.'&s='.urlencode(stripslashes($search)).'">'._('Notification').'</a></th>';

					echo '<th style="border-color:#000 !important" class="text-center">'._('Action').'</th>';
					foreach ($req as $key) {
						if($key['level'] == 5)
							$level = _('Administrator');
						elseif($key['level'] === '4')
							$level = _('Assistant Director');
						elseif($key['level'] === '3')
							$level = _('Manager');
						elseif($key['level'] === '2')
							$level = _('Official uploader');
						elseif($key['level'] === '1')
							$level = _('Uploader');
						else
							$level = _('Member');
						echo '<tr>
							<td style="border-color:#000 !important" class="text-center">'.$key['pseudo'].'</td>';
						if(isset($settings['level']))
							echo '<td style="border-color:#000 !important" class="text-center">'.$level.'</td>';
						if(isset($settings['last_visit']))
							echo '<td style="border-color:#000 !important" class="text-center">'.date_format(date_create($key['last_date']), "d/m/Y - H:i").'</td>';
						if(isset($settings['first_visit']))
							echo '<td style="border-color:#000 !important" class="text-center">'.date_format(date_create($key['date']), "d/m/Y - H:i").'</td>';
						if(isset($settings['packages']))
							echo '<td style="border-color:#000 !important" class="text-center"><a href="/manage-all.php?s='.$key['pseudo'].'&champs=pseudo">'.print_number($key['package']).'</a></td>';
						if(isset($settings['udid']))
							echo '<td style="border-color:#000 !important" class="text-center">'.print_number($key['udid']).'</td>';
						if(isset($settings['notification']))
							echo '<td style="border-color:#000 !important" class="text-center">'.(empty($key['message_notification']) ? _('No') : _('Yes')).'</td>';
						if($key['level'] <= $membre->_level) {
							echo '<td style="border-color:#000 !important" class="text-center"><div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown">'._('Action').' <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right text-left" role="menu">
									<li><a class="btn-xs" href="/membre.php?id='.$key['id'].'"><span class="glyphicon glyphicon-edit"></span> '._('Edit a member').'</a></li>
									<li><a class="btn-xs" href="/membres.php?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&level='.$level_search.'&champs='.$champ.'&page='.$page.'&s='.urlencode(stripslashes($search)).'&id='.$key['id'].'&reset"><span class="glyphicon glyphicon-refresh"></span> '._('Reset password').'</a></li>';
									if($membre->_level > 4)
										echo '<li><a class="btn-xs" href="/membre-infos.php?id='.$key['id'].'"><span class="glyphicon glyphicon-stats"></span> '._('Informations').'</a></li>';
									echo '<li class="divider"></li>
									<li class="deconnexion"><a class="btn-xs" href="/delete-membre.php?id='.$key['id'].'"><span class="glyphicon glyphicon-trash"></span> '._('Delete').'</a></li>
								</ul>
							</div></td>';
						} else
							echo '<td style="border-color:#000 !important" class="text-center"><div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle btn-xs disabled" data-toggle="dropdown">'._('Action').' <span class="caret"></span></button>
							</div></td>';
						echo '</tr>';
					} ?>
					</table>
					<?php echo '<div class="text-center"><ul class="pagination">';
					if($page > 1)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&level='.$level_search.'&champs='.$champ.'&page=1&s='.urlencode(stripslashes($search)).'">1</a></li>';
					if($precedent > 2)
						echo '<li class="disabled"><a>...</a></li>';
					if($page > 2)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&level='.$level_search.'&champs='.$champ.'&page='.$precedent.'&s='.urlencode(stripslashes($search)).'">'.$precedent.'</a></li>';
					echo '<li class="active"><a>'.$page.'</a></li>';
					if($fin > $suivant)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&level='.$level_search.'&champs='.$champ.'&page='.$suivant.'&s='.urlencode(stripslashes($search)).'">'.$suivant.'</a></li>';
					if($fin > $suivant + 1)
						echo '<li class="disabled"><a>...</a></li>';
					if($fin >= $suivant)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&level='.$level_search.'&champs='.$champ.'&page='.$fin.'&s='.urlencode(stripslashes($search)).'">'.$fin.'</a></li>';
					echo '</ul></div>'; ?>
			<?php } else
				echo '<h2 class="text-center">'._('No member found !').'</h2>'; ?>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php  } else
	require_once('404.php'); ?>