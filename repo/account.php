<?php require_once('includes/session.class.php');
$membre = new Session();
if($membre->_connected) {
	$site_url = config('url');
	$site_nom = config('nom');
	require_once('includes/time-header.php');
	$lang_user = translation(); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/style.min.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<title><?php echo _('Your account'); ?> | <?php echo $site_nom; ?></title>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0;" href="./"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0;" href="login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto"><div class="lead">
			<?php if(isset($_GET['login']) && $membre->_connected)
				echo '<div class="alert alert-success alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><p>'._('You have successfully connected.').'</p></div>'; ?>
			<h2 class="text-center"><span class="glyphicon glyphicon-user"></span> <?php echo _('Your account').' | '.$membre->_pseudo; ?></h2><br /><hr />
			<p class="text-center">
				<a href="<?php echo $site_url; ?>downloads.php"><span class="glyphicon glyphicon-download"></span> <span dir="ltr"><?php echo _('Your downloads'); ?></span></a><br /><br />
				<a href="<?php echo $site_url; ?>settings.php"><span class="glyphicon glyphicon-cog"></span> <?php echo _('Account settings'); ?></a><br /><br />
			</p>
			<hr /><p class="text-center">
				<a href="<?php echo $site_url; ?>login.php?action=logout&amp;token=<?php echo $membre->_token; ?>"><span class="glyphicon glyphicon-log-out"></span> <?php echo _('Logout'); ?></a>
				<?php if($membre->_level > 0)
					echo '<br /><br /><a href="'.$site_url.'home.php"><span class="glyphicon glyphicon-dashboard"></span> Administration</a>'; ?>
			</p>
		</div></div>
		<?php require_once('includes/front/footer.php'); ?>
	</body>
</html>
<?php } else
	header('Location: /login.php'); ?>