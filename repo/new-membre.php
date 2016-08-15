<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 1) {
		require_once('includes/time-header.php');
		translation();
		$errors = array();
		$success = array();
		if(!empty($_POST['pseudo']) && !empty($_POST['mail'])) {
			$pdo = PDO2::getInstance();
			if(strlen(trim(addslashes(htmlspecialchars($_POST['pseudo'])))) > 20)
				$errors[] = _('This nickname is too long !');
			elseif(strlen(addslashes(htmlspecialchars(trim($_POST['pseudo'])))) < 3)
				$errors[] = _('This nickname is too short !');
			else {
				$query1 = $pdo->prepare("SELECT id FROM membre WHERE pseudo = :pseudo");
				$query1->execute(array(':pseudo' => $_POST['pseudo']));
				$count1 = $query1->rowCount();
				$query1 = $query1->fetchColumn();
				if($count1 === 0) {
					$pseudo = addslashes(htmlspecialchars(trim($_POST['pseudo'])));
				} else {
					$errors[] = _('This nickname is already used by another member !');
				}
			}
			if(!preg_match('/@.+\./', $_POST['mail']))
				$errors[] = _('Mail is invalid !');
			elseif(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
				$errors[] = _('Mail is invalid !');
			elseif(strlen($_POST['mail']) > 250)
				$errors[] = _('This mail is too long !');
			elseif(strlen($_POST['mail']) < 5)
				$errors[] = _('This mail is too short !');
			else {
				$query1 = $pdo->prepare("SELECT id FROM membre WHERE mail = :mail");
				$query1->execute(array(':mail' => $_POST['mail']));
				$count1 = $query1->rowCount();
				$query1 = $query1->fetchColumn();
				if($count1 === 0)
					$mail = $_POST['mail'];
				else
					$errors[] = _('This mail is already used by another member !');
			}

			if(isset($_POST['level']) && is_numeric($_POST['level']) && $_POST['level'] < $membre->_level)
				$level = $_POST['level'];
			else
				$errors[] = _('Level can not be equal or superior to yours !');

			if(trim($_POST['password']) != trim($_POST['password_verif']))
				$errors[] = _('Passwords does not match !');
			elseif(strlen(trim($_POST['password'])) < 5)
				$errors[] = _('Password is too short !');
			else
				$password = hash('sha512', hash('sha512', $_POST['password']));

			if(empty($errors)) {
				$query = $pdo->prepare("INSERT INTO membre (pseudo, mail, password, level, date) VALUES (:pseudo, :mail, :password, :level, CURRENT_TIMESTAMP)");
				$query->execute(array(':pseudo' => $pseudo, ':mail' => $mail, ':level' => $level, ':password' => $password));
				$success[] = _('Member added successfully !');
			}
			$pdo = PDO2::closeInstance();
		} elseif(isset($_POST['pseudo']) || isset($_POST['level']))
			$errors[] = _('Please fill in all fields.');
		$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Add a member'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Add a member'); ?></h2>
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
		<form class="jumbotron form-horizontal" method="POST"><fieldset>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="pseudo"><?php echo _('Nickname'); ?></label>
				<div class="col-sm-10">
					<input type="pseudo" class="form-control" name="pseudo" id="pseudo" placeholder="<?php echo _('Nickname'); ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="mail"><?php echo _('Mail'); ?></label>
				<div class="col-sm-10">
					<input type="pseudo" class="form-control" name="mail" id="mail" placeholder="<?php echo _('Mail'); ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="level"><?php echo _('Access level'); ?></label>
				<div class="col-sm-10">
					<select class="form-control" name="level" required="required">
						<option value="NULL"><?php echo _('Choose a level'); ?></option>
						<option value="0"><?php echo _('Member'); ?></option>
						<option value="1"><?php echo _('Simple uploader'); ?></option>
						<?php if($membre->_level > 2) { ?>
						<option value="2"><?php echo _('Official uploader'); ?></option>
						<?php }
						if($membre->_level > 3) { ?>
							<option value="3"><?php echo _('Manager'); ?></option>
						<?php }
						if($membre->_level > 4) { ?>
							<option value="4"><?php echo _('Assistant Director'); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="password"><?php echo _('Password'); ?></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="password" id="password" placeholder="<?php echo _('Password'); ?>"/></br>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="password_verif"><?php echo _('Password'); ?></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="password_verif" id="password_verif" placeholder="<?php echo _('Password'); ?>"/></br>
					<span class="help-block"><?php echo _('Please enter the password twice to avoid typing errors.'); ?></span>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary btn-block"><?php echo _('Save'); ?></button>
			</div>
		</fieldset></form>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>