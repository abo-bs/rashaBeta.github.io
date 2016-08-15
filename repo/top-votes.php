<?php require_once('includes/functions.php');
$cache = 'includes/cache/top-votes.txt';
$expire = time() - 1800; // 30 minutes
if(file_exists($cache) && filemtime($cache) > $expire && $re) {
	$res = unserialize(file_get_contents($cache));
} else {
	ignore_user_abort(1);
	$pdo = PDO2::getInstance();
	$req = $pdo->prepare("SELECT description.id, Name, Version, Section, total_value, total_votes FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE online = 1 ORDER BY (total_value / total_votes) DESC, total_votes DESC LIMIT 0, 50");
	try {
		$req->execute();
		$res = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		file_put_contents($cache, serialize($res));
	} catch(Exception $e) {
		$req->closeCursor();
		$res = array();
	}
	$pdo = PDO2::closeInstance();
}
$lang_user = translation();
$site_url = config('url');
$site_nom = config('nom'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="title" content="<?php echo _('Top 50 ratings'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:title" content="<?php echo _('Top 50 ratings'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>top-votes.php">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?> ">
		<meta name="twitter:url" content="<?php echo $site_url; ?>top-votes.php">
		<meta name="twitter:title" content="<?php echo _('Top 50 ratings'); ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<title><?php echo _('Top 50 ratings'); ?> | <?php echo $site_nom; ?></title>
		<link rel="shortcut icon" href="images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/style.min.css" />
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0" href="./"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0" href="top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<h3 class="text-center"><span class="glyphicon glyphicon-star"></span> <?php echo _('Top 50 ratings'); ?></h3>
			<div class="list-group media"><div class="row">
			<?php if(!empty($res)) {
				$j = 0;
				foreach($res as $key) {
					if($j == 0 OR $j == 25)
						echo '<div class="col-sm-6">';

					$j++;
					$nom = htmlspecialchars($key['Name']);
					$version = $key['Version'];
					$tense = ($key['total_votes'] < 2) ? _('vote') : _('votes');
					$votes = '<strong>'.@number_format($key['total_value'] / $key['total_votes'], 1).'</strong>/5 ('.@number_format($key['total_votes'], 0).' '.$tense.')';

					if($j <= 12)
						$color = '#FFCF00';
					elseif($j <= 24)
						$color = '#FFDB44';
					elseif($j <= 36)
						$color = '#BDBDBD';
					else
						$color = '#DB9000';
					if(file_exists('images/debs/'.$key['id'].'.png'))
						$icon = 'images/debs/'.$key['id'].'.png';
					else
						$icon = 'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($key['Section']))).'.png';

					echo '<a href="pack/'.$key['id'].'" class="list-group-item media text-center">
						<span class="pull-left"><span style="background:'.$color.';position:absolute;top:0.75em;transform:rotate(-45deg);-webkit-transform:rotate(-45deg);-moz-transform:rotate(-45deg);line-height:1.3em;max-width:100%;min-width:6em;text-align:center;left:-1.5em;opacity:.85;box-shadow: 1px 2px 3px #000;text-shadow: 1px 2px 3px #fff;color:#000;font-weight:bold">'.$j.'°</span><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object lazy" data-src="'.$icon.'" alt="'.$nom.' - '.$version.'" /><noscript><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object" src="'.$icon.'" alt="'.$nom.' - '.$version.'" /></noscript></span>
						<div class="media-body">
							<h4 class="media-heading">'.$nom.' <small>'.$version.'</small></h4>
							'.$votes.'
						</div>
					</a>';

					if($j == count($res) OR $j == 25 OR $j == 50)
						echo '</div>';
				}
			} else {
				echo '<p class="text-center">Error load data... Try to refresh</p>';
			} ?>
			</div></div>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html>