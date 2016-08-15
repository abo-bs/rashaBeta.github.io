<?php require_once('includes/session.class.php');
$membre = new Session();
	if ($membre->_connected && $membre->_level > 0) {
		require_once('includes/time-header.php');
		require_once('includes/package.class.php');
		translation();
		$errors = array();
		$success = array();
		$pdo = PDO2::getInstance();
		$settings = Session::checkSettings('manage-all', array('computer' => array('perPage' => 25, 'state' => true, 'section' => true, 'size' => true, 'author' => true, 'downloaded' => true, 'rating' => true, 'uploader' => true, 'views' => true), 'mobile' => array('perPage' => 25)));
		if(!empty($_POST)) {
			if($_POST['perPage'] < 1 || $_POST['perPage'] > 99 || !is_numeric($_POST['perPage']))
				$errors[] = _('Number of packages must be an integer between 1 and 99 !');
			if(empty($errors)) {
				Session::setSettings('manage-all');
				header('Location: manage-all.php?settingsupdated');
			}
		}
		if(isset($_GET['settingsupdated']))
			$success[] = _('Your settings have been saved !');
		if($settings['perPage'] < 1 || $settings['perPage'] > 99 || !is_numeric($settings['perPage'])) {
			$settings = Session::checkSettings('manage-all', array('computer' => array('perPage' => 25, 'state' => true, 'section' => true, 'size' => true, 'author' => true, 'downloaded' => true, 'rating' => true, 'uploader' => true, 'views' => true), 'mobile' => array('perPage' => 25)), true);
			header('Location: manage-all.php');
		}
		if(!empty($_GET['id'])) {
			if(is_array($_GET['id'])) {
				$ids = $_GET['id'];
				$paquets = array();
				foreach($ids as $id) {
					$paquets[] = new Paquet($id);
				}
				foreach($paquets as $test_paquet) {
					$infos = $test_paquet->package_control(array('online', 'id_membre', 'Name', 'tweet', 'Version', 'date_update', 'date'));
					if($test_paquet->verifier_fiche()) {
						if($membre->_level > 2 || $membre->_id === $infos['id_membre']) {
							if(isset($_GET['online'])) {
								if(!$infos['online']) {
									if($test_paquet->verifier_deb()) {
										$test_paquet->changer_control(true, 'online');
										if(file_exists('includes/cache/Packages.bz2'))
											unlink('includes/cache/Packages.bz2');
										if(file_exists('includes/cache/dpt-update.txt'))
											unlink('includes/cache/dpt-update.txt');
										if(file_exists('includes/cache/nouveautes.txt'))
											unlink('includes/cache/nouveautes.txt');
										if(file_exists('includes/cache/totaldebs.txt'))
											unlink('includes/cache/totaldebs.txt');
										if(!$infos['tweet']) {
											$updt = date_format(date_create($infos['date_update']), "H d/m/Y");
											$new = date_format(date_create($infos['date']), "H d/m/Y");
											$site_url = config('url');
											if($updt == $new) {
												$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date');
												$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date_update');
												$success[] = sprintf(_('%s has been put online and tweeted as new !'), $infos['Name']);
												tweet('New : '.$infos['Name'].' - '.$infos['Version'].' - '.$site_url.'pack/'.$test_paquet->getId().' #cydia');
											} else {
												$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date_update');
												$success[] = sprintf(_('%s has been put online and tweeted as updated !'), $infos['Name']);
												tweet('Update : '.$infos['Name'].' - '.$infos['Version'].' - '.$site_url.'pack/'.$test_paquet->getId().' #cydia');
											}
											unset($updt);
											unset($new);
											$test_paquet->changer_control(true, 'tweet');
										} else
											$success[] = sprintf(_('%s has been put online !'), $infos['Name']);
									} else
										$errors[] = _('No package found');
								}
							} elseif(isset($_GET['offline'])) {
								if($infos['online']) {
									$test_paquet->changer_control(false, 'online');
									if(file_exists('includes/cache/Packages.bz2'))
										unlink('includes/cache/Packages.bz2');
									if(file_exists('includes/cache/dpt-update.txt'))
										unlink('includes/cache/dpt-update.txt');
									if(file_exists('includes/cache/nouveautes.txt'))
										unlink('includes/cache/nouveautes.txt');
									if(file_exists('includes/cache/totaldebs.txt'))
										unlink('includes/cache/totaldebs.txt');
									$success[] = sprintf(_('%s has been put offline !'), $infos['Name']);
								}
							}
						} else
							$errors[] = _('You are not allowed to edit this package !');
					} else
						$errors[] = _('No package found');
					unset($infos);
					unset($test_paquet);
				}
				unset($paquets);
			} else {
				$test_paquet = new Paquet($_GET['id']);
				$infos = $test_paquet->package_control(array('online', 'id_membre', 'Name', 'tweet', 'Version', 'date_update', 'date'));
				if($test_paquet->verifier_fiche()) {
					if($membre->_level > 2 || $membre->_id === $infos['id_membre']) {
						if(isset($_GET['online'])) {
							if(!$infos['online']) {
								if($test_paquet->verifier_deb()) {
									$test_paquet->changer_control(true, 'online');
									if(file_exists('includes/cache/Packages.bz2'))
										unlink('includes/cache/Packages.bz2');
									if(file_exists('includes/cache/dpt-update.txt'))
										unlink('includes/cache/dpt-update.txt');
									if(file_exists('includes/cache/nouveautes.txt'))
										unlink('includes/cache/nouveautes.txt');
									if(file_exists('includes/cache/totaldebs.txt'))
										unlink('includes/cache/totaldebs.txt');
									if(!$infos['tweet']) {
										$updt = date_format(date_create($infos['date_update']), "H d/m/Y");
										$new = date_format(date_create($infos['date']), "H d/m/Y");
										$site_url = config('url');
										if($updt == $new) {
											$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date');
											$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date_update');
											$success[] = sprintf(_('%s has been put online and tweeted as new !'), $infos['Name']);
											tweet('New : '.$infos['Name'].' - '.$infos['Version'].' - '.$site_url.'pack/'.$_GET['id'].' #cydia');
										} else {
											$test_paquet->changer_control(date('Y-m-d H:i:s'), 'date_update');
											$success[] = sprintf(_('%s has been put online and tweeted as updated !'), $infos['Name']);
											tweet('Update : '.$infos['Name'].' - '.$infos['Version'].' - '.$site_url.'pack/'.$_GET['id'].' #cydia');
										}
										unset($updt);
										unset($new);
										$test_paquet->changer_control(true, 'tweet');
									} else
										$success[] = sprintf(_('%s has been put online !'), $infos['Name']);
								} else
									$errors[] = _('No package found');
							}
						} elseif(isset($_GET['offline'])) {
							if($infos['online']) {
								$test_paquet->changer_control(false, 'online');
								if(file_exists('includes/cache/Packages.bz2'))
									unlink('includes/cache/Packages.bz2');
								if(file_exists('includes/cache/dpt-update.txt'))
									unlink('includes/cache/dpt-update.txt');
								if(file_exists('includes/cache/nouveautes.txt'))
									unlink('includes/cache/nouveautes.txt');
								if(file_exists('includes/cache/totaldebs.txt'))
									unlink('includes/cache/totaldebs.txt');
								$success[] = sprintf(_('%s has been put offline !'), $infos['Name']);
							}
						}
					} else
						$errors[] = _('You are not allowed to edit this package !');
				} else
					$errors[] = _('No package found');
				unset($infos);
				unset($test_paquet);
			}
		}
		if(isset($_GET['ord']) && $_GET['ord'] == 0) {
			$ordre = 0;$rangement = "ASC";
		} else {
			$ordre = 1;$rangement = "DESC";
		}
		if(isset($_GET['type'])) {
			switch($_GET['type']) {
				case 9:$type = 9;$order = "visits";break;
				case 8:$type = 8;$order = "pseudo";break;
				case 7:$type = 7;$order = 'telechargements';break;
				case 6:$type = 6;$order = 'total_votes '.$rangement.', (total_value / total_votes)';break;
				case 4:$type = 4;$order = 'Size';break;
				case 3:$type = 3;$order = 'Section';break;
				case 2:$type = 2;$order = 'Author';break;
				case 1:$type = 1;$order = 'Name';break;
				default: $type = 0;$order = 'date_update';
			}
		} else {
			$type = 0;
			$order = 'date_update';
		}

		$section_search = (!empty($_GET['section'])) ? urldecode(addslashes(trim($_GET['section']))) : urlencode('all');
		$section_req = (!empty($_GET['section']) && $_GET['section'] != 'all') ? ' AND description.Section = "'.$section_search.'"' : '';

		$search = (!empty($_GET['s'])) ? addslashes(preg_replace(array('#\%#', '#\_#'), array('\%', '\_'), trim($_GET['s']))) : '';

		if(isset($_GET['champs'])) {
			switch($_GET['champs']) {
				case 'all': $champs = '(Name LIKE "%'.$search.'%" OR Author LIKE "%'.$search.'%" OR Package LIKE "%'.$search.'%" OR Description LIKE "%'.$search.'%" OR Description1 LIKE "%'.$search.'%" OR pseudo LIKE "%'.$search.'%")';$champ = 'all';break;
				case 'Name': $champs = 'Name LIKE "%'.$search.'%"';$champ = 'Name';break;
				case 'Author': $champs = 'Author LIKE "%'.$search.'%"';$champ = 'Author';break;
				case 'Package': $champs = 'Package LIKE "%'.$search.'%"';$champ = 'Package';break;
				case 'Description': $champs = 'Description LIKE "%'.$search.'%"';$champ = 'Description';break;
				case 'Description1': $champs = 'Description1 LIKE "%'.$search.'%"';$champ = 'Description1';break;
				case 'pseudo': $champs = "pseudo LIKE '%".$search."%'";$champ = "pseudo";break;
				default: $champs = 'Name LIKE "%'.$search.'%"';$champ = 'Name';break;
			}
		} else {
			$champs = 'Name LIKE "%'.$search.'%"';
			$champ = 'Name';
		}

		$req1 = $pdo->prepare('SELECT COUNT(description.id) FROM description INNER JOIN description_meta ON description.id = description_meta.id INNER JOIN membre ON description_meta.id_membre = membre.id WHERE '.$champs.$section_req);
		$req1->execute();
		$req1 = $req1->fetchColumn();
		$fin = ceil($req1 / $settings['perPage']);
		$page = (!empty($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] - 1) < $fin && $_GET['page'] > 0) ? preg_replace("/[^0-9]/", '', $_GET['page']) : 1;
		$debut = ($page - 1) * $settings['perPage'];
		$resultat = ($req1 < 2) ? _('result') : _('results');
		$precedent = $page - 1;
		$suivant = $page + 1;
		$req = $pdo->prepare('SELECT description.id, package, Name, Version, Author, Section, Size, description_meta.date, date_update, online, id_membre, total_value, total_votes, total_download AS telechargements, pseudo, visits FROM description INNER JOIN description_meta ON description.id = description_meta.id INNER JOIN membre ON description_meta.id_membre = membre.id WHERE '.$admin.$champs.$section_req.' ORDER BY online ASC, '.$order.' '.$rangement.' LIMIT '.$debut.', '.$settings['perPage']);
		$req->execute();
		$req = $req->fetchAll(PDO::FETCH_ASSOC);
		$sections = $pdo->prepare("SELECT DISTINCT(Section) FROM description ORDER BY Section ASC");
		$sections->execute();
		$sections = $sections->fetchAll(PDO::FETCH_ASSOC);
		$site_nom = config('nom');
		$pdo = PDO2::closeInstance(); ?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php if(!empty($_GET['s'])) echo _('Search');else echo _('Manage all packages');if($page>1){echo ' - Page '.$page;} ?> - <?php echo $site_nom; ?></title>
