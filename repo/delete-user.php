<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 4) {
	require_once('includes/time-header.php');
	translation();
	$errors = array();
	$success = array();
	$pdo = PDO2::getInstance();

	if(is_array($_GET['id'])) {
		foreach($_GET['id'] as $id) {
			$req = $pdo->prepare("SELECT udid FROM users WHERE id = :id");
			$req->execute(array(':id' => $id));
			$verif = $req->fetch(PDO::FETCH_ASSOC);
			$req->closeCursor();
			if($verif) {
				if(isset($_POST['delete'])) {
					$req = $pdo->prepare("UPDATE download SET user = NULL WHERE user = :id");
					$req->execute(array(':id' => $id));
					$req->closeCursor();
					$req = $pdo->prepare('DELETE FROM users WHERE id = :id');
					$req->execute(array(':id' => $id));
					$req->closeCursor();
					$success[] = sprintf(_('UDID %s has been removed'), $verif['udid']).' <a class="btn btn-xs" href="users.php">'._('Manage UDIDs').'</a>';
					unset($verif);
				} else
					$errors[] = sprintf(_('Do you want to remove %s ?'), $verif['udid']);
			} else
				$errors[] = _('UDID not found !').' <a class="btn btn-xs" href="users.php">'._('Manage UDIDs').'</a>';
		}
		unset($id);
	} else {
		$req = $pdo->prepare("SELECT udid FROM users WHERE id = :id");
		$req->execute(array(':id' => $_GET['id']));
		$verif = $req->fetch(PDO::FETCH_ASSOC);
		$req->closeCursor();
		if($verif) {
			if(isset($_POST['delete'])) {
				$req = $pdo->prepare("UPDATE download SET user = NULL WHERE user = :id");
				$req->execute(array(':id' => $_GET['id']));
				$req->closeCursor();
				$req = $pdo->prepare('DELETE FROM users WHERE id = :id');
				$req->execute(array(':id' => $_GET['id']));
				$req->closeCursor();
				$success[] = sprintf(_('UDID %s has been removed'), $verif['udid']).' <a class="btn btn-xs" href="users.php">'._('Manage UDIDs').'</a>';
				unset($verif);
			} else
				$errors[] = sprintf(_('Do you want to remove %s ?'), $verif['udid']);
		} else
			$errors[] = _('UDID not found !').' <a class="btn btn-xs" href="users.php">'._('Manage UDIDs').'</a>';
	}
	$site_nom = config('nom');
	$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Delete an UDID').' - '. $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Delete an UDID'); ?></h2>
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
				<div class="row marketing text-center">
					<div class="form-group">
						<label for="delete" class="control-label"><?php echo _('Delete UDID and downloads'); ?> :</label><br />
						<input class="btn btn-danger btn-lg" type="submit" name="delete" value='<?php echo _('Confirm'); ?>' />
						<a class="btn btn-lg btn-primary" href="users.php"><?php echo _('Cancel'); ?></a>
					</div>
				</div>
			</fieldset></form>
		<?php } ?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>