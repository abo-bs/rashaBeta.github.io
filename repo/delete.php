<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	require_once('includes/package.class.php');
	translation();
	$errors = array();
	$success = array();
	$verification = false;
	if(is_array($_GET['id'])) {
		$ids = $_GET['id'];
		$paquets = array();
		foreach($ids as $id) {
			$paquets[] = new Paquet($id);
		}
		if(!isset($_POST['Delete']) && !isset($_POST['Annuler'])) {
			foreach($paquets as $paquet) {
				if(!$paquet->verifier_fiche())
					$errors[] = _('This package does not exist !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
				elseif($membre->_level < 3 && $paquet->package_control('id_membre') != $membre->_id)
					$errors[] = _('You are not allowed to edit this package !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
				else {
					$verification = true;
					$errors[] = sprintf(_('Do you want to remove %s ?'), $paquet->package_control('Name'));
				}
			}
		} elseif(isset($_POST['Delete']) && $_POST['token'] == $membre->_token) {
			foreach($paquets as $paquet) {
				if(!$paquet->verifier_fiche() && !$paquet->verifier_deb())
					$errors[] = _('This package does not exist !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
				elseif($membre->_level < 3 && $paquet->package_control('id_membre') != $membre->_id)
					$errors[] = _('You are not allowed to edit this package !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
				if(empty($errors)) {
					$paquet->supprimer_definitivement();
					if(file_exists('includes/cache/Packages.bz2'))
						unlink('includes/cache/Packages.bz2');
					array_map('unlink', glob("includes/cache/*.xml"));
					array_map('unlink', glob("includes/cache/*.txt"));
					$success[] = _('Package permanently deleted ! <a class="btn btn-xs" href="manage-all.php">Back to packages</a>');
				}
			}
		} else
			header("Location: manage-all.php");
	} else {
		$idverif = trim($_GET['id']);
		$paquet = new Paquet($idverif);
		if(!isset($_POST['Delete']) && !isset($_POST['Annuler'])) {
			if(!$paquet->verifier_deb())
				$errors[] = _('This package does not exist !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
			elseif($membre->_level < 3 && $paquet->package_control('id_membre') != $membre->_id)
				$errors[] = _('You are not allowed to edit this package !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
			else {
				$verification = true;
				$errors[] = sprintf(_('Do you want to remove %s ?'), $idverif);
			}
		} elseif(isset($_POST['Delete']) && $_POST['token'] == $membre->_token) {
			if(!$paquet->verifier_fiche() && !$paquet->verifier_deb())
				$errors[] = _('This package does not exist !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
			elseif($membre->_level < 3 && $paquet->package_control('id_membre') != $membre->_id)
				$errors[] = _('You are not allowed to edit this package !').' <a class="btn btn-xs" href="manage-all.php">'._('Manage packages').'</a>';
			if(empty($errors)) {
				$paquet->supprimer_definitivement();
				if(file_exists('includes/cache/Packages.bz2'))
					unlink('includes/cache/Packages.bz2');
				array_map('unlink', glob("includes/cache/*.xml"));
				array_map('unlink', glob("includes/cache/*.txt"));
				$success[] = _('Package permanently deleted ! <a class="btn btn-xs" href="manage-all.php">Back to packages</a>');
			}
		} else
			header("Location: manage-all.php");
	}
	$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Delete a package'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<h2 class="text-primary"><?php echo _('Delete a package'); ?></h2>
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
		<?php if(isset($_GET['id']) && (!isset($_POST['Delete'])) && $verification == true){ ?>
	                <form class="jumbotron form-horizontal" method="post"><fieldset>
				<div class="form-group text-center">
					<input type="hidden" name="token" value="<?php echo $membre->_token; ?>" />
					<input class="btn btn-danger" type="submit" name="Delete" value="<?php echo _('Delete permanently'); ?>" />
					<input class="btn btn-primary" type="submit" name="Annuler" value='<?php echo _('Cancel'); ?>' />
				</div>
		        </fieldset></form>
		<?php } ?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>