<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected) {
		require_once("includes/time-header.php");
		$lang_user = translation();
		$errors = array();
		$success = array();
		$pdo = PDO2::getInstance();
		$udids = $pdo->prepare("SELECT udid FROM users WHERE membre = :id");
		$udids->execute(array(':id' => $membre->_id));
		$count_udids = $udids->rowCount();
		$udids = $udids->fetchAll(PDO::FETCH_ASSOC);
		$udid_ligne = '';
		$i = 0;
		foreach($udids as $udid) {
			$i++;
			if($i === $count_udids)
				$udid_ligne .= $udid['udid'];
			else
				$udid_ligne .= $udid['udid'].', ';
		}
		$user = $pdo->prepare("SELECT password, mail FROM membre WHERE id = :id");
		$user->execute(array(':id' => $membre->_id));
		$user = $user->fetch(PDO::FETCH_ASSOC);
		if(!empty($_POST['username']) && !empty($_POST['email'])) {
			if(strlen(htmlspecialchars(htmlentities(strip_tags(trim($_POST['username']))))) > 30)
				$errors[] = _('This nickname is too long !');
			elseif(strlen(htmlspecialchars(htmlentities(strip_tags(trim($_POST['username']))))) < 3)
				$errors[] = _('This nickname is too short !');
			elseif(!preg_match('/^[_a-zA-Z0-9_]+$/', htmlspecialchars(htmlentities(strip_tags(trim($_POST['username'])))))) 
				$errors[] = _('Nickname must not contain special characters except dash bottom (_).');
			else
				$pseudo = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['username'])))));

			if(strlen(htmlspecialchars(htmlentities(strip_tags(trim($_POST['email']))))) > 250)
				$errors[] = _('This mail is too long !');
			elseif(strlen(htmlspecialchars(htmlentities(strip_tags(trim($_POST['email']))))) < 6)
				$errors[] = _('This mail is too short !');
			elseif(!preg_match('/@.+\./', htmlspecialchars(htmlentities(strip_tags(trim($_POST['email'])))))) 
				$errors[] = _('Mail is invalid !');
			elseif(!filter_var(htmlspecialchars(htmlentities(strip_tags(trim($_POST['email'])))), FILTER_VALIDATE_EMAIL))
				$errors[] = _('Mail is invalid !');
			else
				$mail = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['email'])))));

			if(!empty($_POST['password_new']) && !empty($_POST['password_verif'])) {
				if(strlen(trim($_POST['password_new'])) < 6) {
					$errors[] = _('Your new password is too short, at least 6 characters !');
				} elseif(hash('sha512', hash('sha512', $_POST['password_new'])) != hash('sha512', hash('sha512', $_POST['password_verif']))) {
					$errors[] = _('Your new passwords do not match !');
				} else {
					$password_new = hash('sha512', hash('sha512', $_POST['password_new']));
				}
			} elseif(!empty($_POST['password_new']) || !empty($_POST['password_verif']))
				$errors[] = _('Enter the new password 2 times.');
			else
				$password_new = $user['password'];
			if(isset($_POST['newsletter']) && $_POST['newsletter'] == 'yes')
				$newsletter = 1;
			elseif(isset($_POST['newsletter']) && $_POST['newsletter'] == 'no')
				$newsletter = 0;
			else
				$errors[] = _('Choose whether you subscribe to the newsletter !');
			if(isset($_POST['udid']) && $_POST['udid'] != $udid_ligne) {
				$test_udids = explode(', ', htmlspecialchars(htmlentities(strip_tags($_POST['udid']))));
				if(!empty($_POST['udid'])) {
					$count_udid_test = count($test_udids);
					if($count_udid_test < 15) {
						foreach($test_udids as $test_udid) {
							if(strlen($test_udid) == 40) {
								$id_udid = $pdo->prepare("SELECT id, membre FROM users WHERE udid = :id");
								$id_udid->execute(array(':id' => $test_udid));
								$count_id_udid = $id_udid->rowCount();
								if($count_id_udid === 1) {
									$id_udid = $id_udid->fetch(PDO::FETCH_ASSOC);
									if($id_udid['membre'] == NULL) {
										$query = $pdo->prepare("INSERT INTO users (id, membre) VALUES (:id, :membre)
										ON DUPLICATE KEY UPDATE membre = :membre");
										$query->execute(array(':id' => $id_udid['id'], ':membre' => $membre->_id));
										$success[] = $test_udid.' '._('has been registered.');
									} elseif($id_udid['membre'] != $membre->_id)
										$errors[] = $test_udid.' '._('is already being used by another member.');
								} else
									$errors[] = $test_udid.' '._('does not exist or was never added our repo on Cydia.');
							} else
								$errors[] = _('UDID must do 40 characters.');
						}
					} else
						$errors[] = _('If you have really more than 15 devices, for security reason, send a UDID list by mail to admin please.');
				}
				if(empty($errors)) {
					foreach($udids as $udid) {
						$dontremove = false;
						foreach($test_udids as $test_udid) {
							if($udid['udid'] == $test_udid)
						$dontremove = true;
						}
						if($dontremove == false) {
							$query = $pdo->prepare("UPDATE users SET membre = NULL WHERE udid = :id");
							$query->execute(array(':id' => $udid['udid']));
							$success[] = $udid['udid'].' '._('has been removed.');
						}
					}
				}
				$udids = $pdo->prepare("SELECT udid FROM users WHERE membre = :id");
				$udids->execute(array(':id' => $membre->_id));
				$count_udids = $udids->rowCount();
				$udids = $udids->fetchAll(PDO::FETCH_ASSOC);
				$udid_ligne = '';
				$i = 0;
				foreach($udids as $udid) {
					$i++;
					if($i === $count_udids)
						$udid_ligne .= $udid['udid'];
					else
						$udid_ligne .= $udid['udid'].', ';
				}
			} elseif(!empty($_POST['udid']) && $_POST['udid'] != $udid_ligne)
				$errors[] = $_POST['udid'].' '._('UDID must do 40 characters.');

			if(empty($errors)) {
				global $pdo;
				$query = $pdo->prepare("SELECT id FROM membre WHERE pseudo = :pseudo");
				$query->execute(array(':pseudo' => $pseudo));
				$count = $query->rowCount();
				$query = $query->fetchColumn();

				$query2 = $pdo->prepare("SELECT id FROM membre WHERE mail = :mail");
				$query2->execute(array(':mail' => $mail));
				$count2 = $query2->rowCount();
				$query2 = $query2->fetchColumn();

				$query1 = $pdo->prepare("SELECT id FROM membre WHERE id = :id AND password = :password");
				$query1->execute(array(':id' => $membre->_id, ':password' => $user['password']));
				$count1 = $query1->rowCount();
				$query1 = $query1->fetchColumn();

				if($count > 0 && $query != $membre->_id)
					$errors[] = _('This nickname is already used by another member !');
				elseif($count2 > 0 && $query2 != $membre->_id)
					$errors[] = _('This mail is already used by another member !');
				elseif($count1 == 1 && $query1 == $membre->_id) {
					$query = $pdo->prepare("INSERT INTO membre (id, pseudo, mail, password, newsletter) VALUES (:id, :pseudo, :mail, :password, :newsletter)
					ON DUPLICATE KEY UPDATE pseudo = :pseudo, mail= :mail, password = :password, newsletter = :newsletter");
					$query->execute(array(':id' => $membre->_id, ':pseudo' => $pseudo, ':mail' => $mail, ':password' => $password_new, ':newsletter' => $newsletter));
					$user['mail'] = $mail;
					$user['newsletter'] = $newsletter;
					$membre->_pseudo = $pseudo;
					$encrypted = base64_encode($password_new);
					if($_COOKIE['autologin'])
						setrawcookie('autologin', $pseudo.'|'.$encrypted, time() + 3600 * 24 * 360, '/');
					$success[] = _('Your settings have been saved !');
				}
			}
		} elseif(isset($_POST['username']) || isset($_POST['email']))
			$errors[] = _('Please fill in all fields.');
		$pdo = PDO2::closeInstance();
		$site_nom = config('nom'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/style.min.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<title><?php echo _('Account settings'); ?> | <?php echo $site_nom; ?></title>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0" href="./"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0" href="login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto"><div class="lead">
			<h2 class="text-center"><span class="glyphicon glyphicon-cog"></span> <?php echo _('Account settings'); ?></h2><hr />
			<?php if(!empty($errors)) {
				echo '<div class="alert alert-danger alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
				foreach($errors as $error) {
					echo '<p>'.$error.'</p>';
				}
				echo '</div>';
			}
			if(!empty($success)) {
				echo '<div class="alert alert-success alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
				foreach($success as $succes) {
					echo '<p>'.$succes.'</p>';
				}
				echo '</div>';
			} ?>
			<form method="POST"><fieldset>
				<div class="form-group">
					<label for="username"><?php echo _('Nickname'); ?></label>
					<input type ="texte" class="form-control" name="username" id="username" placeholder="<?php echo _('Nickname'); ?>" value="<?php echo $membre->_pseudo; ?>" />
					<span class="help-text"><?php echo _('Nickname must not contain special characters except dash bottom (_).'); ?></span>
				</div>
				<div class="form-group">
					<label for="email"><?php echo _('Mail'); ?></label>
					<input type="email" class="form-control" name="email" id="email" placeholder="<?php echo _('Mail'); ?>" value="<?php echo $user['mail']; ?>" />
					<span class="help-text"><?php echo _('A mail to retrieve your password.'); ?></span>
				</div>
				<div class="form-group">
					<label for="password_new"><?php echo _('New password'); ?></label>
					<input type="password" class="form-control" name="password_new" id="password_new" placeholder="<?php echo _('New password'); ?>"/>
					<span class="help-text"><?php echo _('Leave blank to keep your current password.'); ?></span>
				</div>
				<div class="form-group">
					<label for="password_verif"><?php echo _('New password (verification)'); ?></label>
					<input type="password" class="form-control" name="password_verif" id="password_verif" placeholder="<?php echo _('New password (verification)'); ?>"/>
				</div><hr />
				<div class="form-group">
					<label for="udid"><?php echo _('UDID(s)\'s devices that you have, actually :'); ?> <?php echo $count_udids; ?></label>
					<textarea class="form-control" name="udid" id="udid"><?php echo $udid_ligne; ?></textarea>
					<span class="help-text"><?php echo _('To add more UDIDs, separate them with a comma and space (, ).'); ?></span>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="newsletter"><?php echo _('Newsletter'); ?></label>
					<div class="col-lg-10 text-center">
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-primary<?php if (!empty($user['newsletter'])) echo ' active'; ?>">
								<input type="radio" name="newsletter" id="newsletter" value="yes"<?php if (!empty($user['newsletter'])) echo ' checked="checked"'; ?>> <?php echo _('Yes'); ?>
							</label>
							<label class="btn btn-danger<?php if (empty($user['newsletter'])) echo ' active'; ?>">
								<input type="radio" name="newsletter" id="newsletter" value="no"<?php if (empty($user['newsletter'])) echo ' checked="checked"'; ?>> <?php echo _('No'); ?>
							</label>
						</div>
						<span class="help-block text-left"><?php echo _('To stay informed about news, no more than one email per month.'); ?></span>
					</div>
				</div>
				<div class="text-center">
					<button type="submit" class=" btn btn-primary btn-lg"><?php echo _('Save') ?></button>
				</div>
			</fieldset></form>
		</div></div>
		<?php require_once('includes/front/footer.php'); ?>
	</body>
</html>
<?php } else
	header('Location: login.php'); ?>