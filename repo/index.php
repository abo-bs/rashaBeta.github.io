<?php require_once('includes/package.class.php');
$langues_possibles = array('ar', 'en');
if(isset($_GET['language']) && in_array($_GET['language'], $langues_possibles)) {
	$expire = time() + 30 * 24 * 3600;
	setrawcookie('language', $_GET['language'], $expire, '/');
	setrawcookie('googtrans', '/en/'.$_GET['language'], $expire, '/');
	header('Location: /');
	exit;
}
$lang_user = translation();
$site_url = config('url');
$site_nom = config('nom'); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="<?php echo $site_url; ?>images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>css/style.min.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="title" content="<?php echo _('Home'); ?> | <?php echo $site_nom; ?>">
		<meta name="description" content="<?php echo $site_nom; ?> : free, unique and easy.">
		<meta property="og:title" content="<?php echo _('Home'); ?> | <?php echo $site_nom; ?>">
		<meta property="og:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>">
		<meta property="og:description" content="<?php echo $site_nom; ?> : free, unique and easy.">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?>">
		<meta name="twitter:url" content="<?php echo $site_url; ?>">
		<meta name="twitter:title" content="<?php echo _('Home'); ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:image" content="<?php echo $site_url; ?>CydiaIcon.png">
		<meta name="twitter:description" content="<?php echo $site_nom; ?> : free, unique and easy.">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<title><?php echo _('Home'); ?> | <?php echo $site_nom; ?></title>
		<style>#iphone{max-width:385px;max-height:800px;border:3px solid #ccc;background-color:#1F1F21;border-radius:45px;margin:0 auto;padding:10px}#camera{margin:10px auto;border-radius:50px;height:10px;width:10px;background-color:#2b2b2b}#earpiece{border-radius:20px;height:8px;width:60px;background-color:#2b2b2b;margin:10px auto 30px}#screen{max-height:667px;max-width:375px;margin:0 auto;background-color:#2b2b2b;border-radius:3px}#home{height:50px;width:50px;margin:15px auto 0;background-color:#272727;border-radius:50px;border:4px solid #292929}#screen img{opacity:.9;border-radius:5px}</style>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#fff;font-size:1.6em;padding:15px 10px 0" href="<?php echo $site_url; ?>"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>news.php"></a>
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
		<div class="lead">
			<div class="text-center">
				<ul class="pagination" style="margin:5px 0">
					<li><a style="padding:5px" href="/?language=ar">العربية</a></li>
					<li><a style="padding:5px" href="/?language=en">English</a></li>
				</ul>
			</div><hr />
			<p class="text-center"><?php echo _('Open Cydia > Sources > Edit > Add'); ?></p>
			<p class="text-center" style="font-size:1.4em;font-weight:bold"><input style="text-align:center;border:2px solid #000;width:100%;height:50px;user-select:text;-ms-user-select:text;-moz-user-select:text;-khtml-user-select:text;-webkit-user-select:text;-webkit-touch-callout:text" type="text" value="<?php echo $site_url; ?>" /></p><hr />
			<div id="container">
				<div id="iphone">
					<div id="camera"></div>
					<div id="earpiece"></div>
					<div id="screen" class="text-center">
						<img class="img-responsive lazy" alt="<?php echo $site_url; ?>" data-src="<?php echo $site_url.'images/enter.png'; ?>" width="375" height="667" title="Repository" />
						<noscript><img class="img-responsive" alt="<?php echo $site_url; ?>" src="<?php echo $site_url.'images/enter.png'; ?>" width="375" height="667" title="Repository" /></noscript>
					</div>
					<div id="home"></div>
				</div>
			</div>
		</div>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<!-- GOOGLE ADSENSE -->
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html>