<?php require_once('includes/admin/header.php'); ?>
	<div class="container">

		<div id="accordion" role="tablist" aria-multiselectable="true">
			<div class="panel" style="margin:0;background:transparent;border:0;box-shadow:0">
				<div id="filter" class="collapse jumbotron cleanTop" aria-labelledby="filterLabel">
					<div>
						<form method="post">
							<h5 style="margin-top:0;padding-top:10px"><?php echo _('On the screen'); ?></h5>
							<div class="checkbox">
								<ul class="list-inline">
									<li>
										<div class="checkbox">
											<input id="state" type="checkbox" name="state"<?php if(isset($settings['state']))echo' checked'; ?>></input>
											<label for="state"><?php echo _('State'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="section" type="checkbox" name="section"<?php if(isset($settings['section']))echo' checked'; ?>></input>
											<label for="section"><?php echo _('Section'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="size" type="checkbox" name="size"<?php if(isset($settings['size']))echo' checked'; ?>></input>
											<label for="size"><?php echo _('Size'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="author" type="checkbox" name="author"<?php if(isset($settings['author']))echo' checked'; ?>></input>
											<label for="author"><?php echo _('Author'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="downloaded" type="checkbox" name="downloaded"<?php if(isset($settings['downloaded']))echo' checked'; ?>></input>
											<label for="downloaded"><?php echo _('Downloaded'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="rating" type="checkbox" name="rating"<?php if(isset($settings['rating']))echo' checked'; ?>></input>
											<label for="rating"><?php echo _('Rating'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="uploader" type="checkbox" name="uploader"<?php if(isset($settings['uploader']))echo' checked'; ?>></input>
											<label for="uploader"><?php echo _('Uploader'); ?></label>
										</div>
									</li>
									<li>
										<div class="checkbox">
											<input id="views" type="checkbox" name="views"<?php if(isset($settings['views']))echo' checked'; ?>></input>
											<label for="views"><?php echo _('Views'); ?></label>
										</div>
									</li>
								</ul>
								<?php if(type_device() == 'iPhone' || type_device() == 'iPad' || type_device() == 'iPod')
									echo '<p class="help-block">'._('On mobile, you can\'t display alot of columns, sorry').'</p>'; ?>
							</div><hr />
							<div class="form-group">
								<ul class="list-inline">
									<li>
										<div class="checkbox">
											<input id="perPage" type="number" value="<?php echo $settings['perPage']; ?>" maxlength="3" name="perPage" max="999" min="1" step="1"></input>
											<label for="perPage"><?php echo _('Packages'); ?></label>
										</div>
									</li>
									<li>
										<button type="submit" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-ok"></span></button>
									</li>
								</ul>
							</div>
						</form>
					</div>
				</div>
				<div id="help" class="collapse jumbotron cleanTop" aria-labelledby="helpLabel">
					<div>
						<h4 style="margin-top:0;padding-top:10px"><?php echo _('Help'); ?></h4>
						<p><?php echo _('Here, you can manage packages.');
						switch($membre->_level) {
							case 5:
								echo _('You\'re allowed to upload and edit all packages.');
								break;
							case 4:
								echo _('You\'re allowed to upload and edit all packages.');
								break;
							case 3:
								echo _('You\'re allowed to upload and edit all packages.');
								break;
							case 2:
								echo _('You\'re only allowed to upload and edit your packages.');
								break;
							case 1:
								echo _('You\'re only allowed to upload and edit your packages.');
								break;
							default:
								echo _('You can\'t see this part, what\'s the fuck ?!');
						} ?></p>
					</div>
				</div>
			</div>
			<div class="tabbable-line pull-right">
				<ul class="nav nav-tabs text-center">
					<li role="tab" id="filterLabel" class="cleanBottom">
						<a class="cleanBottom collapsed" data-toggle="collapse" data-parent="#accordion" href="#filter" aria-expanded="false" aria-controls="filter"><span class="glyphicon glyphicon-filter"></span></a>
					</li>
					<li role="tab" id="helpLabel" class="cleanBottom">
						<a class="cleanBottom collapsed" data-toggle="collapse" data-parent="#accordion" href="#help" aria-expanded="false" aria-controls="help"><span class="glyphicon glyphicon-question-sign"></span></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="panel-heading">
			<h2 class="text-primary"><?php if(!empty($_GET['s'])) echo _('Search').' <small>'.@number_format($req1, 0, ', ', ' ').' '.$resultat.'</small>';else echo _('Manage all packages').' <small>'.@number_format($req1, 0, ', ', ' ').' '.strtolower(_('Packages')).'</small>'; ?></h2>
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
			<form class="navbar-form text-center" style="box-shadow:0 0 0">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="<?php echo _('Search').'..'; ?>" name="s" value="<?php echo htmlspecialchars(stripslashes(preg_replace(array('#\\\%#', '#\\\_#'), array('%', '_'), $search))); ?>" />
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="champs" class="form-control">
						<option value="all"><?php echo _('All fields'); ?></option>
						<option value="Package"<?php if($champ == "Package") echo ' selected'; ?>><?php echo _('Identifiers'); ?></option>
						<option value="Name"<?php if($champ == "Name") echo ' selected'; ?>><?php echo _('Names'); ?></option>
						<option value="Author"<?php if($champ == "Author") echo ' selected'; ?>><?php echo _('Authors'); ?></option>
						<option value="Description"<?php if($champ == "Description") echo ' selected'; ?>><?php echo _('Short descriptions'); ?></option>
						<option value="Description1"<?php if($champ == "Description1") echo ' selected'; ?>><?php echo _('Complete descriptions'); ?></option>
						<option value="pseudo"<?php if($champ == "pseudo") echo ' selected'; ?>><?php echo _('Uploaders'); ?></option>
					</select>
					<span style="color:#fff;text-shadow:0 0 2.5px #000;font-size:1.5em"> <?php echo _('in'); ?> </span>
					<select name="section" class="form-control">
						<option value="all"><?php echo _('All sections'); ?></option>
						<?php foreach($sections as $section) {
							echo '<option';
							if($section_search == $section['Section'])
								echo ' selected';
							echo '>'.$section['Section'];
							echo '</option>';
						} ?>
					</select>
				</div>
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php if(!empty($req)) { ?>
				<form id="multiAction">
					<p style="margin:-10px auto 5px" class="text-center visible displayMulti"><button class="btn btn-xs btn-primary" type="button" onclick="$('.hidden').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('hidden visible', 1000 );"><?php echo _('Multiple choise'); ?></button></p>
					<p style="margin:-10px auto 5px" class="text-center hidden">
						<select class="btn-xs btn-primary selectActionTop">
							<option><?php echo _('Select an option'); ?></option>
							<option value="online"><?php echo _('Put online'); ?></option>
							<option value="offline"><?php echo _('Put offline'); ?></option>
							<option value="delete"><?php echo _('Delete'); ?></option>
						</select>
						<button class="btn btn-xs btn-primary" type="submit"><?php echo _('Action'); ?></button>
						<button class="btn btn-xs btn-primary" type="button" onclick="$('.visible').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('visible hidden', 1000 );"><?php echo _('Hide'); ?></button>
					</p>
					<table class="table table-hover table-condensed table-responsive" style="border-bottom:1px solid;margin:0">
						<tr>
						<th style="border-color:#000 !important" class="hidden text-center allMulti"><input type="checkbox" name="allMulti" id="allMulti" /> <label for="allMulti"><?php echo _('Multi'); ?></label></th>
						<?php if(isset($settings['state']))
							echo '<th style="border-color:#000 !important" class="text-center">'._('State').'</th>';
						if($ordre == 0 && $type == 1)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=1&ord=1&s='.urlencode(stripslashes($search)).'">'._('Package').'</a> <span class="glyphicon glyphicon-sort-by-alphabet"></span></th>';
						elseif($ordre == 1 && $type == 1)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=1&ord=0&s='.urlencode(stripslashes($search)).'">'._('Package').'</a> <span class="glyphicon glyphicon-sort-by-alphabet-alt"></span></th>';
						else
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=1&ord=0&s='.urlencode(stripslashes($search)).'">'._('Package').'</a></th>';

						if($ordre == 0 && $type == 3 && isset($settings['section']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=3&ord=1&s='.urlencode(stripslashes($search)).'">'._('Section').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 3 && isset($settings['section']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=3&ord=0&s='.urlencode(stripslashes($search)).'">'._('Section').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['section']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=3&ord=0&s='.urlencode(stripslashes($search)).'">'._('Section').'</a></th>';

						if($ordre == 0 && $type == 0)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=0&ord=1&s='.urlencode(stripslashes($search)).'">'._('Date').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 0)
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=0&ord=0&s='.urlencode(stripslashes($search)).'">'._('Date').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						else
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=0&ord=0&s='.urlencode(stripslashes($search)).'">'._('Date').'</a></th>';

						if($ordre == 0 && $type == 4 && isset($settings['size']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=4&ord=1&s='.urlencode(stripslashes($search)).'">'._('Size').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 4 && isset($settings['size']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=4&ord=0&s='.urlencode(stripslashes($search)).'">'._('Size').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['size']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=4&ord=0&s='.urlencode(stripslashes($search)).'">'._('Size').'</a></th>';

						if($ordre == 0 && $type == 2 && isset($settings['author']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=2&ord=1&s='.urlencode(stripslashes($search)).'">'._('Author').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 2 && isset($settings['author']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=2&ord=0&s='.urlencode(stripslashes($search)).'">'._('Author').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['author']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=2&ord=0&s='.urlencode(stripslashes($search)).'">'._('Author').'</a></th>';

						if($ordre == 0 && $type == 7 && isset($settings['downloaded']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=7&ord=1&s='.urlencode(stripslashes($search)).'">'._('Downloaded').'</a> <span class="glyphicon glyphicon-sort-by-order"></span></th>';
						elseif($ordre == 1 && $type == 7 && isset($settings['downloaded']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=7&ord=0&s='.urlencode(stripslashes($search)).'">'._('Downloaded').'</a> <span class="glyphicon glyphicon-sort-by-order-alt"></span></th>';
						elseif(isset($settings['downloaded']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=7&ord=0&s='.urlencode(stripslashes($search)).'">'._('Downloaded').'</a></th>';

						if($ordre == 0 && $type == 6 && isset($settings['rating']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=6&ord=1&s='.urlencode(stripslashes($search)).'">'._('Rating').'</a> <span class="glyphicon glyphicon-sort-by-order"></span></th>';
						elseif($ordre == 1 && $type == 6 && isset($settings['rating']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=6&ord=0&s='.urlencode(stripslashes($search)).'">'._('Rating').'</a> <span class="glyphicon glyphicon-sort-by-order-alt"></span></th>';
						elseif(isset($settings['rating']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=6&ord=0&s='.urlencode(stripslashes($search)).'">'._('Rating').'</a></th>';

						if($ordre == 0 && $type == 8 && isset($settings['uploader']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=8&ord=1&s='.urlencode(stripslashes($search)).'">'._('Uploader').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 8 && isset($settings['uploader']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=8&ord=0&s='.urlencode(stripslashes($search)).'">'._('Uploader').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['uploader']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=8&ord=0&s='.urlencode(stripslashes($search)).'">'._('Uploader').'</a></th>';

						if($ordre == 0 && $type == 9 && isset($settings['views']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=9&ord=1&s='.urlencode(stripslashes($search)).'">'._('Views').'</a> <span class="glyphicon glyphicon-arrow-up"></span></th>';
						elseif($ordre == 1 && $type == 9 && isset($settings['views']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=9&ord=0&s='.urlencode(stripslashes($search)).'">'._('Views').'</a> <span class="glyphicon glyphicon-arrow-down"></span></th>';
						elseif(isset($settings['views']))
							echo '<th style="border-color:#000 !important" class="text-center"><a href="?champs='.$champ.'&section='.$section_search.'&type=9&ord=0&s='.urlencode(stripslashes($search)).'">'._('Views').'</a></th>';

						echo '<th style="border-color:#000 !important" class="text-center">'._('Action').'</th>
						</tr>';
						foreach ($req as $key) {
							$date = date_format(date_create($key['date_update']), "d/m/Y - H:i");
							$updt = date_format(date_create($key['date_update']), "i:H d/m/Y");
							$new = date_format(date_create($key['date']), "i:H d/m/Y");
							if(!$key['online'] && !file_exists('debs/org.goldencydia.'.$key['id'].'/org.goldencydia.'.$key['id'].'.deb')) {
								$etat_bool = '&online';
								$etat_style = 'glyphicon glyphicon-ok-circle';
								$etat_texte= _('Put online');
								$updt_class = '<span class="label label-danger">Offline</span>';
							} elseif(!$key['online'] && file_exists('debs/org.goldencydia.'.$key['id'].'/org.goldencydia.'.$key['id'].'.deb')) {
								$etat_bool = '&online';
								$etat_style = 'glyphicon glyphicon-ok-circle';
								$etat_texte= _('Put online');
								$updt_class = '<span class="label label-warning">Offline</span>';
							} elseif($updt == $new) {
								$etat_bool = '&offline';
								$etat_style = 'glyphicon glyphicon-remove-circle';
								$etat_texte= _('Put offline');
								$updt_class = '<span class="label label-success">New</span>';
							} else {
								$etat_bool = '&offline';
								$etat_style = 'glyphicon glyphicon-remove-circle';
								$etat_texte= _('Put offline');
								$updt_class = '<span class="label label-info">Update</span>';
							}
							if(strlen($key['Author']) > 10)
								$auteur = mb_substr($key['Author'], 0, 10, 'UTF-8').'..';
							else
								$auteur = $key['Author'];
							if(strlen($key['Name']) > 15)
								$nom = mb_substr($key['Name'], 0, 15, 'UTF-8').'..';
							else
								$nom = $key['Name'];
							if(strlen($key['Version']) > 10)
								$version = mb_substr($key['Version'], 0, 10, 'UTF-8').'..';
							else
								$version = $key['Version'];
							if(file_exists('images/debs/'.$key['id'].'.png'))
								$icon = 'images/debs/'.$key['id'].'.png';
							else
								$icon = 'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($key['Section']))).'.png';
							$disabled = ($membre->_level > 2 || $membre->_id == $key['id_membre']) ? '' : 'disabled';
							echo '<tr>
								<td style="border-color:#000 !important" class="hidden text-center"><input type="checkbox" value="'.$key['id'].'" name="id[]" /></td>';
							if(isset($settings['state']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$updt_class.'</td>';
								echo '<td style="border-color:#000 !important" class="text-center"><a target="_blank" href="pack/'.$key['id'].'"><img width="25" height="25" style="border-radius:13px;padding:1px" src="'.$icon.'" alt="Icon">'.$nom.' <small>'.$version.'</small></a></td>';
							if(isset($settings['section']))
								echo '<td style="border-color:#000 !important" class="text-center"><a target="_blank" href="section/'.urlencode($key['Section']).'">'.$key['Section'].'</a></td>';
								echo '<td style="border-color:#000 !important" class="text-center"><small>'.$date.'</small></td>';
							if(isset($settings['size']))
								echo '<td style="border-color:#000 !important" class="text-center"><small>'.taille_fichier($key['Size']).'</small></td>';
							if(isset($settings['author']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$auteur.'</td>';
							if(isset($settings['downloaded']))
								echo '<td style="border-color:#000 !important" class="text-center">'.print_number($key['telechargements']).'</td>';
							if(isset($settings['rating']))
								echo '<td style="border-color:#000 !important" class="text-center">'.($key['total_votes'] > 0 ? @number_format($key['total_value'] / $key['total_votes'], 1, ',', ' ') : '0,0').'/5 ('.$key['total_votes'].' <span class="glyphicon glyphicon-user"></span>)</td>';
							if(isset($settings['uploader']))
								echo '<td style="border-color:#000 !important" class="text-center">'.$key['pseudo'].'</td>';
							if(isset($settings['views']))
								echo '<td style="border-color:#000 !important" class="text-center">'.print_number($key['visits']).'</td>';
								echo '<td style="border-color:#000 !important" class="text-center"><div class="btn-group">
									<button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown">'._('Action').' <span class="caret"></span></button>
									<ul class="dropdown-menu pull-right text-left" role="menu">
										<li class="'.$disabled.'"><a href="presentation.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon-edit"></span> '._('Edit description').'</a></li>
										<li class="'.$disabled.'"><a href="upload.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon-pencil"></span> '._('Edit package').'</a></li>
										<li class="'.$disabled.'"><a href="upload.php?update='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon-refresh"></span> '._('Update').'</a></li>
										<li class="divider"></li>
										<li><a href="stats-paquet.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon-stats"></span> '._('Statistics').'</a></li>
										<li><a href="down.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon-download"></span> '._('Download').'</a></li>
										<li class="divider"></li>
										<li class="deconnexion '.$disabled.'"><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&s='.urlencode(stripslashes($search)).'&page='.$page.'&id='.$key['id'].''.$etat_bool.'" class="btn-xs"><span class="'.$etat_style.'"></span> '.$etat_texte.'</a></li>
										<li class="deconnexion '.$disabled.'"><a href="delete.php?id='.$key['id'].'" class="btn-xs"><span class="glyphicon glyphicon glyphicon-trash"></span> '._('Delete').'</a></li>
									</ul>
								</div></td>
							</tr>';
						} ?>
					</table>
					<p class="text-center visible displayMulti"><button class="btn btn-xs btn-primary" type="button" onclick="$('.hidden').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('hidden visible', 1000 );"><?php echo _('Multiple choise'); ?></button></p>
					<p class="text-center hidden">
						<select class="btn-xs btn-primary selectActionBottom">
							<option><?php echo _('Select an option'); ?></option>
							<option value="online"><?php echo _('Put online'); ?></option>
							<option value="offline"><?php echo _('Put offline'); ?></option>
							<option value="delete"><?php echo _('Delete'); ?></option>
						</select>
						<button class="btn btn-xs btn-primary" type="submit"><?php echo _('Action'); ?></button>
						<button class="btn btn-xs btn-primary" type="button" onclick="$('.visible').toggleClass('hidden visible', 1000 );$('.displayMulti').toggleClass('visible hidden', 1000 );"><?php echo _('Hide'); ?></button>
					</p>
					</form>
					<?php echo '<div class="text-center"><ul class="pagination">';
					if($page > 1)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page=1&s='.urlencode(stripslashes($search)).'">1</a></li>';
					if($precedent > 2)
						echo '<li class="disabled"><a>...</a></li>';
					if($page > 2)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page='.$precedent.'&s='.urlencode(stripslashes($search)).'">'.$precedent.'</a></li>';
					echo '<li class="active"><a>'.$page.'</a></li>';
					if($fin > $suivant)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page='.$suivant.'&s='.urlencode(stripslashes($search)).'">'.$suivant.'</a></li>';
					if($fin > $suivant + 1)
						echo '<li class="disabled"><a>...</a></li>';
					if($fin >= $suivant)
						echo '<li><a href="?champs='.$champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page='.$fin.'&s='.urlencode(stripslashes($search)).'">'.$fin.'</a></li>';
					echo '</ul></div>'; ?>
			<?php } else
				echo '<h2 class="text-center">'._('No package found').'</h2>'; ?>
		</div>
	</div>
	<?php require_once('includes/admin/footer.php'); ?>
	<script>jQuery(document).ready(function(e){
		$("#multiAction").submit(function(e){
			e.preventDefault();
			var checked = false;
			$("input[name='id[]']").map(function(){
				if($(this).prop('checked') == true)
					checked = true;
			});
			if(checked == true) {
				var selectedTop = $('.selectActionTop').find(":selected").val();
				var selectedBottom = $('.selectActionBottom').find(":selected").val();
				if(selectedTop == 'online' || selectedBottom == 'online') {
					var ids = $("input[name='id[]']").map(function(){if($(this).is(':checked'))return $(this).val();}).get();
					window.location.replace("manage-all.php?champs=<?php echo $champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page='.$page.'&s='.urlencode(stripslashes($search)); ?>&id[]="+ids.join("&id[]=")+"&online");
				} else if(selectedTop == 'offline' || selectedBottom == 'offline') {
					var ids = $("input[name='id[]']").map(function(){if($(this).is(':checked'))return $(this).val();}).get();
					window.location.replace("manage-all.php?champs=<?php echo $champ.'&section='.$section_search.'&type='.$type.'&ord='.$ordre.'&page='.$page.'&s='.urlencode(stripslashes($search)); ?>&id[]="+ids.join("&id[]=")+"&offline");
				} else if(selectedTop == 'delete' || selectedBottom == 'delete') {
					var ids = $("input[name='id[]']").map(function(){if($(this).is(':checked'))return $(this).val();}).get();
					window.location.replace("delete.php?id[]="+ids.join("&id[]="));
				} else 
					alert('<?php echo _('Please choose an action !'); ?>');
			} else
				alert('<?php echo _('Please select a package !'); ?>');
		});
		$(".allMulti").click(function(){
			var is_checked = $("input[name='allMulti']").is(":checked");
			$("input[name='id[]']").map(function(){
				$(this).prop('checked', is_checked);
			});
			$("input[name='allMulti']").prop('checked', is_checked);
		});
		$("input[name='id[]']").click(function(){
			var total_boxes = $("input[name='id[]']").length;
			var checked_boxes = $("input[name='id[]']:checked").length;
			var $checkall = $("input[name='allMulti']");
			if (total_boxes == checked_boxes)
				$checkall.prop({checked: true, indeterminate: false});
			else if (checked_boxes > 0)
				$checkall.prop({checked: true, indeterminate: true});
			else
				$checkall.prop({checked: false, indeterminate: false});
		});
	});</script>
</body>
</html>
<?php } else
	require_once('404.php'); ?>