<?php if(!empty($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 7, strlen($_SERVER['SERVER_NAME'])) != $_SERVER['SERVER_NAME'])
	$_POST = array();
require_once('includes/session.class.php');
$membre = new Session();
$lang_user = translation();
$site_nom = config('nom');
$site_url = config('url');
if ($membre->_connected) {
	header("Location: dpt-account.php");
	exit;
} elseif (!empty($_POST['username']) && !empty($_POST['mail'])) {
	$mail = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['mail'])))));
	$pseudo = addslashes(htmlspecialchars(htmlentities(strip_tags(trim($_POST['username'])))));
	if(!preg_match('/^[_a-zA-Z0-9_]+$/', $_POST['username'])) {
		header('Location: register.php?nicknameerror');
		exit;
	} elseif(strlen($_POST['username']) > 20) {
		header('Location: register.php?nicknamelong');
		exit;
	} elseif(strlen($_POST['username']) < 3) {
		header('Location: register.php?nicknameshort');
		exit;
	} else {
		$pdo = PDO2::getInstance();
		$query = $pdo->prepare("SELECT id FROM membre WHERE pseudo = :pseudo");
		$query->execute(array(':pseudo' => $pseudo));
		$count = $query->rowCount();
		$query = $query->fetchColumn();
		if($count === 0) {
			if(!preg_match('/@.+\./', $_POST['mail'])) {
				$pdo = PDO2::closeInstance();
				header('Location: register.php?mailerror');
				exit;
			} elseif(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
				$pdo = PDO2::closeInstance();
				header('Location: register.php?mailerror');
				exit;
			} elseif(strlen($_POST['mail']) > 250) {
				$pdo = PDO2::closeInstance();
				header('Location: register.php?maillong');
				exit;
			} elseif(strlen($_POST['mail']) < 6) {
				$pdo = PDO2::closeInstance();
				header('Location: register.php?mailshort');
				exit;
			} else {
				$query1 = $pdo->prepare("SELECT id FROM membre WHERE mail = :mail");
				$query1->execute(array(':mail' => $mail));
				$count1 = $query1->rowCount();
				$query1 = $query1->fetchColumn();
				if($count1 === 0) {
					$chaine = "abcdefghijklmnpqrstuvwxyz0123456789";
					$nb_chars = strlen($chaine);
					for($i=0; $i < 8; $i++) {
						$password .= $chaine[ rand(0, ($nb_chars-1)) ];
					}
					$password_hash = hash('sha512', hash('sha512', $password));
					$query = $pdo->prepare("INSERT INTO membre (pseudo, mail, password, ip, level, date) VALUES (:pseudo, :mail, :password, :ip, 0, CURRENT_TIMESTAMP)");
					$query->execute(array(':pseudo' => $pseudo, ':mail' => $mail, ':password' => $password_hash, ':ip' => getIp()));
					$pdo = PDO2::closeInstance();
					$headers   = array();
					$headers[] = "MIME-Version: 1.0";
					$headers[] = "Content-type: text/html; charset=utf-8";
					$headers[] = "From: GoldenCydia Robot <noreply@goldencydia.org>";
					$headers[] = "Subject: "._('Registration')." | ".$site_nom;
					$headers[] = "Return-Path: <".$mail.">";
					$headers[] = "X-Mailer: PHP/".phpversion();
					$headers[] = "X-Sender: <www.goldencydia.org>";
					$headers[] = "X-auth-smtp-user: contact@goldencydia.org";
					$headers[] = "X-abuse-contact: abuse@goldencydia.org";
					$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'._('Registration').'</title></head><body><p>'.$pseudo.',<br />'._('thanks for your registration !').'</p><p dir="ltr">'._('Your password is :').' <b>'.$password.'</b></p><p>'._('To log on, follow the link below :').'<br /><a href="'.$site_url.'login.php">'.$site_url.'login.php</a></p><br /><p>'.$site_nom.' Staff</p><p style="text-align:center"><small>Automatically sent with a PHP script.</small></p></body></html>';
					mail($mail, _('Registration'), $message, implode("\r\n", $headers));
					if(file_exists('includes/cache/totalMembre.txt'))
						unlink('includes/cache/totalMembre.txt');
					header('Location: register.php?success');
					exit;
				} else {
					$pdo = PDO2::closeInstance();
					header('Location: register.php?mailused');
					exit;
				}
			}
		} else {
			$pdo = PDO2::closeInstance();
			header('Location: register.php?nicknameused');
			exit;
		}
	}
} elseif (isset($_POST['submit']) && (empty($_POST['username']) || empty($_POST['mail']))) {
	header('Location: register.php?notenough');
	exit;
} else {  ?>
<!doctype html>
<html>
	<head>
		<title><?php echo _('Registration'); ?> | <?php echo $site_nom; ?></title>
		<link rel="stylesheet" href="css/style.min.css">
		<link rel="shortcut icon" href="images/favicon.ico" />
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<style type="text/css" media="screen">html{height:auto}body{height:auto;margin:5px 0;background:#222 !important}.login{margin:auto;width:320px;text-align:center}.well{box-shadow: 0px 4px 10px -1px rgba(200, 200, 200, 0.7);background:#fff;border: 1px solid rgb(0, 0, 0);border-radius: 10px;margin:0 5px 20px}label{font-size: 14px;text-align:center}input{color: rgb(85, 85, 85);font-weight:400;font-size:20px;line-height: 1;width:95%;padding: 3px;margin-top: 2px;margin-bottom: 16px;border: 1px solid rgb(229, 229, 229);background: none repeat scroll 0% 0% rgb(251, 251, 251);outline: 0px none;box-shadow: 1px 1px 2px rgba(200, 200, 200, 0.2) inset;height:30px}h1 a {background-image: url('/images/logo.png');background-position: center top;background-repeat: no-repeat;width: 320px;height: 80px;text-indent: -9999px;outline: 0px none;overflow: hidden;padding-bottom: 15px;margin:0 auto;display: block;}a#backtoblog {color: rgb(0, 136, 204);text-shadow: 0px 1px 0px rgb(0, 0, 0)}a:hover#backtoblog {color: rgb(255, 255, 255);}hr{margin-top:0}</style>
	</head>
	<body>
		<h1><a href="<?php echo $site_url; ?>" title="<?php echo $site_nom; ?>"><?php echo $site_nom; ?></a></h1>
		<div class="login">
			<form class="well" method="POST">
			<?php if (isset($_GET['notenough'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('Please fill in all fields.').'</h5><hr>';
			} elseif (isset($_GET['nicknameused'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This nickname is already used by another member !').'</h5><hr>';
			} elseif (isset($_GET['nicknameerror'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('Nickname must not contain special characters except dash bottom (_).').'</h5><hr>';
			} elseif (isset($_GET['nicknameshort'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This nickname is too short !').'</h5><hr>';
			} elseif (isset($_GET['nicknamelong'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This nickname is too long !').'</h5><hr>';
			} elseif (isset($_GET['mailused'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This mail is already used by another member !').'</h5><hr>';
			} elseif (isset($_GET['mailerror'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('Mail is invalid !').'</h5><hr>';
			} elseif (isset($_GET['mailshort'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This mail is too short !').'</h5><hr>';
			} elseif (isset($_GET['maillong'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#f00">'._('This mail is too long !').'</h5><hr>';
			} elseif (isset($_GET['success'])) {
				echo '<h3>'._('Registration').'</h3>';
				echo '<h5 style="color:#0e0">'._('Successful registration ! Check your mail to retrieve your password.').'</h5><hr>';
			} else {
				echo '<h3>'._('Registration').'</h3><hr>';
			} ?>
				<label for="username"><?php echo _('Nickname'); ?></label>
				<input autofocus type="text" id="username" name="username" required="required" placeholder="<?php echo _('Nickname'); ?>" />
				<label for="mail"><?php echo _('Mail'); ?></label>
				<input type="email" id="mail" name="mail" placeholder="<?php echo _('Mail'); ?>" required="required" /></p>
				<button type="submit" class="btn btn-medium btn-primary"><?php echo _('Registration'); ?></button>
			</form>
			<div><a href="./" title="← <?php echo _('Back to home.'); ?>">← <?php echo _('Back to home.'); ?></a> | <a href="forgot-password.php" title="<?php echo _('Lost password'); ?>"><?php echo _('Lost password'); ?></a> | <a href="login.php" title="<?php echo _('Sign In'); ?>"><?php echo _('Sign In'); ?></a></div>
		</div>
	</body>
</html><?php } ?>