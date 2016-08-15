<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 0) {
		require_once('includes/time-header.php');
		require_once('includes/package.class.php');
		$lang_user = translation();
		function darkroom($img, $width = 0, $height = 0){
			ini_set('memory_limit', -1);
			$dimensions = @getimagesize($img);
			if($dimensions[0] == 0 || $dimensions[1] == 0)
				return false;
			$ratio = $dimensions[0] / $dimensions[1];
			if($width <= $dimensions[0]) {
				if($width == 0 && $height == 0){
					$width = $dimensions[0];
					$height = $dimensions[1];
				}elseif($height == 0)
					$height = round($width / $ratio);
				elseif ($width == 0)
					$width = round($height * $ratio);
				if($dimensions[0] && ($width / $height) * $dimensions[1]){
					$dimY = $height;
					$dimX = @round($height * $dimensions[0] / $dimensions[1]);
					$decalX = ($dimX - $width) / 2;
					$decalY = 0;
				}
				if($dimensions[0] && ($width / $height) * $dimensions[1]){
					$dimX = $width;
					$dimY = @round($width * $dimensions[1] / $dimensions[0]);
					$decalY = ($dimY - $height) / 2;
					$decalX = 0;
				}
				if($dimensions[0] == ($width / $height) * $dimensions[1]){
					$dimX = $width;
					$dimY = $height;
					$decalX = 0;
					$decalY = 0;
				}
				$pattern = @imagecreatetruecolor($width, $height);
				$type = @mime_content_type($img);
				switch (substr($type, 6)) {
					case 'jpeg':
						$image = @imagecreatefromjpeg($img);
						@imagealphablending($pattern, false);
						@imagesavealpha($pattern, true);
						@imagealphablending($image, true);
						@imagecopyresampled($pattern, $image, 0, 0, 0, 0, $dimX, $dimY, $dimensions[0], $dimensions[1]);
						@imagedestroy($image);
						@imagejpeg($pattern, $img, 100);
						break;
					case 'jpg':
						$image = @imagecreatefromjpg($img);
						@imagealphablending($pattern, false);
						@imagesavealpha($pattern, true);
						@imagealphablending($image, true);
						@imagecopyresampled($pattern, $image, 0, 0, 0, 0, $dimX, $dimY, $dimensions[0], $dimensions[1]);
						@imagedestroy($image);
						@imagejpeg($pattern, $img, 100);
						break;
					case 'gif':
						$image = @imagecreatefromgif($img);
						@imagecopyresampled($pattern, $image, 0, 0, 0, 0, $dimX, $dimY, $dimensions[0], $dimensions[1]);
						@imagedestroy($image);
						@imagegif($pattern, $img, 100);
						break;
					case 'png':
						$image = @imagecreatefrompng($img);
						@imagealphablending($pattern, false);
						@imagesavealpha($pattern, true);
						@imagealphablending($image, true);
						@imagecopyresampled($pattern, $image, 0, 0, 0, 0, $dimX, $dimY, $dimensions[0], $dimensions[1]);
						@imagedestroy($image);
						@imagepng($pattern, $img, 9);
						break;
				}
				ini_restore('memory_limit');
				return true;
			} else
				ini_restore('memory_limit');
				return false;
		}
		$errors = array();
		$success = array();
		$idverif = trim($_GET['id']);

		if(isset($_GET['id'])) {
			$paquet = new Paquet($idverif);
			$infos = $paquet->package_control(array('Name', 'compatible_ios', 'compatible_device'));
			$description = $paquet->description_paquet();
			if(!$paquet->verifier_fiche())
				$errors[] = _('No package found');
			elseif($membre->_level < 3 && $paquet->package_control('id_membre') != $membre->_id)
				$errors[] = _('You are not allowed to edit this package !');
		} else
			header('Location: manage.php');

		if(isset($_POST['description'])){
			if($_POST['description'] !== $description && empty($errors)) {
				$paquet->changer_control($_POST['description'], 'description1');
				$description = $_POST['description'];
				$success[] = sprintf(_('%s description has been edited !'), $infos['Name']);
			}
		}
		if(!empty($_FILES['icon']) && !empty($_FILES['icon']['name'])) {
			if((empty($_FILES['icon']['type'])) || (!in_array($_FILES['icon']['type'], array('image/png'))))
				$errors[] = _('Please choose a png icon only !');
			else {
				switch($_FILES['icon']['type']){
					case "image/png": $patch_type = 'png';break;
					default: $patch_type = 'error';break;
				}
			}
			if(!is_uploaded_file($_FILES['icon']['tmp_name']))
				$errors[] = 'Veuillez poster le fichier via le formulaire !';
			if ($_FILES['icon']['error']) {
				switch ($_FILES['icon']['error']){
					case 1: $errors[] ="Le fichier dépasse la limite autorisée par le serveur !";break;
					case 2: $errors[] = "Le fichier dépasse la limite autorisée dans le formulaire HTML !";break;
					case 3: $errors[] = "L'envoi du fichier a été interrompu pendant le transfert !";break;
					case 4: $errors[] = "Le fichier que vous avez envoyé a une taille nulle !";break;
				}
			}
			if(empty($errors)) {
				if(file_exists("images/debs/$idverif.png"))
					unlink("images/debs/$idverif.png");
				move_uploaded_file($_FILES["icon"]["tmp_name"], "images/debs/$idverif.png");
				if(darkroom("images/debs/$idverif.png", '60') == true)
					$success[] = _('Icon added successfully !');
				else
					$success[] = _('Icon added successfully !');
			}
		}
		if(!empty($_FILES['images'])) {
			$i = 0;
			while($i < count($_FILES['images']['name'])) {
				if(!empty($_FILES['images']['name'][$i])) {
				 	if((empty($_FILES['images']['type'][$i])) || (!in_array($_FILES['images']['type'][$i], array('image/png', 'image/gif', 'image/jpg', 'image/jpeg'))))
						$errors[] = _('Please select a png, gif, jpeg or jpg image !');
				        else {
						switch($_FILES['images']['type'][$i]){
							case "image/png": $patch_type = 'png';break;
							case "image/jpg": $patch_type = 'jpg';break;
							case "image/jpeg": $patch_type = 'jpeg';break;
							case "image/gif": $patch_type = 'gif';break;
							default: $patch_type = 'error';break;
						}
					}
					if(!is_uploaded_file($_FILES['images']['tmp_name'][$i]))
						$errors[] = 'Veuillez poster le fichier via le formulaire !';
					if ($_FILES['images']['error'][$i]) {
						switch ($_FILES['images']['error'][$i]){
							case 1: $errors[] ="Le fichier dépasse la limite autorisée par le serveur !";break;
							case 2: $errors[] = "Le fichier dépasse la limite autorisée dans le formulaire HTML !";break;
							case 3: $errors[] = "L'envoi du fichier a été interrompu pendant le transfert !";break;
							case 4: $errors[] = "Le fichier que vous avez envoyé a une taille nulle !";break;
						}
					}
					if(empty($errors)) {
						if(!is_dir("images/debs/$idverif")) {
							mkdir("images/debs/$idverif", 0755);
							$img_nom = '1';
						} else {
							$img_nom = '1';
							while(file_exists("images/debs/$idverif/$img_nom.png") || file_exists("images/debs/$idverif/$img_nom.gif") || file_exists("images/debs/$idverif/$img_nom.jpg") || file_exists("images/debs/$idverif/$img_nom.jpeg"))
								$img_nom++;
						}
						move_uploaded_file($_FILES['images']["tmp_name"][$i], "images/debs/$idverif/$img_nom.$patch_type");
						if(darkroom("images/debs/$idverif/$img_nom.$patch_type", '480') == true)
							$success[] = _('Image added successfully !');
						else
							$success[] = _('Image added successfully !');
					}
				}
				$i++;
			}
		}
		if(isset($_POST['compatible_device'])) {
			foreach($_POST['compatible_device'] as $value) {
				if($value != 'iPhone' && $value != 'iPad' && $value != 'iPod')
				$errors[] = _('This device does not exist !');
			}
			if(empty($errors)) {
				if($infos['compatible_device'] !== serialize($_POST['compatible_device'])) {
					$paquet->changer_control(serialize($_POST['compatible_device']), 'compatible_device');
					$infos['compatible_device'] = serialize($_POST['compatible_device']);
					$success[] = _('Devices compatibility has been updated !');
				}
			}
		} elseif(!empty($_POST) && empty($_POST['compatible_device'])) {
			if($infos['compatible_device'] != false && empty($errors)) {
				$paquet->changer_control(false, 'compatible_device');
				$infos['compatible_device'] = false;
				$success[] = _('Devices compatibility has been reset !');
			}
		}
		if(isset($_POST['compatible_ios'])) {
			foreach($_POST['compatible_ios'] as $value) {
				if($value != 'iOS 3' && $value != 'iOS 4' && $value != 'iOS 5' && $value != 'iOS 6' && $value != 'iOS 7' && $value != 'iOS 8' && $value != 'iOS 9')
				$errors[] = _('This iOS does not exist !');
			}
			if(empty($errors)) {
				if($infos['compatible_ios'] !== serialize($_POST['compatible_ios'])) {
					$paquet->changer_control(serialize($_POST['compatible_ios']), 'compatible_ios');
					$infos['compatible_ios'] = serialize($_POST['compatible_ios']);
					$success[] = _('IOS compatibility has been updated !');
				}
			}
		} elseif(!empty($_POST) && empty($_POST['compatible_ios'])) {
			if($infos['compatible_ios']!= false && empty($errors)) {
				$paquet->changer_control(false, 'compatible_ios');
				$infos['compatible_ios'] = false;
				$success[] = _('IOS compatibility has been reset !');
			}
		}
		if(!empty($_GET['remove']) && empty($errors)) {
			$remove_img = preg_replace('/[^0-9a-z-\.]/', '', trim($_GET['remove']));
			if(file_exists('images/debs/'.$idverif.'/'.$remove_img)) {
				unlink('images/debs/'.$idverif.'/'.$remove_img);
				$i = 0;
				$dir_rename = opendir('images/debs/'.$idverif) or die('Erreur de listage : le repertoire ne peut pas etre ouvert.');
				while($element = readdir($dir_rename)) {
					if($element != '.' && $element != '..') {
						$path_parts = pathinfo($dir_rename.'/'.$element);
						$i++;
						rename('images/debs/'.$idverif.'/'.$element, 'images/debs/'.$idverif.'/'.$i.'.'.$path_parts['extension']);
					}
				}
				if(is_dir('images/debs/'.$idverif) && $i ==0) {
					rmdir('images/debs/'.$idverif);
				}
				$success[] = _('Image deleted successfully !');
			} elseif(file_exists('images/debs/'.$remove_img)) {
				unlink('images/debs/'.$remove_img);
				$success[] = _('Icon deleted successfully !');
			} else
				$errors[] = _('Unable to locate the image to delete !');
		}
		$compatible_ios = unserialize($infos['compatible_ios']);
		$compatible_device = unserialize($infos['compatible_device']);
		$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Edit description'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Edit description'); ?> - <?php echo $paquet->package_control('Name');		
			if(file_exists('images/debs/'.$idverif.'.png'))
				echo ' <img src="images/debs/'.$idverif.'.png?'.rand(0, 9).'" width="50" height="50" />'; ?></h2>
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
		<div class="tabbable-panel">
			<div class="tabbable-line">
				<ul class="nav nav-tabs text-center">
					<li class="active">
						<a href="#tab_description" data-toggle="tab"><?php echo _('Description'); ?></a>
					</li>
					<li>
						<a href="#tab_changelog" data-toggle="tab"><?php echo _('Recent changes'); ?></a>
					</li>
				</ul>
				<div class="tab-content jumbotron">
					<div class="tab-pane fade in active" id="tab_description">
						<form class="form-horizontal" action="?id=<?php echo $idverif; ?>" method="post" enctype="multipart/form-data"><fieldset>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="icon"><?php if(file_exists('images/debs/'.$idverif.'.png')) {echo '<a class="pull-left" href="?id='.$idverif.'&amp;remove='.$idverif.'.png"><span class="glyphicon glyphicon-trash"></span></a> ';}echo _('Icon').' (png)'; ?></label>
								<div class="col-sm-10">
									<input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
									<input type="file" class="form-control" name="icon" />
								</div>
							</div>
							<div class="form-group">
									<textarea name="description" rows="12" style="width:100%"><?php echo $description; ?></textarea>
							</div>
							<?php $dir_nom = 'images/debs/'.$idverif;
							if(is_dir($dir_nom)){
								echo '<div class="form-group">';
								$dir = opendir($dir_nom) or die('Erreur de listage : le répertoire ne peut pas être ouvert.');
								$images= array();
								while($element = readdir($dir)) {
									if($element != '.' && $element != '..') {
										if(!is_dir($dir_nom.'/'.$element)) {
											$images[] = $element;
										}
									}
								}
								foreach($images as $image) {
									echo '<a href="?id='.$idverif.'&amp;remove='.$image.'"><span class="glyphicon glyphicon-trash"></span></a>';
									echo '<img src="'.$dir_nom.'/'.$image.'?'.rand(0, 9).'" width="99" />';
								}
								echo '</div>';
							} ?>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="images[]"><?php echo _('Screenshots'); ?></label>
								<div class="col-sm-10">
									<input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
									<input type="file" class="form-control" name="images[]" id="images[]" multiple />
									<span class="help-block"><?php echo _('Select one or multiple screenshots.'); ?></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label"><?php echo _('Compatibility'); ?></label>
								<div class="col-lg-3 btn-group text-center" data-toggle="buttons">
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_device) && in_array('iPhone', $compatible_device)) echo ' active'; ?>" for="compatible_device[]">
										<input type="checkbox" name="compatible_device[]" value="iPhone"<?php if(is_array($compatible_device) && in_array('iPhone', $compatible_device)) echo ' checked'; ?>>iPhone
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_device) && in_array('iPad', $compatible_device)) echo ' active'; ?>" for="compatible_device[]">
										<input type="checkbox" name="compatible_device[]" value="iPad"<?php if(is_array($compatible_device) && in_array('iPad', $compatible_device)) echo ' checked'; ?>>iPad
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_device) && in_array('iPod', $compatible_device)) echo ' active'; ?>" for="compatible_device[]">
										<input type="checkbox" name="compatible_device[]" value="iPod"<?php if(is_array($compatible_device) && in_array('iPod', $compatible_device)) echo ' checked'; ?>>iPod
									</label>
								</div>
								<div class="col-lg-7 btn-group text-center" data-toggle="buttons">
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 3', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 3"<?php if(is_array($compatible_ios) && in_array('iOS 3', $compatible_ios)) echo ' checked'; ?>>iOS 3
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 4', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 4"<?php if(is_array($compatible_ios) && in_array('iOS 4', $compatible_ios)) echo ' checked'; ?>>iOS 4
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 5', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 5"<?php if(is_array($compatible_ios) && in_array('iOS 5', $compatible_ios)) echo ' checked'; ?>>iOS 5
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 6', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 6"<?php if(is_array($compatible_ios) && in_array('iOS 6', $compatible_ios)) echo ' checked'; ?>>iOS 6
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 7', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 7"<?php if(is_array($compatible_ios) && in_array('iOS 7', $compatible_ios)) echo ' checked'; ?>>iOS 7
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 8', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 8"<?php if(is_array($compatible_ios) && in_array('iOS 8', $compatible_ios)) echo ' checked'; ?>>iOS 8
									</label>
									<label class="btn btn-sm btn-success<?php if(is_array($compatible_ios) && in_array('iOS 9', $compatible_ios)) echo ' active'; ?>" for="compatible_ios[]">
										<input type="checkbox" name="compatible_ios[]" value="iOS 9"<?php if(is_array($compatible_ios) && in_array('iOS 9', $compatible_ios)) echo ' checked'; ?>>iOS 9
									</label>
								</div>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-block"><?php echo _('Save'); ?></button>
							</div>
						</fieldset></form>
					</div>
					<div class="tab-pane fade" id="tab_changelog">
						<form class="form-horizontal" method="post" id="changelog" action="/ajax/changelog.php?id=<?php echo $idverif; ?>"></fieldset>
							<h4 class="text-center"><?php echo _('Edit the recent changes'); ?></h4>
							<div id="errorChangelog"></div>
							<div class="form-group">
								<textarea id="changelogTexte" name="changelog" rows="12" style="width:100%"><?php echo $paquet->package_control('changelog'); ?></textarea>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-block"><?php echo _('Save'); ?></button>
							</div>
						</fieldset></form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script>$("#changelog").submit(function(e){$("html,body").animate({scrollTop:$("#errorChangelog").offset().top - 50},"slow");$("#errorChangelog").html('<div class="text-center"><img src="images/ajax-loader.gif" /></div>');var changelog = tinyMCE.get("changelogTexte").getContent();$.ajax({url : $(this).attr("action"),type: "POST",data : "changelog="+encodeURIComponent(changelog), success:function(data){if(data.numError == '0'){$("#errorChangelog").html('<p class="text-center text-danger">'+data.error+'</p>');} else {$("#errorChangelog").html('<p class="text-center text-success">'+data.error+'</p>');}},error:function(error) {$("#errorChangelog").html('<p class="text-center text-danger">Connexion impossible ! Erreur '+error.responseText+'</p>');}});e.preventDefault();});</script>
	<script src="js/tinymce/tinymce.min.js"></script>
	<script>tinymce.init({selector: "textarea",plugins: ["advlist autolink autoresize lists link image charmap print preview hr anchor pagebreak","searchreplace wordcount visualblocks visualchars code fullscreen","insertdatetime media table contextmenu","paste textcolor"],toolbar1: "undo redo | styleselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media print preview",max_height: 350,min_height: 200,height : 250,
<?php if($lang_user != 'en_US') echo 'language:"'.$lang_user.'"'; ?>});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>