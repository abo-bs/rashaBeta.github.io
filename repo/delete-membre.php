<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 4) {
		require_once('includes/time-header.php');
		require_once('includes/package.class.php');
		translation();
		if(empty($_GET['id']) || !is_numeric($_GET['id']))
			header('Location: membres.php');
		$errors = array();
		$success = array();
		$pdo = PDO2::getInstance();

		$verif = $pdo->prepare("SELECT pseudo, level FROM membre WHERE id = :id");
		$verif->execute(array(':id' => $_GET['id']));
		$verif = $verif->fetch(PDO::FETCH_ASSOC);
		if($verif) {
			if($verif['level'] < $membre->_level) {
				if(isset($_POST['Delete'])) {
					$test_delete = $pdo->prepare('SELECT id FROM description_meta WHERE id_membre = :id');
					$test_delete->execute(array(':id' => $_GET['id']));
					$test_delete = $test_delete->fetchAll(PDO::FETCH_ASSOC);
					$i = 0;
					foreach($test_delete as $pack_delete) {
						$pack_delete = new Paquet($pack_delete['id']);
						$pack_delete->supprimer_definitivement();
						$i++;
					}
					$udid_deletes = $pdo->prepare('SELECT id FROM users WHERE membre = :id');
					$udid_deletes->execute(array(':id' => $_GET['id']));
					$udid_deletes = $udid_deletes->fetchAll(PDO::FETCH_ASSOC);
					foreach($udid_deletes as $udid_delete) {
						$udid_remove= $pdo->prepare("UPDATE users SET membre = NULL WHERE id = :id");
						$udid_remove->execute(array(':id' => $udid_delete['id']));
					}
					$delete = $pdo->prepare('DELETE FROM membre WHERE id = :id');
					$delete->execute(array(':id' => $_GET['id']));
					if($i > 0)
						$success[] = sprintf(_('Member %s was removed and %s package(s) has been removed.'), $verif['pseudo'], $i).' <a class="btn btn-xs" href="/membres.php">'._('Manage members').'</a>';
					else
						$success[] = sprintf(_('Member %s was removed.'), $verif['pseudo']).' <a class="btn btn-xs" href="/membres.php">'._('Manage members').'</a>';
					unset($verif);
					if(file_exists('includes/cache/totalMembre.txt'))
						unlink('includes/cache/totalMembre.txt');
				} elseif(isset($_POST['Edit']) && isset($_POST['pseudo'])) {
					$test_edit = $pdo->prepare('SELECT COUNT(id) FROM membre WHERE id = :pseudo');
					$test_edit->execute(array(':pseudo' => $_POST['pseudo']));
					$test_edit = $test_edit->fetchColumn();
					if(empty($_POST['pseudo']) || $_POST['pseudo'] === 'Choisissez un membre')
						$errors[] = _('Please specify a member.');
					elseif($test_edit == 1) {
						$test_delete = $pdo->prepare('SELECT id FROM description_meta WHERE id_membre = :id');
						$test_delete->execute(array(':id' => $_GET['id']));
						$test_delete = $test_delete->fetchAll(PDO::FETCH_ASSOC);
						$i = 0;
						foreach($test_delete as $pack_edit) {
							$pack_edit = new Paquet($pack_edit ['id']);
							$pack_edit ->changer_control($_POST['pseudo'], 'id_membre');
							$i++;
						}
						$udid_deletes = $pdo->prepare('SELECT id FROM users WHERE membre = :id');
						$udid_deletes->execute(array(':id' => $_GET['id']));
						$udid_deletes = $udid_deletes->fetchAll(PDO::FETCH_ASSOC);
						foreach($udid_deletes as $udid_delete) {
							$udid_remove= $pdo->prepare("UPDATE users SET membre = NULL WHERE id = :id");
							$udid_remove->execute(array(':id' => $udid_delete['id']));
						}
						$delete = $pdo->prepare('DELETE FROM membre WHERE id = :id');
						$delete->execute(array(':id' => $_GET['id']));
						$success[] = sprintf(_('Member %s was removed and %s package(s) has been edited.'), $verif['pseudo'], $i).' <a class="btn btn-xs" href="/membres.php">'._('Manage members').'</a>';
						unset($verif);
						if(file_exists('includes/cache/totalMembre.txt'))
							unlink('includes/cache/totalMembre.txt');
					} else
						$errors[] = _('No member found !');
				} elseif(isset($_POST['Edit']) || isset($_POST['pseudo']))
					$errors[] = _('Please specify a member.');
				$total_pack = $pdo->prepare("SELECT COUNT(id) FROM description_meta WHERE id_membre = :id");
				$total_pack->execute(array(':id' => $_GET['id']));
				$total_pack = $total_pack->fetchColumn();
				$all_users = $pdo->prepare("SELECT id, pseudo FROM membre WHERE id != :id");
				$all_users->execute(array(':id' => $_GET['id']));
				$all_users = $all_users->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$errors[] = 'Vous n\'êtes pas autorisé à modifier ce membre !';
				unset($verif);
			}
		} else {
			header('Location: membres.php');
			exit;
		}
		$site_nom = config('nom');
		$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Delete a member').' - '.$site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Delete a member'); ?></h2>
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
				<p class="text-center"><?php echo _('You have chosen to remove member :'); ?> <?php echo $verif['pseudo'].' - ID : '.$_GET['id']; ?><br />
				<?php if($total_pack > 0) { ?>
				<?php echo _('What to do with his packages'); ?> (<?php echo $total_pack; ?>) ?</p>
				<div class="row marketing text-center">
					<div class="col-lg-6">
						<div class="form-group">
							<label for="pseudo" class="control-label"><?php echo _('Assign packages to :'); ?></label>
							<select name="pseudo" class="form-control">
								<option><?php echo _('Choose a member'); ?></option>
								<?php foreach($all_users as $user) {echo '<option value="'.$user['id'].'">'.$user['pseudo'].'</option>';} ?>
							<select>
						</div>
						<div class="form-group">
							<input class="btn btn-warning" type="submit" name="Edit" value="<?php echo _('Edit and remove'); ?>" />
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label for="Delete" class="control-label">Supprimer l'utilisateur et le contenu :</label><br />
							<input class="btn btn-danger btn-lg" type="submit" name="Delete" value="<?php echo _('Remove permanently'); ?>" />
							<a href="membres.php" class="btn btn-primary btn-lg"><?php echo _('Cancel'); ?></a>
						</div>
					</div>
				</div>
				<?php } else { ?></p>
				<div class="row marketing text-center">
					<div class="form-group">
						<input class="btn btn-danger btn-lg" type="submit" name="Delete" value="<?php echo _('Remove permanently'); ?>" />
							<a href="membres.php" class="btn btn-primary btn-lg"><?php echo _('Cancel'); ?></a>
					</div>
				</div>
				<?php } ?>
			</fieldset></form>
		<?php } ?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>