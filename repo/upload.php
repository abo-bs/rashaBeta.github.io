<?php require_once('includes/session.class.php');
$membre = new Session();
/**
* @return bool
* @param string $in
* @param string $out
* @desc uncompressing the file with the bzip2-extension
*/
function bunzip2 ($in, $out)
{
    if (!file_exists ($in) || !is_readable ($in))
        return false;
    if ((!file_exists ($out) && !is_writeable (dirname ($out)) || (file_exists($out) && !is_writable($out)) ))
        return false;

    $in_file = bzopen ($in, "rb");
    $out_file = fopen ($out, "wb");

    while ($buffer = bzread ($in_file, 4096)) {
        fwrite ($out_file, $buffer, 4096);
    }

    bzclose ($in_file);
    fclose ($out_file);
   
    return true;
} 
	if ($membre->_connected && $membre->_level > 0) {
		translation();
		ignore_user_abort(1);
		require_once('includes/time-header.php');
		require_once('includes/package.class.php');
		$errors = array();
		$success = array();
		$upload = false;
		$reussi = false;
		if(!empty($_GET['update'])) {
			$_GET['update'] = preg_replace("/[^0-9a-z-]/", "", $_GET['update']);
			$check = new Paquet($_GET['update']);
			$update = ($check->verifier_fiche()) ? $check->package_control('Package') : false;
			unset($check);
 		}
		function explore($dir, $i = 0) {
			if (!$i)
				$i = 0;
			if(is_dir($dir)) {
				$return = '';
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != '.' && $object != '..') {
						if(@filetype($dir.'/'.$object) == 'dir')
							$return .= '<div onclick="$(\'#dir-'.preg_replace("/[^A-Za-z0-9]/", '', strtolower(trim($object))).'-'.$i.'\').toggle(100);$(\'#img-dir-'.preg_replace("/[^A-Za-z0-9]/", '', strtolower(trim($object))).'-'.$i.'\').toggleClass(\'glyphicon-folder-open glyphicon-folder-close text-muted\');">
								<span id="img-dir-'.preg_replace("/[^A-Za-z0-9]/", '', strtolower(trim($object))).'-'.$i.'" class="glyphicon glyphicon-folder-close text-muted"></span> '.$object.'
							</div>
							<div id="dir-'.preg_replace("/[^A-Za-z0-9]/", '', strtolower(trim($object))).'-'.$i.'" style="display:none;padding-left:15px">
								'.explore($dir."/".$object, $i).'
							</div>';
						else
							$return .= '<div>
								<span class="glyphicon glyphicon-file"></span> '.$object.'
							</div>';
					}
					$i++;
				}
				reset($objects);
				return $return;
			} else
				return false;
		}

		if(isset($_FILES['file']) && !isset($_POST['nom']) && !isset($_POST['version']) && !isset($_POST['auteur']) && !isset($_POST['categorie']) && !isset($_POST['description'])){
			if(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION) !== 'deb'){
				$errors[] = _('Error : Please choose a .deb file !a');
			}
			if ($_FILES['file']['error']) {
				switch ($_FILES['file']['error']){
					case 1: // UPLOAD_ERR_INI_SIZE
					$errors[] = _('File exceeds limit allowed by the server !');
					break;
					case 2: // UPLOAD_ERR_FORM_SIZE
					$errors[] = _('File exceeds limit allowed in the HTML form !');
					break;
					case 3: // UPLOAD_ERR_PARTIAL
					$errors[] = _('Sending the file has been interrupted during transfer !');
					break;
					case 4: // UPLOAD_ERR_NO_FILE
					$errors[] = _('File you sent has zero size!');
					break;
				}
			}
			// Nettoyage
			if(is_dir('includes/admin/temp/'.$membre->_pseudo))
				rrmdir("includes/admin/temp/".$membre->_pseudo);
			if(is_dir('includes/admin/temp/'.$membre->_pseudo))
				$errors[] = _('Cleaning Error !');
			if(file_exists('includes/admin/temp/'.$membre->_pseudo.'.deb'))
				unlink('includes/admin/temp/'.$membre->_pseudo.'.deb');
			if(file_exists('includes/admin/temp/'.$membre->_pseudo.'.deb'))
				$errors[] = _('Cleaning Error !');

			if(empty($errors)){
				if(!is_dir('includes/admin/temp'))
					mkdir('includes/admin/temp');
				//Deplace dans le dossier tmp
				move_uploaded_file($_FILES['file']['tmp_name'], 'includes/admin/temp/'.$membre->_pseudo.'.deb');
				passthru("dpkg-deb -x ".escapeshellarg(dirname(__FILE__)."/includes/admin/temp/".$membre->_pseudo.".deb")." ".escapeshellarg(dirname(__FILE__)."/includes/admin/temp/".$membre->_pseudo)."");
				passthru("dpkg-deb -e ".escapeshellarg(dirname(__FILE__)."/includes/admin/temp/".$membre->_pseudo.".deb")." ".escapeshellarg(dirname(__FILE__)."/includes/admin/temp/".$membre->_pseudo."/DEBIAN")."");
				if(!is_dir('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN') || !file_exists('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control')) {
					$errors[] = _('Error : Please choose a .deb file !b');
					$upload = false;
//					rrmdir('includes/admin/temp/'.$membre->_pseudo);
//					unlink('includes/admin/temp/'.$membre->_pseudo.'.deb');
				} else {
					chmod('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN', 0755);
					chmod('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 0755);
					$clean = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Package');
					if($update)
						$id_prev = strtolower($_GET['update']);
					elseif(strrchr($clean, '.'))
						$id_prev = strtolower(substr(strrchr($clean, '.'), 1));
					else
						$id_prev = strtolower($clean);
					$paquet = new Paquet($id_prev);
					$infos = $paquet->package_control(array('Package', 'Name', 'Section', 'Version', 'Author', 'Description', 'Depends', 'Pre-Depends', 'Conflicts', 'Icon', 'id_membre', 'online'));
					if($infos['Package'] && stristr('org.goldencydia.', substr($infos['Package'], 0, 21)))
						$clean = $id_prev;
					$nom = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Name');
					$version = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Version');
					$auteur = preg_replace('/<.*>/', '', package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Author'));
					$description = preg_replace("#\n|\t|\r#", "", package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Description'));
					$section = trim(preg_replace('/\(.+\)/', '', package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Section')));
					$dependance = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Depends');
					$predependance = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Pre-Depends');
					$conflit = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Conflicts');
					$icon = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Icon');
					$etat = false;
					$success[] = _('Package being added, if you leave this page, it will be deleted.');
					$upload = true;
					$existe = ($infos['Package'] != false) ? true : false;
					if(isset($_GET['update'])) {
						if($membre->_level < 3 && $infos['id_membre'] != $membre->_id)
							$errors[] = _('You are not allowed to edit this package !');
					}
				}
			}
		} elseif(isset($_GET['id']) && !isset($_POST['identifiant']) && !isset($_POST['nom']) && !isset($_POST['version']) && !isset($_POST['auteur']) && !isset($_POST['categorie']) && !isset($_POST['description'])) {
			$idverif = trim($_GET['id']);
			if(!is_dir("debs/org.goldencydia.$idverif"))
				$errors[] = _('This package does not exist !');
			else {
				if(!file_exists("debs/org.goldencydia.$idverif/org.goldencydia.$idverif.deb"))
					$errors[] = _('This package does not exist !');
			}

			// Nettoyage
			if(is_dir('includes/admin/temp/'.$membre->_pseudo))
				rrmdir('includes/admin/temp/'.$membre->_pseudo);
			if(is_dir('includes/admin/temp/'.$membre->_pseudo))
				$errors[] = _('Cleaning Error !');

			if(file_exists('includes/admin/temp/'.$membre->_pseudo.'.deb'))
				unlink('includes/admin/temp/'.$membre->_pseudo.'.deb');
			if(file_exists('includes/admin/temp/'.$membre->_pseudo.'.deb'))
				$errors[] = _('Cleaning Error !');

			if(!is_dir('includes/admin/temp'))
				mkdir('includes/admin/temp');
			//Copie le deb
			if(!@copy('debs/org.goldencydia.'.$idverif.'/org.goldencydia.'.$idverif.'.deb', 'includes/admin/temp/'.$membre->_pseudo.'.deb')) {
				cchmod("debs", 0775);
				if(!@copy('debs/org.goldencydia.'.$idverif.'/org.goldencydia.'.$idverif.'.deb', 'includes/admin/temp/'.$membre->_pseudo.'.deb'))
					$errors[] = _('Can not copy the package !');
			}

			if(empty($errors)){
				// Extrait le deb
        		        shell_exec("dpkg-deb -x includes/admin/temp/".$membre->_pseudo.".deb includes/admin/temp/".$membre->_pseudo."");
				// Extrait le control
				shell_exec("dpkg-deb -e includes/admin/temp/".$membre->_pseudo.".deb includes/admin/temp/".$membre->_pseudo."/DEBIAN");
				$clean = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Package');
				if(stristr('org.goldencydia.',substr($clean,0,21)))
					$clean = substr($clean,21);
				if(strrchr($clean, '.'))
					$id_prev = strtolower(substr(strrchr($clean, '.'), 1));
				else
					$id_prev = strtolower($clean);
				$id_prev = strtolower($clean);
				$paquet = new Paquet($id_prev);
				$infos = $paquet->package_control(array('Package', 'Name', 'Section', 'Version', 'Author', 'Description', 'Depends', 'Pre-Depends', 'Conflicts', 'Icon', 'id_membre', 'online'));
				if($membre->_level < 3 && $infos['id_membre'] != $membre->_id)
					$errors[] = _('You are not allowed to edit this package !');
				$nom = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Name');
				$version = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Version');
				$auteur = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Author');
				$description = preg_replace("#\n|\t|\r#", "", package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Description'));
				$section = trim(preg_replace('/\(.+\)/', '', package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Section')));
				$dependance = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Depends');
				$predependance = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Pre-depends');
				$conflit = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Conflicts');
				$icon = package_control('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'Icon');
				$etat = $infos['online'];
				$success[] = _('Package being changed !');
				$existe = true;
				$upload = true;
			}
		} elseif(isset($_POST['identifiant']) || isset($_POST['nom']) || isset($_POST['version']) || isset($_POST['auteur']) || isset($_POST['categorie']) || isset($_POST['description'])){
			if(!isset($_POST["identifiant"]))
				$errors[] = _('Please specify an identifier !');
			else
				$clean = preg_replace("/[^0-9a-z-]/", "", strtolower(trim($_POST["identifiant"])));

			if(strlen($clean) > 50)
				$errors[] = _('Identifier must do less than 50 characters !');

			if((isset($_GET['id']) && $clean != $_GET['id']) || ($update && $clean != $_GET['update']) || (!isset($_GET['id']) && !$update)) {
				if(file_exists('debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb'))
					$errors[] = _('This package already exist !');
			}
			if(!empty($_POST["nom"]))
				$nom = trim(trim($_POST["nom"]));
			else
				$errors[] = _('Please specify a name !');
			if(!empty($_POST["version"]))
				$version = trim($_POST["version"]);
			else
				$errors[] = _('Please specify a version !');
			if(!empty($_POST["auteur"]))
				$auteur = trim($_POST["auteur"]);
			else
				$errors[] = _('Please specify an author !');
			if(empty($_POST["categorie"]))
				$errors[] = _('Please choose a section !');
			elseif($_POST["categorie"] == _('Choose a section'))
				$errors[] = _('Please choose a section !');
			else
				$section = trim(preg_replace('/\(.+\)/', '', $_POST["categorie"]));
			if(!empty($_POST["description"]))
				$description = trim(preg_replace("#\n|\t|\r#", "", $_POST["description"]));
			else
				$errors[] = _('Please specify a description !');

			if(empty($_POST["icon"]))
				$icon = '';
			else
				$icon = trim($_POST["icon"]);
			if(empty($_POST["dependance"]))
				$dependance='';
			else
				$dependance = trim($_POST["dependance"]);
			if(empty($_POST["predependance"]))
				$predependance='';
			else
				$predependance = trim($_POST["predependance"]);
			if(empty($_POST["conflit"]))
				$conflit='';
			else
				$conflit = trim($_POST["conflit"]);
			if(isset($_POST['remove'])) {
				foreach($_POST['remove'] as $trop) {
					if(file_exists('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/'.$trop)) {
						unlink('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/'.$trop);
						if(!file_exists('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/'.$trop))
							$success[] = sprintf(_('File %s has been removed !'), $trop);
					} else
						$errors[] = sprintf(_('File %s can\' be located !'), $trop);
				}
			}
			if(!empty($_POST['online']) && $_POST['online'] == 'online')
				$etat = 1;
			elseif(!empty($_POST['online']) && $_POST['online'] == 'offline')
				$etat = 0;
			else
				$errors[] = _('Please specify a state.');

			if(isset($_GET['id']))
				$paquet = new Paquet($_GET['id']);
			elseif(isset($_GET['update']))
				$paquet = new Paquet($_GET['update']);
			else
				$paquet = new Paquet($clean);
			$infos = $paquet->package_control(array('Package', 'Name', 'Section', 'Version', 'Author', 'Description', 'Depends', 'Pre-Depends', 'Conflicts', 'Icon', 'id_membre', 'online'));
			if(isset($_GET['id']) || $update) {
				if($membre->_level < 3 && $infos['id_membre'] != $membre->_id)
					$errors[] = _('You are not allowed to edit this package !');
			}

			if(!is_dir('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN') || !file_exists('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control')) {
				$errors[] = _('Error : Please choose a .deb file !');
				$upload = false;
				rrmdir('includes/admin/temp/'.$membre->_pseudo);
				if(file_exists('includes/admin/temp/'.$membre->_pseudo.'.deb'))
					unlink('includes/admin/temp/'.$membre->_pseudo.'.deb');
			} else
				$upload = true;

			if(empty($errors)){
				$pdo = PDO2::getInstance();
				$enregistrer_paquet = new Paquet($clean);
				$enregistrer_paquet->changer_control('org.goldencydia.'.$clean, 'Package');
				$enregistrer_paquet->changer_control($nom, 'Name');
				$enregistrer_paquet->changer_control($version, 'Version');
				$enregistrer_paquet->changer_control($description, 'Description');
				$enregistrer_paquet->changer_control($auteur, 'Author');
				$enregistrer_paquet->changer_control($_POST["categorie"], 'Section');
				if(!empty($icon)) {
					$enregistrer_paquet->changer_control($icon, 'Icon');
					$icon = "Icon: ".$icon."\n";
				} else
					$enregistrer_paquet->changer_control(false, 'Icon');
				if(!empty($dependance)) {
					$enregistrer_paquet->changer_control($dependance, 'Depends');
					$dependance = "Depends: ".$dependance."\n";
				} else
					$enregistrer_paquet->changer_control(false, 'Depends');
				if(!empty($predependance)) {
					$enregistrer_paquet->changer_control($predependance, 'Pre-Depends');
					$predependance = "Pre-Depends: ".$predependance."\n";
				} else
					$enregistrer_paquet->changer_control(false, 'Pre-Depends');
				if(!empty($conflit)) {
					$enregistrer_paquet->changer_control($conflit, 'Conflicts');
					$conflit = "Conflicts: ".$conflit."\n";
				} else
					$enregistrer_paquet->changer_control(false, 'Conflicts');
				$enregistrer_paquet->changer_control(false, 'Installed-Size');
				$enregistrer_paquet->changer_control(false, 'Priority');
				$enregistrer_paquet->changer_control(false, 'Replaces');
				$enregistrer_paquet->changer_control($membre->_id, 'id_membre');
				$site_url = config('url');

				//Modifier le control original du deb
				unlink('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control');
				$file = fopen('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control', 'w');
				fwrite($file, "Package: org.goldencydia.".$clean."\nName: ".$nom."\nVersion: ".$version."\nArchitecture: iphoneos-arm\nDescription: ".$description."\nDepiction: ".$site_url."pack/".$clean."\nAuthor: ".$auteur."\nSection: ".$_POST["categorie"]."\n".$predependance."".$dependance."".$conflit."".$icon."\n");
				fclose($file);

				//Compression en deb
				shell_exec("cd includes/admin/temp/".$membre->_pseudo."/DEBIAN/ && tar czvf control.tar.gz *");
				chmod('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/control.tar.gz', 0775);
				chmod('includes/admin/temp/'.$membre->_pseudo.'.deb', 0775);
				shell_exec("cd includes/admin/temp/ && ar r ".escapeshellarg($membre->_pseudo.".deb")." ".escapeshellarg($membre->_pseudo."/DEBIAN/control.tar.gz")."");

				//Crée les repertoires
				if(!empty($_GET['id']) && $_GET['id'] !== $clean) {
					if(file_exists('debs/org.goldencydia.'.$_GET['id'].'/org.goldencydia.'.$_GET['id'].'.deb'))
						unlink('debs/org.goldencydia.'.$_GET['id'].'/org.goldencydia.'.$_GET['id'].'.deb');
					if(is_dir('debs/org.goldencydia.'.$_GET['id']))
						rename('debs/org.goldencydia.'.$_GET['id'], 'debs/org.goldencydia.'.$clean);
					if(!is_dir("debs/org.goldencydia.$clean")) {
						mkdir("debs/org.goldencydia.$clean", 0775, true);
						if($etat) {
							tweet('Update : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package updated under the identifier : %s ! Tweeted as updated.'), $clean);
						} else
							$success[] = sprintf(_('Package updated under the identifier : %s !'), $clean);
					} else {
						if($etat) {
							tweet('Update : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package updated under the identifier : %s ! Tweeted as updated.'), $clean);
						} else
							$success[] = sprintf(_('Package updated under the identifier : %s !'), $clean);
					}
					$remove = new Paquet($_GET['id']);
					$infos_remove = $remove->package_control(array('description1', 'changelog', 'total_votes', 'total_value', 'used_ips'));
					if(file_exists('images/debs/'.$_GET['id'].'.png'))
						rename('images/debs/'.$_GET['id'].'.png', 'images/debs/'.$clean.'.png');
					if(is_dir('images/debs/'.$_GET['id']))
						rename('images/debs/'.$_GET['id'], 'images/debs/'.$clean);
					$enregistrer_paquet->changer_control($infos_remove['description1'], 'description1');
					$enregistrer_paquet->changer_control($infos_remove['changelog'], 'changelog');
					if($infos_remove['total_votes'])
						$enregistrer_paquet->changer_control($infos_remove['total_votes'], 'total_votes');
					if($infos_remove['total_value'])
						$enregistrer_paquet->changer_control($infos_remove['total_value'], 'total_value');
					$enregistrer_paquet->changer_control($infos_remove['used_ips'], 'used_ips');
					$download_update = $pdo->prepare("UPDATE download SET package = :package WHERE package = :id");
					$download_update->execute(array(':id' => $_GET['id'], ':package' => $clean));
					$remove->supprimer_definitivement();
				} elseif($update && $_GET['update'] !== $clean) {
					if(file_exists('debs/org.goldencydia.'.$_GET['update'].'/org.goldencydia.'.$_GET['update'].'.deb'))
						unlink('debs/org.goldencydia.'.$_GET['update'].'/org.goldencydia.'.$_GET['update'].'.deb');
					if(is_dir('debs/org.goldencydia.'.$_GET['update']))
						rename('debs/org.goldencydia.'.$_GET['update'], 'debs/org.goldencydia.'.$clean);
					if(!is_dir("debs/org.goldencydia.$clean")) {
						mkdir("debs/org.goldencydia.$clean", 0775, true);
						if($etat) {
							tweet('Update : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package updated under the identifier : %s ! Tweeted as updated.'), $clean);
						} else
							$success[] = sprintf(_('Package updated under the identifier : %s !'), $clean);
					} else {
						if($etat) {
							tweet('Update : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package updated under the identifier : %s ! Tweeted as updated.'), $clean);
						} else
							$success[] = sprintf(_('Package updated under the identifier : %s !'), $clean);
					}
					$remove = new Paquet($_GET['update']);
					$infos_remove = $remove->package_control(array('description1', 'changelog', 'total_votes', 'total_value', 'used_ips'));
					if(file_exists('images/debs/'.$_GET['update'].'.png'))
						rename('images/debs/'.$_GET['update'].'.png', 'images/debs/'.$clean.'.png');
					if(is_dir('images/debs/'.$_GET['update']))
						rename('images/debs/'.$_GET['update'], 'images/debs/'.$clean);
					$enregistrer_paquet->changer_control($infos_remove['description1'], 'description1');
					$enregistrer_paquet->changer_control($infos_remove['changelog'], 'changelog');
					if($infos_remove['total_votes'])
						$enregistrer_paquet->changer_control($infos_remove['total_votes'], 'total_votes');
					if($infos_remove['total_value'])
						$enregistrer_paquet->changer_control($infos_remove['total_value'], 'total_value');
					$enregistrer_paquet->changer_control($infos_remove['used_ips'], 'used_ips');
					$download_update = $pdo->prepare("UPDATE download SET package = :package WHERE package = :id");
					$download_update->execute(array(':id' => $_GET['update'], ':package' => $clean));
					$remove->supprimer_definitivement();
				} else {
					if(file_exists('debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb') && $update)
						unlink('debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb');
					if(!is_dir("debs/org.goldencydia.$clean")) {
						mkdir("debs/org.goldencydia.$clean", 0775, true);
						if($etat) {
							tweet('New : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package added under the identifier : %s ! Tweeted as new.'), $clean);
						} else
							$success[] = sprintf(_('Package added under the identifier : %s !'), $clean);
					} else {
						if($etat) {
							tweet('Update : '.$nom.' - '.$version.' - '.$site_url.'pack/'.$clean.' #cydia');
							$success[] = sprintf(_('Package updated under the identifier : %s ! Tweeted as updated.'), $clean);
						} else
							$success[] = sprintf(_('Package updated under the identifier : %s !'), $clean);
					}
				}

				// deplace le deb
				rename('includes/admin/temp/'.$membre->_pseudo.'.deb', 'debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb');


				// Verification de l'etat
				if($etat && file_exists('debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb')) {
					$etat = true;
				} else
					$etat = false;
				// Suppression de tmp
				rrmdir('includes/admin/temp/'.$membre->_pseudo);
				if(is_dir('includes/admin/temp/'.$membre->_pseudo))
					$errors[] = _('Cleaning Error !');

		                // Ajout des dernières infos a la bdd
				$relachermd5 = md5_file("debs/org.goldencydia.$clean/org.goldencydia.$clean.deb");
				$taille = filesize("debs/org.goldencydia.$clean/org.goldencydia.$clean.deb");

				$enregistrer_paquet->changer_control($taille, 'Size');
				$enregistrer_paquet->changer_control($relachermd5, 'Md5sum');
				$enregistrer_paquet->changer_control($etat, 'online');
				$enregistrer_paquet->changer_control($etat, 'tweet');
				$enregistrer_paquet->changer_control(date('Y-m-d H:i:s', fileatime('debs/org.goldencydia.'.$clean.'/')), 'date');
				$enregistrer_paquet->changer_control(date('Y-m-d H:i:s', fileatime('debs/org.goldencydia.'.$clean.'/org.goldencydia.'.$clean.'.deb')), 'date_update');
				if($etat) {
					if(file_exists('includes/cache/Packages.bz2'))
						unlink('includes/cache/Packages.bz2');
					if(file_exists('includes/cache/dpt-update.txt'))
						unlink('includes/cache/dpt-update.txt');
					if(file_exists('includes/cache/nouveautes.txt'))
						unlink('includes/cache/nouveautes.txt');
					if(file_exists('includes/cache/totaldebs.txt'))
						unlink('includes/cache/totaldebs.txt');
					$enregistrer_paquet->supprimer_cache();
				}
				$reussi = true;
				$pdo = PDO2::closeInstance();
			}
		}
		if(file_exists('includes/admin/sections.txt')) {
			$sections = file_get_contents('includes/admin/sections.txt');
			$sections = explode("\n", $sections);
		}
		$site_nom = config('nom');
 ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php if($update && !$reussi) echo _('Update').' '.$update;else echo _('Add a package'); ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">
		<div class="panel-heading">
			<h2 class="text-primary"><?php if($update && !$reussi) echo _('Update').' '.$update;else echo _('Add a package'); ?></h2>
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
		<?php if($upload == false OR $reussi == true){ ?>
			<form class="jumbotron form-horizontal" method="post" enctype="multipart/form-data" action="<?php if($update && !$reussi) echo 'upload.php?update='.$_GET['update'];else echo 'upload.php'; ?>"><fieldset>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="file"><?php echo _('File .deb'); ?></label>
					<div class="col-sm-10">
						<input required="required" type="file" class="form-control" name="file" id="file" />
						<span class="help-text"><?php echo _('Choose a .deb file'); ?></span>
					</div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-block"><?php echo _('Upload'); ?></button>
				</div>
			</fieldset></form>
		<?php } if($upload == true && $reussi == false){ ?>
			<div class="tabbable-panel">
				<div class="tabbable-line">
					<?php if($update) { ?>
					<ul class="nav nav-tabs text-center">
						<li class="active">
							<a href="#tab_update" data-toggle="tab"><?php echo _('Update'); ?> </a>
						</li>
						<li>
							<a href="#tab_changelog" data-toggle="tab"><?php echo _('Recent changes'); ?> </a>
						</li>
					</ul>
					<?php } ?>
					<div class="tab-content jumbotron">
						<div class="tab-pane fade in active" id="tab_update">
        				        	<form class="form-horizontal" method="post" enctype="multipart/form-data"><fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="identifiant"><?php echo _('Identifier'); ?>*</label>
									<div class="col-sm-10">
										<div class="input-group">
											<span class="input-group-addon">org.goldencydia.</span>
											<input type="text" class="form-control" required="required" name="identifiant" id="identifiant" value="<?php echo $clean; ?>" />
										</div>
										<?php if($infos['Package'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Package'].'</span>';}else{ ?><span class=="help-text"><?php echo _('This form will automatically add the prefix'); ?> 'org.goldencydia.'</span><?php } ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="nom"><?php echo _('Name'); ?>*</label>
									<div class="col-sm-10">
										<input class="form-control" required="required" type="text" name="nom" id="nom" value="<?php echo $nom; ?>" />
										<?php if($infos['Name'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Name'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label for="categorie" class="col-sm-2 control-label"><?php echo _('Section'); ?>*</label>
									<div class="col-sm-10">
										<select class="form-control" name="categorie" id="categorie" required="required">
											<option <?php if(empty($section)){echo 'selected';} ?>><?php echo _('Choose a section'); ?></option>
											<?php foreach ($sections as $customSection) {
												echo '<option';
												if($section == $customSection)
													echo ' selected="selected"';
												echo '>'.$customSection.'</option>';
											} ?>
											<option <?php if($section=='Addons'){echo 'selected="selected"';} ?>>Addons</option>
											<option <?php if($section=='Administration'){echo 'selected';} ?>>Administration</option>
											<option <?php if($section=='Archiving'){echo 'selected';} ?>>Archiving</option>
											<option <?php if($section=='Carrier Bundles'){echo 'selected';} ?>>Carrier Bundles</option>
											<option <?php if($section=='Data Storage'){echo 'selected';} ?>>Data Storage</option>
											<option <?php if($section=='Development'){echo 'selected';} ?>>Development</option>
											<option <?php if($section=='Dictionaries'){echo 'selected';} ?>>Dictionaries</option>
											<option <?php if($section=='Education'){echo 'selected';} ?>>Education</option>
											<option <?php if($section=='Entertainment'){echo 'selected';} ?>>Entertainment</option>
											<option <?php if($section=='Fonts'){echo 'selected';} ?>>Fonts</option>
											<option <?php if($section=='Games'){echo 'selected';} ?>>Games</option>
											<option <?php if($section=='Java'){echo 'selected';} ?>>Java</option>
											<option <?php if($section=='Keyboards'){echo 'selected';} ?>>Keyboards</option>
											<option <?php if($section=='Localization'){echo 'selected';} ?>>Localization</option>
											<option <?php if($section=='Messaging'){echo 'selected';} ?>>Messaging</option>
											<option <?php if($section=='Multimedia'){echo 'selected';} ?>>Multimedia</option>
											<option <?php if($section=='Navigation'){echo 'selected';} ?>>Navigation</option>
											<option <?php if($section=='Networking'){echo 'selected';} ?>>Networking</option>
											<option <?php if($section=='Packaging'){echo 'selected';} ?>>Packaging</option>
											<option <?php if($section=='Productivity'){echo 'selected';} ?>>Productivity</option>
											<option <?php if($section=='Repositories'){echo 'selected';} ?>>Repositories</option>
											<option <?php if($section=='Ringtones'){echo 'selected';} ?>>Ringtones</option>
											<option <?php if($section=='Scripting'){echo 'selected';} ?>>Scripting</option>
											<option <?php if($section=='Security'){echo 'selected';} ?>>Security</option>
											<option <?php if($section=='Site-Specific Apps'){echo 'selected';} ?>>Site-Specific Apps</option>
											<option <?php if($section=='Soundboards'){echo 'selected';} ?>>Soundboards</option>
											<option <?php if($section=='System'){echo 'selected';} ?>>System</option>
											<option <?php if($section=='Terminal Support'){echo 'selected';} ?>>Terminal Support</option>
											<option <?php if($section=='Themes'){echo 'selected="selected"';} ?>>Themes</option>
											<option <?php if($section=='Toys'){echo 'selected="selected"';} ?>>Toys</option>
											<option <?php if($section=='Tweaks'){echo 'selected="selected"';} ?>>Tweaks</option>
											<option <?php if($section=='Utilities'){echo 'selected="selected"';} ?>>Utilities</option>
											<option <?php if($section=='Wallpaper'){echo 'selected="selected"';} ?>>Wallpaper</option>
											<option <?php if($section=='Widgets'){echo 'selected="selected"';} ?>>Widgets</option>
											<option <?php if($section=='X Window'){echo 'selected="selected"';} ?>>X Window</option>
										</select>
										<?php if($infos['Section'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Section'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="version"><?php echo _('Version'); ?>*</label>
									<div class="col-sm-10">
										<input class="form-control" required="required" type="text" name="version" id="version" value="<?php echo $version; ?>" />
										<?php if($infos['Version'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Version'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="auteur"><?php echo _('Author'); ?>*</label>
									<div class="col-sm-10">
										<input class="form-control" required="required" type="text" name="auteur" id="auteur" value="<?php echo $auteur; ?>" />
										<?php if($infos['Author'] != false) {echo '<span class="help-block">'._('Current').' : '.htmlspecialchars($infos['Author']).'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="description"><?php echo _('Short description'); ?>*</label>
									<div class="col-sm-10">
										<textarea class="form-control" required="required" name="description" id="description"><?php echo trim($description); ?></textarea>
										<?php if($infos['Description'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Description'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="dependance"><?php echo _('Depends'); ?></label>
									<div class="col-sm-10">
										<input class="form-control" type="text" name="dependance" value="<?php echo $dependance; ?>" id="dependance" />
										<?php if($infos['Depends'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Depends'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="predependance"><?php echo _('Pre-depends'); ?></label>
									<div class="col-sm-10">
										<input class="form-control" type="text" name="predependance" value="<?php echo $predependance; ?>" id="predependance" />
										<?php if($infos['Pre-Depends'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Pre-Depends'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="conflit"><?php echo _('Conflicts'); ?></label>
									<div class="col-sm-10">
										<input class="form-control" type="text" name="conflit" value="<?php echo $conflit; ?>" id="conflit" />
										<?php if($infos['Conflicts'] != false) {echo '<span class="help-block">'._('Current').' : '.$infos['Conflicts'].'</span>';} ?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="icon"><?php echo _('Icon'); ?></label>
									<div class="col-sm-10">
										<input class="form-control" type="text" name="icon" value="<?php echo $icon; ?>" id="icon" />
										<?php if($infos['Icon'] != false) {echo '<span class="help-block">'._('Current').' : ';echo $infos['Icon'];echo '</span>';} ?>
									</div>
								</div>
								<center><div class="text-center btn-group" data-toggle="buttons">
									<label class="btn btn-primary<?php if($etat) echo ' active'; ?>">
										<input type="radio" name="online" id="online" value="online"<?php if($etat) echo ' checked'; ?>> <?php echo _('Online'); ?>
									</label>
									<label class="btn btn-danger<?php if(!$etat) echo ' active'; ?>">
										<input type="radio" name="online" id="offline" value="offline"<?php if(!$etat) echo ' checked'; ?>> <?php echo _('Offline'); ?>
									</label>
								</div></center><br />
								<?php $dir = opendir('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/') or die('Listing Error: The directory can not be opened.');
								$i = 0;
								while($element = @readdir($dir)) {
									if($element != '.' && $element != '..' && $element != 'control' && !is_dir($element)) {
										$titre[] = $element;
										$contenu[] = @file('includes/admin/temp/'.$membre->_pseudo.'/DEBIAN/'.$element);
										$fichier_extra[$titre[$i]] = $contenu[$i];
										$i++;
									}
								}
								if(isset($fichier_extra) && is_array($fichier_extra)) {
									$j = 1;
									echo '<div class="panel-group" id="accordion">';
									foreach($fichier_extra as $title => $content) {
										echo '<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$j.'">'._('File').' '.$title.'</a><span class="btn-group pull-right" data-toggle="buttons"><label class="btn btn-danger" style="line-height:0.5"><input type="checkbox" name="remove[]" value="'.$title.'">'._('Remove').'</label></span></h4></div>';
										echo '<div id="collapse'.$j.'" class="panel-collapse collapse"><div class="panel-body">';
										foreach($content as $ligne) {
											echo htmlspecialchars($ligne).'<br />';
										}
										$j++;
										echo '</div></div></div>';
									}
									echo '</div>';
								}
								echo '<div class="well"><h4 class="text-center">'._('Package contents:').'</h4><hr />'.explore('includes/admin/temp/'.$membre->_pseudo).'</div>'; ?>
								<div class="form-group">
									<button type="submit" class="btn btn-primary btn-block"><?php echo _('Save'); ?></button>
								</div>
							</fieldset></form>
						</div>
						<?php if($update) { ?>
						<div class="tab-pane fade" id="tab_changelog">
							<form class="form-horizontal" method="post" id="changelog" action="/ajax/changelog.php?id=<?php echo $_GET['update']; ?>"></fieldset>
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
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<?php if($update && $upload) { ?>
		<script>$("#changelog").submit(function(e){$("#errorChangelog").html('<div class="text-center"><img src="images/ajax-loader.gif" /></div>');var changelog = tinyMCE.get("changelogTexte").getContent();$.ajax({url : $(this).attr("action"),type: "POST",data : "changelog="+changelog, success:function(data){if(data.numError == '0'){$("#errorChangelog").html('<p class="text-center text-danger">'+data.error+'</p>');} else {$("#errorChangelog").html('<p class="text-center text-success">'+data.error+'</p>');}},error:function(error) {$("#errorChangelog").html('<p class="text-center text-danger"><?php echo _('Can not connect ! Error '); ?>'+error.responseText+'</p>');}});e.preventDefault();});</script>
		<script src="js/tinymce/tinymce.min.js"></script>
		<script>tinymce.PluginManager.load('moxiemanager', '/js/tinymce/plugins/moxiemanager/plugin.min.js');tinymce.init({selector: "#changelogTexte",plugins: ["advlist autolink lists link image charmap print preview hr anchor pagebreak","searchreplace wordcount visualblocks visualchars code fullscreen","insertdatetime media table contextmenu","paste textcolor moxiemanager"],toolbar1: "undo redo | styleselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media print preview",max_height: 350,min_height: 200,height : 250});</script>
	<?php } ?>
</body>
</html>
<?php } else
	require_once('404.php'); ?>