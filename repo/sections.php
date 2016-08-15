<?php if($_SERVER['REQUEST_URI'] == '/section/') {
require_once('includes/functions.php');
$pdo = PDO2::getInstance();
$req = $pdo->prepare("SELECT DISTINCT Section, COUNT(id) FROM description GROUP BY Section ORDER BY Section");
try {
	$req->execute();
	$count = $req->rowCount();
	$req = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
	$count = 0;
	$req = array();
}
$pdo = PDO2::closeInstance();
$lang_user = translation();
$site_nom = config('nom');
$site_url = config('url'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="title" content="<?php echo _('Sections'); ?> | <?php echo $site_nom; ?>">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<meta property="og:title" content="<?php echo _('Sections'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>section/">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?> ">
		<meta name="twitter:url" content="<?php echo $site_url; ?>section/">
		<meta name="twitter:title" content="<?php echo _('Sections'); ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<title><?php echo _('Sections'); ?> | <?php echo $site_nom; ?></title>
		<link rel="shortcut icon" href="<?php echo $site_url; ?>images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>css/style.min.css" />
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
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-open" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>section/"></a>
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
			<h3 class="text-center"><span class="glyphicon glyphicon-folder-open"></span> <?php echo _('Sections');echo ' <small>'.$count.'</small>'; ?></h3>
			<div class="list-group media">
			<?php if(!empty($req)) {
				foreach($req as $section) {
					$package_nbr = ($section['COUNT(id)'] <  2) ? strtolower(_('Package')) : strtolower(_('Packages'));
					echo '<a href="'.rawurlencode($section['Section']).'" class="list-group-item media text-center">
						<span class="pull-left"><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object lazy" data-src="'.$site_url.'images/sections/'.preg_replace("/[\/_|+ -]+/", "-", strtolower(trim($section['Section']))).'.png" alt="'.$section['Section'].'" /><noscript><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object" src="'.$site_url.'images/sections/'.preg_replace("/[\/_|+ -]+/", "-", strtolower(trim($section['Section']))).'.png" alt="'.$section['Section'].'" /></noscript></span>
						<div class="media-body">
							<h4 class="media-heading">'.$section['Section'].'</h4>
							'.$section['COUNT(id)'].' '.$package_nbr.'
						</div>
					</a>';
				}
			} else {
				echo '<p class="text-center">Error load data... Try to refresh please</p>';
			} ?>
			</div>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html><?php } else
	header('Location: /section/', true, 404); ?>