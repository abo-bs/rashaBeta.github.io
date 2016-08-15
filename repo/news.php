<?php require_once('includes/package.class.php');
require_once('includes/session.class.php');
$membre = new Session();
$cache = 'includes/cache/nouveautes.txt';
if(file_exists($cache))
	$res = unserialize(file_get_contents($cache));
else {
	ignore_user_abort(1);
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT description.id, description.Name, description.Version, description.Author, description.Section, description_meta.date, description_meta.date_update FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE description_meta.online = true ORDER BY description_meta.date_update DESC LIMIT 0, 50");
	try {
		$req->execute();
		$res = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		$pdo = PDO2::closeInstance();
		file_put_contents($cache, serialize($res));
	} catch(Exception $e) {
		$req->closeCursor();
		$pdo = PDO2::closeInstance();
		$res = array();
	}
}
$lang_user = translation();
$site_nom = config('nom');
$site_url = config('url'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="<?php echo $site_url; ?>images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>css/style.min.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="title" content="<?php echo _('Novelty and updates'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:title" content="<?php echo _('Novelty and updates'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>news.php">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?> ">
		<meta name="twitter:url" content="<?php echo $site_url; ?>news.php">
		<meta name="twitter:title" content="<?php echo _('Novelty and updates'); ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<title><?php echo _('Novelty and updates'); ?> | <?php echo $site_nom; ?></title>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0" href="<?php echo $site_url; ?>"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="<?php echo $site_url; ?>search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<h3 class="text-center"><span class="glyphicon glyphicon-refresh"></span> <?php echo _('Novelty and updates'); ?></h3>
			<div class="list-group media"><div class="row">
			<?php if(!empty($res)) {
				$j = 0;
				foreach($res as $key) {
					if($j == 0 OR $j == 25)
						echo '<div class="col-sm-6">';
					$j++;
					$new_date = false;
					if(empty($date_reel) OR $date_reel !== date_format(date_create($key['date_update']),"j/m/Y")) {
						$date_reel = date_format(date_create($key['date_update']),"j/m/Y");
						$new_date = true;
					}
					if($new_date == true)
						echo '<h4 class="text-center"><span class="glyphicon glyphicon-calendar"></span> '.$date_reel.'</h4>';
					$updt = date_format(date_create($key['date_update']), "i:H d/m/Y");
					$new = date_format(date_create($key['date']), "i:H d/m/Y");
					if($updt == $new) {
						$update = 'New';$color = '#f00';
					} else {
						$update = 'Update';$color = '#00f';
					}
					if(file_exists('images/debs/'.$key['id'].'.png'))
						$icon = 'images/debs/'.$key['id'].'.png';
					else
						$icon = 'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($key['Section']))).'.png';
					$nom = htmlspecialchars($key['Name']);
					$version = $key['Version'];
					echo '<span class="list-group-item media text-center">
						<span class="pull-left"><span style="background:'.$color.';position:absolute;top:0.75em;transform:rotate(-45deg);-webkit-transform:rotate(-45deg);-moz-transform:rotate(-45deg);line-height:1.3em;max-width:100%;min-width:6em;text-align:center;left:-1.5em;opacity:.85;box-shadow: 1px 2px 3px #000;text-shadow: 1px 2px 3px #fff;color:#000;font-weight:bold">'.$update.'</span><img width="50" height="50" style="background:rgba(0, 136, 255, 0.2);border-radius:13px;padding:1px" class="media-object lazy" data-src="'.$site_url.$icon.'" alt="'.$nom.' - '.$version.'" /><noscript><img width="50" height="50" style="background:rgba(0, 136, 255, 0.2);border-radius:13px;padding:1px" class="media-object" src="'.$site_url.$icon.'" alt="'.$nom.' - '.$version.'" /></noscript></span>
						<div class="media-body">
							<h4 class="media-heading"><a href="pack/'.$key['id'].'">'.$nom.'</a> <small>'.$version.'</small></h4>
							<span class="glyphicon glyphicon-user"></span> '.$key['Author'].' <a href="section/'.rawurlencode($key['Section']).'"><span class="glyphicon glyphicon-folder-open"></span> '.$key['Section'].'</a>
						</div>
					</span>';
					if($j == count($res)  OR $j == 25 OR $j == 50)
						echo '</div>';
				}
			} else
				echo '<p class="text-center">Error load data... Retry later please.</p>'; ?>
			</div></div>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
		<script type="text/javascript" src="<?php echo $site_url; ?>js/behavior.js"></script>1500
		<script type="text/javascript" src="<?php echo $site_url; ?>js/favorite.js"></script>
	</body>
</html>