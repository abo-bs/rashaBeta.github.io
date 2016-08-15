<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 4) {
		require_once('includes/time-header.php');
		translation();
		$errors = array();
		$success = array();
		if(isset($_GET['token']) && $_GET['token'] == $membre->_token) {
			if(!empty($_FILES['images']) && !empty($_FILES['images']['name'])) {
			 	if((empty($_FILES['images']['type'])) || (!in_array($_FILES['images']['type'], array('image/png')))) {
					$errors[] = 'Veuillez choisir une image png !';
			        } else {
					switch($_FILES['images']['type']){
						case "image/png": $patch_type = 'png';
						break;
						default: $patch_type = 'error';
						break;
					}
				}
				if(!is_uploaded_file($_FILES['images']['tmp_name'])) {$errors[] = 'Veuillez poster le fichier via le formulaire !';}
				if($patch_type == 'error') {$errors[] = 'Veuillez choisir une image png !';}
				if ($_FILES['images']['error']) {
					switch ($_FILES['images']['error']){
						case 1: // UPLOAD_ERR_INI_SIZE
						$errors[] ="Le fichier dépasse la limite autorisée par le serveur !";
						break;
						case 2: // UPLOAD_ERR_FORM_SIZE
						$errors[] = "Le fichier dépasse la limite autorisée dans le formulaire HTML !";
						break;
						case 3: // UPLOAD_ERR_PARTIAL
						$errors[] = "L'envoi du fichier a été interrompu pendant le transfert !";
						break;
						case 4: // UPLOAD_ERR_NO_FILE
						$errors[] = "Le fichier que vous avez envoyé a une taille nulle !";
						break;
					}
				}
				if(empty($errors)) {
					if(file_exists('CydiaIcon.png')) {
						unlink('CydiaIcon.png');
					}
					move_uploaded_file($_FILES["images"]["tmp_name"], "CydiaIcon.$patch_type");
					$success[] = 'Image ajoutée avec succès !';
				}
			}
			if(!empty($_POST['origin']) && !empty($_POST['label']) && !empty($_POST['version']) && !empty($_POST['description'])) {
				$release_text = "Origin: ".$_POST['origin'];
				$release_text .= "\nLabel: ".$_POST['label'];
				$release_text .= "\nSuite: stable";
				$release_text .= "\nVersion: ".$_POST['version'];
				$release_text .= "\nCodename: ios";
				$release_text .= "\nArchitectures: iphoneos-arm";
				$release_text .= "\nComponents: main";
				$release_text .= "\nDescription: ".$_POST['description'];
				chmod("Release", 0755);
				$release_handle = fopen("Release","w");
				fputs($release_handle,stripslashes($release_text)."\n");
				fclose($release_handle);
				chmod("Release", 0444);
				$success[] = 'Vos réglages ont bien été appliqués !';
			}
		}
		if (file_exists("Release")) {
			$release_file = file("Release");
			$release = array();
			foreach ($release_file as $line) {
				if(preg_match("#^Origin|Label|Version|Description#", $line)) {
					$release[trim(preg_replace("#^(.+): (.+)#","$1", $line))] = trim(preg_replace("#^(.+): (.+)#","$2", $line));
				}
			}
		}
		$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title>Paramètres de la source - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary">Paramètres de la source<?php if(file_exists('CydiaIcon.png')) echo ' <img  width="50" height="50" src="CydiaIcon.png" />'; ?></h2>
		</div>
		<?php if(!empty($errors)) {
			echo '<div class="alert alert-danger alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
			foreach($errors as $error) {
				echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$error.'</div>';
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
		<form class="jumbotron form-horizontal" method="POST" action="release.php?token=<?php echo $membre->_token; ?>" enctype="multipart/form-data"><fieldset>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="images">Icône de la source :</label>
				<div class="col-sm-10">
					<input type="file" class="form-control" name="images" id="images" />
					<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
					<span class="help-block">Image au format png et recommandé en 120x120.</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="origin">Nom de la source</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" required="required" name="origin" id="origin" value="<?php if (!empty($release['Origin'])) {echo $release['Origin'];} ?>"/>
					<span class="help-block">Ceci est utilisé par Cydia en tant que nom de la source dans l'éditeur des sources.</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="label">Nom court</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" required="required" name="label" id="label" value="<?php if (!empty($release['Label'])) {echo $release['Label'];} ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="description">Description</label>
				<div class="col-sm-10">
	 				<input class="form-control" type="text" required="required" name="description" id="description" value="<?php if (!empty($release['Description'])) {echo $release['Description'];} ?>"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="version">Version</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" required="required" name="version" id="version" value="<?php if (!empty($release['Version'])) {echo $release['Version'];} ?>"/>
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