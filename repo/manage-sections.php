<?php require_once('includes/session.class.php');
$membre = new Session();
if ($membre->_connected && $membre->_level > 0) {
	require_once('includes/time-header.php');
	require_once('includes/package.class.php');
	translation();
	$errors = array();
	$success = array();
	if(file_exists('includes/admin/sections.txt')) {
		$file = file_get_contents('includes/admin/sections.txt');
		$file = explode("\n", $file);
	}
	if(isset($_GET['build'])) {
		$paquet = new Paquet('icone');
		if(count($file) > 0) {
			if($paquet->verifier_fiche()) {
				$version = str_replace(',', '.', $paquet->package_control('Version') + '0.1');
				$success[] = _('Your package has been updated successfully !');
				$tweet = 'Update : Gold Cydia Repo Icons - '.$version.' - '.config('url').'pack/icone';
				tweet($tweet);
			} else {
				mkdir('debs/org.goldencydia.icone', 0775, true);
				$paquet->changer_control('org.goldencydia.icone', 'Package');
				$paquet->changer_control('Gold Cydia Repo Icons', 'Name');
				$paquet->changer_control('Officials Gold Cydia Repo Icons', 'Description');
				$paquet->changer_control('GoldenCydia', 'Author');
				$paquet->changer_control('System', 'Section');
				$paquet->changer_control('yes', 'Essential');
				$version = '1.0';
				$success[] = _('Your package has been added successfully !');
				$tweet = 'New : Gold Cydia Repo Icons - '.$version.' - '.config('url').'pack/icone';
				tweet($tweet);
			}
			$chem_temp = 'includes/admin/temp/'.$membre->_id;
			if(is_dir($chem_temp))
				rrmdir($chem_temp);
			if(is_dir($chem_temp))
				$errors[] = _('Error during cleaning !');
			if(file_exists($chem_temp.'.deb'))
				@unlink($chem_temp.'.deb');
			if(file_exists($chem_temp.'.deb'))
				$errors[] = _('Error during cleaning !');

			mkdir($chem_temp);
			mkdir($chem_temp.'/DEBIAN', 0775, true);
			mkdir($chem_temp.'/Applications', 0775, true);
			mkdir($chem_temp.'/Applications/Cydia.app', 0775, true);
			mkdir($chem_temp.'/Applications/Cydia.app/Sections', 0775, true);

			foreach($file as $section_a_copier) {
				@copy('images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($section_a_copier))).'.png', $chem_temp.'/Applications/Cydia.app/Sections/'.str_replace(' ', '_', trim(preg_replace('/\(.*\)/', '', html_entity_decode($section_a_copier)))).'.png');
			}
			$f = fopen($chem_temp.'/DEBIAN/control', 'w');
			fwrite($f, "Package: org.goldencydia.icone\nName: Gold Cydia Repo Icons\nVersion: ".$version."\nArchitecture: iphoneos-arm\nDescription: Officials Gold Cydia Repo Icons\nDepiction: ".config('url')."pack/icone\nAuthor: GoldenCydia\nSection: System\nEssential: yes\n");
			fclose($f);

			cchmod($chem_temp, 0775);
			shell_exec('dpkg-deb -b '.$chem_temp);
			rename($chem_temp.'.deb', 'debs/org.goldencydia.icone/org.goldencydia.icone.deb');
			cchmod('debs/org.goldencydia.icone', 0775);
			if(is_dir($chem_temp))
				rrmdir($chem_temp);
			if(is_dir($chem_temp))
				$errors[] = _('Error during cleaning !');
			if(file_exists($chem_temp.'.deb'))
				@unlink($chem_temp.'.deb');
			if(file_exists($chem_temp.'.deb'))
				$errors[] = _('Error during cleaning !');

			$paquet->changer_control($version, 'Version');
			$taille = filesize('debs/org.goldencydia.icone/org.goldencydia.icone.deb');
			$paquet->changer_control($taille, 'Size');
			unset($taille);
			$paquet->changer_control(md5_file('debs/org.goldencydia.icone/org.goldencydia.icone.deb'), 'Md5sum');
			$paquet->changer_control(true, 'online');
			$paquet->changer_control(true, 'tweet');
			$paquet->changer_control($membre->_id, 'id_membre');
			$paquet->changer_control(date('Y-m-d H:i:s', fileatime('debs/org.goldencydia.icone/org.goldencydia.icone.deb')), 'date_update');
			$paquet->supprimer_cache();
			unset($chem_temp);
		} else {
			if($paquet->verifier_fiche()) {
				if(file_exists('images/debs/org.goldencydia.icone.png')) {
					@unlink('images/debs/org.goldencydia.icone.png');
				}
				$paquet->supprimer_definitivement();
				$success[] = _('Your package has been removed successfully !');
			}
		}
		unset($paquet);
	}
	if(!empty($_POST['section']) && !empty($_FILES['icone'])) {
		$i = 0;
		while($i < count($_POST['section']) || $i < count($_FILES['icone']['name'])) {
			$test_section = trim(preg_replace('/\(|\)/', '', strip_tags($_POST['section'][$i])));
			if(!empty($test_section) && !empty($_FILES['icone']['name'][$i])) {
				if((empty($_FILES['icone']['type'][$i])) || (!in_array($_FILES['icone']['type'][$i], array('image/png'))))
					$errors[] = _('Please choose a png file only !');
				else {
					switch($_FILES['icone']['type'][$i]){
						case 'image/png': $patch_type = 'png';break;
						default: $patch_type = 'error';break;
					}
				}
				if(!is_uploaded_file($_FILES['icone']['tmp_name'][$i]))
					$errors[] = _('Please post the file with the form !');
				if(filesize($_FILES['icone']['tmp_name'][$i]) > 1000000)
					$errors[] = sprintf(_('Your file is larger than %s MB !'), '1');

				if ($_FILES['icone']['error'][$i]) {
					switch ($_FILES['icone']['error'][$i]){
						case 1: $errors[] = _('The file exceeds the limit allowed by the server !');break;
						case 2: $errors[] = _('The file exceeds the allowed limit in the HTML form !');break;
						case 3: $errors[] = _('Sending the file was interrupted during transfer !');break;
						case 4: $errors[] = _('The file you sent has zero size !');break;
					}
				}
				if(!file_exists('images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($test_section))).'.png')) {
					if(strlen($test_section) < 50) {
						if(empty($errors)) {
							file_put_contents('includes/admin/sections.txt', "\n".$test_section, FILE_APPEND);
							move_uploaded_file($_FILES['icone']['tmp_name'][$i], 'images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($test_section))).'.png');
							$success[] = sprintf(_('%s has been added !'), $test_section);
						}
					} else
						$errors[] = _('Section name must be 50 characters or less.');
				} else
					$errors[] = _('This section already exists !');
			}
			unset($test_section);
			$i++;
		}
		if(file_exists('includes/admin/sections.txt')) {
			$file = file_get_contents('includes/admin/sections.txt');
			$file = explode("\n", $file);
		}
	}
	if(!empty($_POST['section']) && !empty($_FILES['icon']['name'])) {
		if((empty($_FILES['icon']['type'])) || (!in_array($_FILES['icon']['type'], array('image/png'))))
			$errors[] = _('Please choose a png file only !');
		else {
			switch($_FILES['icon']['type']){
				case 'image/png': $patch_type = 'png';break;
				default: $patch_type = 'error';break;
			}
		}
		if(!is_uploaded_file($_FILES['icon']['tmp_name']))
			$errors[] = _('Please post the file with the form !');
		if(filesize($_FILES['icon']['tmp_name']) > 1000000)
			$errors[] = sprintf(_('Your file is larger than %s MB !'), '1');
		if ($_FILES['icon']['error']) {
			switch ($_FILES['icone']['error'][$i]){
				case 1: $errors[] = _('The file exceeds the limit allowed by the server !');break;
				case 2: $errors[] = _('The file exceeds the allowed limit in the HTML form !');break;
				case 3: $errors[] = _('Sending the file was interrupted during transfer !');break;
				case 4: $errors[] = _('The file you sent has zero size !');break;
			}
		}
		if(file_exists('images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($_POST['section']))).'.png')) {
			if(empty($errors)) {
				unlink('images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($_POST['section']))).'.png');
				move_uploaded_file($_FILES['icon']['tmp_name'], 'images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($_POST['section']))).'.png');
				$success[] = sprintf(_('%s has been edited !'), $_POST['section']);
			}
		}
	}
	$site_nom = config('nom'); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo _('Manage all sections'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">

		<div class="panel-heading">
			<h2 class="text-primary"><?php echo _('Manage all sections').' <small>'.@number_format(count($file), 0, ', ', ' ').' '.strtolower(_('Sections')).'</small>';
				echo ' <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target=".add">'._('Add').'</button>';
				$paquet = new Paquet('icone');
				if(count($file) > 0) {
					if($paquet->verifier_fiche())
						echo ' <a href="?build" class="btn btn-xs btn-info">'._('Rebuild package').'</a>';
					else
						echo ' <a href="?build" class="btn btn-xs btn-success">'._('Build package').'</a>';
				} else {
					if($paquet->verifier_fiche())
						echo ' <a href="?build" class="btn btn-xs btn-danger">'._('Remove package').'</a>';
				}
				unset($paquet); ?>
			</h2>
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
			<?php if (!empty($file)) {
				$modal = '';
				echo '<table class="table table-bordered table-hover text-center table-responsive" style="border:0 none transparent;margin:5px 0">';
				$j = 0;
				foreach ($file as $section) {
					echo '<tr>
						<td style="word-break:break-all;border-bottom:1px solid;margin:0"><img height="50" width="50" class="img-thumbnail" src="/images/sections/'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($section))).'.png" /> '.$section.'</td>
						<td style="border-bottom:1px solid;margin:0"><div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown">'._('Actions').' <span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li class="text-left"><a href="#" data-toggle="modal" data-target=".edit-'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($section))).'"><span class="glyphicon glyphicon-picture"></span> '._('Edit').'</a></li>
							</ul>
						</div></td>
					</tr>';
					$modal .= '<div class="modal fade edit-'.preg_replace('/[\/_|+ -]+/', '-', strtolower(trim($section))).'" tabindex="-1" role="dialog" aria-labelledby="add" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="?" enctype="multipart/form-data"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'._('Close').'</span></button><h4 class="modal-title">'._('Edit your section').'</h4></div><div class="modal-body text-center"><p>'._('Png image and recommended 120x120.').'</p><div class="row"><div class="col-sm-12"><input class="form-control" type="hidden" name="section" value="'.$section.'" /><input type="file" name="icon" class="form-control" data-icon="false" data-buttonBefore="true" data-buttonName="btn-primary" data-buttonText="'._('Browse').'" /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" /></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">'._('Edit').'</button></div></form></div></div></div>';
				}
				echo '</table>';
				echo $modal;
			} else
				echo '<p>No section</p>'; ?>
			<div class="modal fade add" tabindex="-1" role="dialog" aria-labelledby="add" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<form method="post" action="?" enctype="multipart/form-data">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo _('Close'); ?></span></button>
								<h4 class="modal-title text-center"><?php echo _('Add your sections'); ?></h4>
							</div>
							<div class="modal-body text-center" id="section_to_add">
								<p><?php echo _('Png image and recommended 120x120.'); ?></p>
								<div class="row">
									<div class="col-sm-6">
										<input class="form-control" type="text" name="section[]" placeholder="<?php echo _('Name'); ?>" />
									</div>
									<div class="col-sm-6">
										<input type="file" name="icone[]" class="form-control" data-icon="false" data-buttonBefore="true" data-buttonName="btn-primary" data-buttonText="<?php echo _('Browse'); ?>" />
									<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-primary"><?php echo _('Add'); ?></button>
								<button onclick="javascript:new_section();return false;" class="btn btn-default"><?php echo _('Add another field'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>function new_section(){$("#section_to_add").append('<div class="row" style="margin-top:10px"><div class="col-sm-6"><input class="form-control" type="text" name="section[]" placeholder="<?php echo _('Name'); ?>"  /></div><div class="col-sm-6"><input type="file" class="form-control" name="icone[]" /></div>');}</script>
	<?php require_once('includes/admin/footer.php'); ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>