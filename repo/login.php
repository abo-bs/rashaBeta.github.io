<?php 
require_once('includes/session.class.php');
$membre = new Session();
$lang_user = translation();
if($membre->_connected && $_GET['action'] == 'logout' && $_GET['token'] == $membre->_token) {
	if(is_dir('includes/temp/'.$membre->_pseudo))
		rrmdir('includes/temp/'.$membre->_pseudo);
	if(file_exists('includes/temp/'.$membre->_pseudo.'.deb'))
		unlink('includes/temp/'.$membre->_pseudo.'.deb');
	$membre->deconnecte();
	header('Location: login.php?logout');
	exit;
} elseif($membre->_connected) {
	header('Location: account.php');
	exit;
} elseif (!empty($_POST['username']) && !empty($_POST['password'])) {
	$ip = getIp();
	$pseudo = addslashes(htmlspecialchars(trim($_POST['username'])));
	$password = $_POST['password'];
	$persistant = (isset($_POST['persistant'])) ? true : false;
	if($membre->connexion($pseudo, $password, $encrypted = false, $persistant)) {
		$pdo = PDO2::getInstance();
		$update_user = $pdo->prepare('INSERT INTO membre (id, ip, last_date, recovery) VALUES (:id, :ip, CURRENT_TIMESTAMP, NULL)
		ON DUPLICATE KEY UPDATE ip = :ip, last_date = CURRENT_TIMESTAMP, recovery = NULL');
		$update_user->execute(array(':id' => $membre->_id, ':ip' => $ip));
		$pdo = PDO2::closeInstance();
		header('Location: account.php?login');
		exit;
	} else {
		header('Location: login.php?error=badlogin');
		exit;
	}
} elseif (isset($_POST['submit']) && (empty($_POST['username']) || empty($_POST['password']))) {
	header('Location: login.php?error=notenough');
	exit;
} else {
	$site_nom = config('nom'); ?>
<!doctype html>
<html>
	<head>
		<title><?php echo _('Sign In'); ?> | <?php echo $site_nom; ?></title>
		<link rel="stylesheet" href="css/style.min.css">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<style type="text/css" media="screen">html{height:auto}body{height:auto;margin:5px 0;background:#222 !important}.login{margin:auto;width:320px;text-align:center}.well{box-shadow:0 4px 10px -1px rgba(200, 200, 200, 0.7);background:#fff;border:1px solid rgb(0, 0, 0);border-radius:10px;margin:0 5px 20px}label{font-size:14px;text-align:center;display:block}h1 a{background-image:url('/images/logo.png');background-position:center top;background-repeat:no-repeat;width:320px;height:80px;text-indent:-9999px;outline:0px none;overflow:hidden;padding-bottom:15px;margin:0 auto;display:block}hr{margin-top:0}input{color: rgb(85, 85, 85);font-weight:400;font-size:20px;line-height:1;width:95%;padding:3px;margin-top:2px;margin-bottom:16px;border:1px solid rgb(229, 229, 229);background:none repeat scroll 0% 0% rgb(251, 251, 251);outline:0 none;box-shadow:1px 1px 2px rgba(200, 200, 200, 0.2) inset;height:30px}input[type=checkbox]{width:13px;height:13px;vertical-align:sub}.g-recaptcha{margin:5px -16px;overflow:hidden}</style>
	</head>
	<body>
		<h1><a href="./" title="<?php echo $site_nom; ?>"><?php echo $site_nom; ?></a></h1>
		<div class="login">
			<form class="well" method="POST">
			<?php if (isset($_GET['error']) && $_GET["error"] == "notenough") {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#f00">'._('Please fill in all fields.').'</h5><hr>';
			} elseif (isset($_GET['error']) && $_GET["error"] == "badlogin") {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#f00">'._('Your login information is incorrect.').'</h5><hr>';
			} elseif (isset($_GET['error']) && $_GET["error"] == "dberror") {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#f00">'._('Unable to connect to database.').'</h5><hr>';
			} elseif (isset($_GET['logout'])) {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#0d0">'._('You have been successfully logged out !').'</h5><hr>';
			} elseif (isset($_GET['timeout'])) {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#f00">'._('Your session has expired !').'</h5><hr>';
			} elseif (isset($_GET['nocookie'])) {
				echo '<h3>'._('Sign In').'</h3>';
				echo '<h5 style="color:#f00">'._('You must enable cookies on your browser.').'</h5><hr>';
			} else {
				echo '<h3>'._('Sign In').'</h3><hr>';
			} ?>
				<label for="username"><?php echo _('Nickname'); ?></label>
				<input autofocus type="text" id="username" name="username" required="required" placeholder="<?php echo _('Nickname'); ?>" />
				<label for="password"><?php echo _('Password'); ?></label>
				<input type="password" id="password" name="password" placeholder="<?php echo _('Password'); ?>" required="required" />
				<label for="persistant">
					<input style="display:inline-block" type="checkbox" id="persistant" name="persistant" />
					<?php echo _('Remember me'); ?>
				</label>
				<button type="submit" class="btn btn-medium btn-primary"><?php echo _('Sign In'); ?> <span class="glyphicon glyphicon-log-in"></span></button>
			</form>
			<div><a href="./" title="← <?php echo _('Back to home.'); ?>">← <?php echo _('Back to home.'); ?></a> | <a href="forgot-password.php" title="<?php echo _('Lost password'); ?>"><?php echo _('Lost password'); ?></a> | <a href="register.php" title="<?php echo _('Registration'); ?>"><?php echo _('Registration'); ?></a></div>
		</div>
	</body>
</html><?php } ?>