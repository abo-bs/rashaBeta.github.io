<?php if(!empty($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 7, strlen($_SERVER['SERVER_NAME'])) != $_SERVER['SERVER_NAME'])
	$_POST = array();
require_once('includes/session.class.php');
$membre = new Session();
$lang_user = translation();
$site_nom = config('nom');
$site_url = config('url');

if($membre->_connected) {
	header("Location: dpt-account.php");
	exit;
} elseif(!empty($_GET['id']) && !empty($_GET['recovery']) && strlen($_GET['recovery']) > 9) {
	$pdo = PDO2::getInstance();
	$check_reset = $pdo->prepare('SELECT pseudo, mail FROM membre WHERE id = :id AND recovery = :recovery');
	$check_reset->execute(array(':id' => $_GET['id'], ':recovery' => $_GET['recovery']));
	$count_reset = $check_reset->rowCount();
	if($count_reset == 1) {
		$check_reset = $check_reset->fetch(PDO::FETCH_ASSOC);
		$chaine = "abcdefghijklmnpqrstuvwxyz0123456789";
		$nb_chars = strlen($chaine);
		for($i=0; $i < 8; $i++) {
			$password .= $chaine[ rand(0, ($nb_chars-1)) ];
		}
		$password_hash = hash('sha512', hash('sha512', $password));
		$update_user = $pdo->prepare('INSERT INTO membre (id, password, recovery) VALUES (:id, :password, NULL)
		ON DUPLICATE KEY UPDATE password = :password, recovery = NULL');
		$update_user->execute(array(':id' => $_GET['id'], ':password' => $password_hash));
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=utf-8";
		$headers[] = "From: GoldenCydia Robot <noreply@goldencydia.org>";
		$headers[] = "Subject: "._('Lost password')." | ".$site_nom;
		$headers[] = "Return-Path: <".$check_reset['mail'].">";
		$headers[] = "X-Mailer: PHP/".phpversion();
		$headers[] = "X-Sender: <www.goldencydia.org>";
		$headers[] = "X-auth-smtp-user: contact@goldencydia.org";
		$headers[] = "X-abuse-contact: abuse@goldencydia.org";
		$site_url = config('url');
		$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'._('Lost password').'</title></head><body><p>'.$check_reset['pseudo'].',<br />'._('Your password has been reset.').'</p><p dir="ltr">'._('Your new password is :').' <b>'.$password.'</b></p><p>'._('You will need this password to connect, once connected, you must change it by going to your control panel.').'</p><br /><p>'.$site_nom.' Staff</p><p style="text-align:center"><small>Automatically sent with a PHP script.</small></p></body></html>';
		mail($check_reset['mail'], _('Lost password'), $message, implode("\r\n", $headers));
		$pdo = PDO2::closeInstance();
		header("Location: forgot-password.php?reset");
		exit;
	} else {
		$pdo = PDO2::closeInstance();
		header("Location: forgot-password.php");
		exit;
	}
} elseif(!empty($_POST['mail'])) {
	$pdo = PDO2::getInstance();
	$check_reset = $pdo->prepare('SELECT id, pseudo, mail FROM membre WHERE mail = :mail');
	$check_reset->execute(array(':mail' => htmlspecialchars(htmlentities(strip_tags(trim($_POST['mail']))))));
	$count_reset = $check_reset->rowCount();
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		$ip = $_SERVER['REMOTE_ADDR'];
	if($count_reset == 1) {
		$check_reset = $check_reset->fetch(PDO::FETCH_ASSOC);
		$chaine = "abcdefghijklmnpqrstuvwxyz0123456789";
		$nb_chars = strlen($chaine);
		for($i=0; $i < 15; $i++) {
			$recovery .= $chaine[ rand(0, ($nb_chars-1)) ];
		}
		$update_user = $pdo->prepare('INSERT INTO membre (id, recovery) VALUES (:id, :recovery)
		ON DUPLICATE KEY UPDATE recovery = :recovery');
		$update_user->execute(array(':id' => $check_reset['id'], ':recovery' => $recovery));
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=utf-8";
		$headers[] = "From: GoldenCydia Robot <noreply@goldencydia.org>";
		$headers[] = "Subject: "._('Lost password')." | ".$site_nom;
		$headers[] = "Return-Path: <".$check_reset['mail'].">";
		$headers[] = "X-Mailer: PHP/".phpversion();
		$headers[] = "X-Sender: <www.goldencydia.org>";
		$headers[] = "X-auth-smtp-user: contact@goldencydia.org";
		$headers[] = "X-abuse-contact: abuse@goldencydia.org";
		$site_url = config('url');
		$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'._('Lost password').'</title></head><body><p>'.$check_reset['pseudo'].',<br />'._('To complete the phase resetting your password, you will need to go to the following URL in your web browser.').'</p><p><a href="'.$site_url.'forgot-password.php?id='.$check_reset['id'].'&recovery='.$recovery.'">'.$site_url.'forgot-password.php?id='.$check_reset['id'].'&recovery='.$recovery.'</a></p><p>'._('You will receive another mail with your new password.').'</p><br /><p>'.$site_nom.' Staff</p><p style="text-align:center"><small>Automatically sent with a PHP script.</small></p></body></html>';
		mail($check_reset['mail'], _('Lost password'), $message, implode("\r\n", $headers));
		$pdo = PDO2::closeInstance();
		header("Location: forgot-password.php?checkmail");
		exit;
	} else {
		$pdo = PDO2::closeInstance();
		header("Location: forgot-password.php?nomail");
		exit;
	}
} else { ?>
<!doctype html>
<html>
	<head>
		<title><?php echo _('Lost password'); ?> | <?php echo $site_nom; ?></title>
		<link rel="stylesheet" href="css/style.min.css">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<style type="text/css" media="screen">html{height:auto}body{height:auto;margin:5px 0;background:#222 !important}.login{margin:auto;width:320px;text-align:center}.well{box-shadow: 0px 4px 10px -1px rgba(200, 200, 200, 0.7);background:#fff;border: 1px solid rgb(0, 0, 0);border-radius: 10px;margin:0 5px 20px}label{font-size:14px;text-align:center}input{color: rgb(85, 85, 85);font-weight:400;font-size:20px;line-height: 1;width:95%;padding: 3px;margin-top: 2px;margin-bottom: 16px;border: 1px solid rgb(229, 229, 229);background: none repeat scroll 0% 0% rgb(251, 251, 251);outline: 0px none;box-shadow: 1px 1px 2px rgba(200, 200, 200, 0.2) inset;height:30px}h1 a {background-image: url('/images/logo.png');background-position: center top;background-repeat: no-repeat;width: 320px;height: 80px;text-indent: -9999px;outline: 0px none;overflow: hidden;padding-bottom: 15px;margin:0 auto;display: block;}a.backtoblog {color: rgb(0, 136, 204);text-shadow: 0px 1px 0px rgb(0, 0, 0)}a:hover.backtoblog {color: rgb(255, 255, 255);}hr{margin-top:0}</style>
	</head>
	<body>
		<h1><a href="./" title="<?php echo $site_nom; ?>"><?php echo $site_nom; ?></a></h1>
		<div class="login">
			<form class="well" method="POST" name="forgot">
			<?php if (isset($_GET['nomail'])) {
				echo '<h3>'._('Lost password').'</h3>';
				echo '<h5 style="color:#f00">'._('Mail is invalid !').'</h5><hr>';
			} elseif (isset($_GET['checkmail'])) {
				echo '<h3>'._('Lost password').'</h3>';
				echo '<h5 style="color:#0d0">'._('A mail with a link to generate a new password has been sent !').'</h5><hr>';
			} elseif (isset($_GET['reset'])) {
				echo '<h3>'._('Lost password').'</h3>';
				echo '<h5 style="color:#0d0">'._('A mail with your new password has been sent !').'</h5><hr>';
			} else
				echo '<h3>'._('Lost password').'</h3><hr>'; ?>
				<label for="mail"><?php echo _('Mail'); ?></label>
				<input autofocus type="email" id="mail" name="mail" required="required" placeholder="<?php echo _('Mail'); ?>" />
				<button class="btn btn-medium btn-primary" type="submit">OK</button>
			</form>
			<div><a href="./" title="← <?php echo _('Back to home.'); ?>">← <?php echo _('Back to home.'); ?></a> | <a href="register.php" title="<?php echo _('Registration'); ?>"><?php echo _('Registration'); ?></a> | <a href="login.php" title="<?php echo _('Sign In'); ?>"><?php echo _('Sign In'); ?></a></div>
		</div>
	</body>
</html><?php  } ?>