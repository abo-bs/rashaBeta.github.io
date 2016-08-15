<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 3) {
		require_once('includes/time-header.php');
		translation();
		if(empty($_GET['id']) || !is_numeric($_GET['id']))
			header('Location: membres.php');
		$errors = array();
		$success = array();
		$pdo = PDO2::getInstance();
		$verif = $pdo->prepare("SELECT pseudo, mail, level, password FROM membre WHERE id = :id");
		$verif->execute(array(':id' => $_GET['id']));
		$verif = $verif->fetch(PDO::FETCH_ASSOC);
		$udids = $pdo->prepare("SELECT udid FROM users WHERE membre = :id");
		$udids->execute(array(':id' => $_GET['id']));
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
		if($verif) {
			if($verif['level'] < $membre->_level) {
				if(!empty($_POST['username']) && isset($_POST['level']) && !empty($_POST['mail'])) {
					if(strlen(trim($_POST['username'])) > 30)
						$errors[] = _('This nickname is too long !');
					elseif(strlen($_POST['username']) < 3)
						$errors[] = _('This nickname is too short !');
					elseif(!preg_match('`^\w{3,30}$`', $_POST['username'])) 
						$errors[] = _('Nickname must not contain special characters except dash bottom (_).');
					else
						$pseudo = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['username'])))));

					if(strlen(trim($_POST['mail'])) > 250)
						$errors[] = _('This mail is too long !');
					elseif(strlen($_POST['mail']) < 6)
						$errors[] = _('This mail is too short !');
					elseif(!preg_match('/@.+\./', $_POST['mail'])) 
						$errors[] = _('Mail is invalid !');
					elseif(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
						$errors[] = _('Mail is invalid !');
					else
						$mail = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['mail'])))));

					if(!empty($_POST['password_new'])) {
						if(strlen(trim($_POST['password_new'])) < 6)
							$errors[] = _('Your new password is too short, at least 6 characters !');
						else
							$password_new = hash('sha512', hash('sha512', $_POST['password_new']));
					} else
						$password_new = $verif['password'];
					if(isset($_POST['udid']) && $_POST['udid'] != $udid_ligne) {
						$test_udids = explode(', ', htmlspecialchars(htmlentities(strip_tags($_POST['udid']))));
						if(!empty($_POST['udid'])) {
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
											$query->execute(array(':id' => $id_udid['id'], ':membre' => $_GET['id']));
											$success[] = $test_udid.' '._('has been registered.');
										} elseif($id_udid['membre'] != $_GET['id'])
											$errors[] = $test_udid.' '._('is already being used by another member.');
									} else
										$errors[] = $test_udid.' '._('does not exist or was never added our repo on Cydia.');
								} else
									$errors[] = _('UDID must do 40 characters.');
							}
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
						$udids->execute(array(':id' => $_GET['id']));
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

					if(isset($_POST['level']) && is_numeric($_POST['level']) && $_POST['level'] < $membre->_level)
						$level = $_POST['level'];
					else
						$errors[] = _('Level can not be equal or superior to yours !');

					if(empty($errors)) {
						$query = $pdo->query("SELECT id FROM membre WHERE pseudo = '".$pseudo."'");
						$count = $query->rowCount();
						$query = $query->fetchColumn();
						$query1 = $pdo->query("SELECT id FROM membre WHERE mail = '".$mail."'");
						$count1 = $query1->rowCount();
						$query1 = $query1->fetchColumn();

						if($count > 0 && $query != $_GET['id'])
							$errors[] = _('This nickname is already used by another member !');
						elseif($count1 > 0 && $query1 != $_GET['id'])
							$errors[] = _('This mail is already used by another member !');
						else {
							$query = $pdo->prepare("INSERT INTO membre (id, pseudo, mail, level, password) VALUES (:id, :pseudo, :mail, :level, :password)
							ON DUPLICATE KEY UPDATE pseudo = :pseudo, mail = :mail, level = :level, password = :password");
							$query->execute(array(':id' => $_GET['id'], ':pseudo' => $pseudo, ':mail' => $mail, ':level' => $level, ':password' => $password_new));
							$verif['pseudo'] = $pseudo;
							$verif['mail'] = $mail;
							$verif['level'] = $level;
							$success[] = _('Your settings have been saved !');
						}
					}
				} elseif(isset($_POST['username']) || isset($_POST['level']) || isset($_POST['mail'])) {
					$errors[] = _('Please fill in all fields.');
				}
			} else {
				$errors[] = _('You are not allowed to edit this member.');
				unset($verif);
			}
		}
		$site_nom = config('nom');
		$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Edit a member').' - '.$site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Edit a member'); ?></h2>
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
			foreach($success as $succes) {
				echo '<p>'.$succes.'</p>';
			}
			echo '</div>';
		} ?>
		<?php if($verif) { ?>
				<form class="jumbotron form-horizontal" method="POST"><fieldset>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="username"><?php echo _('Nickname'); ?></label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="username" id="username" placeholder="<?php echo _('Nickname'); ?>" value="<? echo $verif['pseudo']; ?>" />
							<span class="help-text"><?php echo _('Nickname must not contain special characters except dash bottom (_).'); ?></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="mail"><?php echo _('Mail'); ?></label>
						<div class="col-sm-10">
							<input type="email" class="form-control" name="mail" id="mail" placeholder="<?php echo _('Mail'); ?>" value="<? echo $verif['mail']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="level"><?php echo _('Access level'); ?></label>
						<div class="col-sm-10">
							<select class="form-control" name="level" required="required">
								<option value="NULL" <?php if($verif['level'] == '' || $verif['level'] == NULL){echo 'selected';} ?>><?php echo _('Choose a level'); ?></option>
								<option value="0" <?php if($verif['level'] == 0){echo 'selected="selected"';} ?>><?php echo _('Member'); ?></option>
								<option value="1" <?php if($verif['level'] == 1){echo 'selected="selected"';} ?>><?php echo _('Uploader'); ?></option>
								<option value="2" <?php if($verif['level'] == 2){echo 'selected="selected"';} ?>><?php echo _('Official uploader'); ?></option>
								<?php if($membre->_level > 3) { ?>
									<option value="3" <?php if($verif['level'] == 3){echo 'selected="selected"';} ?>><?php echo _('Manager'); ?></option>
								<?php } ?>
								<?php if($membre->_level > 4) { ?>
									<option value="4" <?php if($verif['level'] == 4){echo 'selected="selected"';} ?>><?php echo _('Assistant Director'); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="udid">UDID(s)</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="udid" id="udid" placeholder="UDID(s)" value="<? echo $udid_ligne; ?>" />
							<span class="help-text"><?php echo _('To add more UDIDs, separate them with a comma and space (, ).').' '._('Current'); ?> : <b><?php echo $count_udids; ?></b></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="password_new"><?php echo _('New password'); ?></label>
						<div class="col-sm-10">
							<input type="password" class="form-control" name="password_new" id="password_new" placeholder="<?php echo _('New password'); ?>"/>
							<span class="help-text"><?php echo _('Leave blank to keep your current password.'); ?></span>
						</div>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary btn-block"><?php echo _('Save'); ?></button>
					</div>
				</fieldset></form>
		<?php } else { ?>
			<div class="jumbotron">
				<p class="text-center"><?php echo _('No member found !'); ?></p>
			</div>
		<?php }?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>