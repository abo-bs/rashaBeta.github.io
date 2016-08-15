<?php header('HTTP/1.0 403 Forbidden', true, 403);
require_once("includes/functions.php");
$config['url'] = config('url');
$config['nom'] = config('nom');
$lang_user = translation(); ?>
<!doctype html>
<html style="height:auto">
	<head>
		<title><?php echo _('Error 403'); ?> | <?php echo $config['nom']; ?></title>
		<link rel="shortcut icon" href="<?php echo $config['url']; ?>images/favicon.ico" />
		<link rel="stylesheet" href="<?php echo $config['url']; ?>css/style.min.css">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
	</head>
	<body>
		<h1><a href="<?php echo $config['url']; ?>" title="<?php echo $config['nom']; ?>"><?php echo $config['nom']; ?></a></h1>
		<div class="login">
			<div class="well">
				<h3 class="error"><?php echo _('Error 403'); ?></h3><hr>
				<h5 style="color:#f00"><?php echo _('Sorry, this zone is forbidden.'); ?></h5>
			</div>
			<div><a href="<?php echo $config['url']; ?>" title="← <?php echo _('Back to home.'); ?>">← <?php echo _('Back to home.'); ?></a> | <a href="<?php echo $config['url']; ?>login.php" title="<?php echo _('Sign In'); ?>"><?php echo _('Sign In'); ?></a><br /><a href="<?php echo $config['url']; ?>register.php" title="<?php echo _('Registration'); ?>"><?php echo _('Registration'); ?></a> | <a href="<?php echo $config['url']; ?>forgot-password.php" title="<?php echo _('Lost password'); ?>"><?php echo _('Lost password'); ?></a></div>
		</div>
		<script src="<?php echo $config['url']; ?>js/jquery-latest.min.js"></script>
		<script>$(document).ready(function (e){var glow=$('.well');setInterval(function(){glow.toggleClass('glow');},1000);});</script>
	</body>
</html>
<style type="text/css" media="screen">body{margin:75px 0 0;background:#222 !important}.login{margin:auto;width:320px;text-align:center}.well{box-shadow: 0px 4px 10px -1px rgba(200, 200, 200, 0.7);background:#fff;border: 1px solid rgb(0, 0, 0);border-radius: 10px;margin:0 5px 20px;-webkit-transition:box-shadow 1s linear;-moz-transition:box-shadow 1s linear;-ms-transition:box-shadow 1s linear;-o-transition:box-shadow 1s linear;transition:box-shadow 1s linear}label{font-size: 14px;text-align:left}input{color: rgb(85, 85, 85);font-weight: 400;font-size: 24px;line-height: 1;width:95%;padding: 3px;margin-top: 2px;margin-bottom: 16px;border: 1px solid rgb(229, 229, 229);background: none repeat scroll 0% 0% rgb(251, 251, 251);outline: 0px none;box-shadow: 1px 1px 2px rgba(200, 200, 200, 0.2) inset;height:30px}h1 a {background-image: url('/images/logo.png');background-position: center top;background-repeat: no-repeat;width: 320px;height: 80px;text-indent: -9999px;outline: 0px none;overflow: hidden;padding-bottom: 15px;margin:0 auto;display: block;}a#backtoblog {color: rgb(0, 136, 204);text-shadow: 0px 1px 0px rgb(0, 0, 0)}a:hover#backtoblog {color: rgb(255, 255, 255);}hr{margin-top:0}.well:hover,.well.glow{box-shadow:0 0 20px #fff}</style>