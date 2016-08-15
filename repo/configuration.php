<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 4) {
	require_once('includes/time-header.php');
	translation();
	$success = array();
	$errors = array();
	if(isset($_POST['url'])) {
		if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $_POST['url']))
			$errors[] = 'URL incorrecte !';
		else
			$url = trim($_POST['url']);
		if(!preg_match('/^[a-zA-Z0-9_ -]+$/', $_POST['nom']))
			$errors[] = 'Nom incorrecte !';
		else
			$nom = trim($_POST['nom']);
	}

	if(!empty($url) && !empty($nom) && empty($errors)) {
		$release_text = '<?php global $config;
$config["url"] = "'.$url.'";
$config["nom"] = "'.$nom.'";';
		$release_handle = fopen("includes/config.php","w");
		fputs($release_handle,stripslashes($release_text));
		fclose($release_handle);
		$success[] = "Vos réglages ont bien été appliqués !";
		if(isset($_POST['update-pack']) && $_POST['update-pack'] == true) {
			if(file_exists('includes/cache/Packages.bz2'))
				unlink('includes/cache/Packages.bz2');
			$success[] = "En cache Cydia mis à jour avec succès !";
		}
	}
	require_once('includes/config.php');
	$site_nom = $config['nom']; ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Configuration'); ?> - <?php echo $config['nom']; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Configuration'); ?></h2>
		</div>
		<?php if(!empty($success)) {
			echo "<div class='alert alert-success alert-dismissable fade in'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
			foreach($success as $succes) {
				echo '<p>'.$succes.'</p>';
			}
			echo '</div>';
		}
		if(!empty($errors)) {
			echo "<div class='alert alert-danger alert-dismissable fade in'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
			foreach($errors as $error) {
				echo '<p>'.$error.'</p>';
			}
			echo '</div>';
		} ?>
		<form class="jumbotron form-horizontal" method="POST" action="?token=<?php echo $membre->_token; ?>"><fieldset>
			<input type="hidden" name="url" id="url" value="<?php if (!empty($config['url'])) {echo $config['url'];}else{echo 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, -17); } ?>" />
			<div class="form-group">
				<label class="col-sm-2 control-label" for="nom">Nom du site</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" required="required" name="nom" id="nom" value="<?php if (!empty($config['nom'])) {echo $config['nom'];} ?>"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
						<label for="update-pack"><input type="checkbox" name="update-pack" id="update-pack" value="true" /><div class="switch"></div>Mettre à jour le cache de Cydia</label>
					</div>
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